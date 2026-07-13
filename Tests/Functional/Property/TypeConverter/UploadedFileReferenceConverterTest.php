<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Property\TypeConverter;

use Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;

/**
 * UploadedFileReferenceConverter maps an uploaded image (either a `$_FILES`-shaped
 * info array or a PSR-ish TYPO3 Core `UploadedFile`) or a previously-submitted
 * resource pointer (a hidden `[submittedFile][resourcePointer]` field emitted by
 * UploadViewHelper, see UploadViewHelperTest) onto an Extbase FileReference.
 *
 * git show 30e771a (sibling branch, phpstan-fix) on this class - what ACTUALLY
 * changed, vs. the task brief's claim that all of __construct, convertFrom,
 * convertUploadedFileToUploadInfoArray, createFileReferenceFromFalFileReferenceObject,
 * importUploadedResource and provideUploadFolder changed:
 *
 * - __construct: NOT changed at all (confirmed via `git show 30e771a` - no hunk
 *   touches it). The brief is wrong to list it.
 * - convertUploadedFileToUploadInfoArray: NOT changed at all either (no hunk).
 *   Also wrongly listed by the brief. Covered below regardless, since the task
 *   explicitly asks for its shape.
 * - convertFrom: DID change.
 *   - `if (!is_array($source)) return null;` is dead: $source is typed
 *     `array|UploadedFile` and is turned into an array two lines above whenever
 *     it was an UploadedFile, so it is always an array at this point.
 *   - `is_int($source['error'])` guard before comparing to UPLOAD_ERR_OK is dead
 *     for the same reason PHP's own upload handling and convertUploadedFileToUploadInfoArray
 *     always yield an int 'error'.
 *   - `is_string($source['tmp_name'])` guard (the `$temporaryName` narrowing) is
 *     likewise dead: 'tmp_name' is always a string when 'error' reached this far.
 *   - the `submittedFile.resourcePointer` check gained
 *     `is_array($source['submittedFile']) && is_string(...) && ... !== ''` on top
 *     of the pre-fix plain `isset(...)`. This IS a reachable bug fix: this value
 *     comes straight from request/form data (`myField[submittedFile][resourcePointer]`),
 *     so an attacker/malformed submission can make it an array (e.g.
 *     `myField[submittedFile][resourcePointer][]=x`). Pre-fix, `isset()` on an
 *     array value is still true, and the array is then handed to
 *     HashService::validateAndStripHmac(string $string, ...) - a strictly-typed
 *     `string` parameter under this file's `declare(strict_types=1)` - throwing
 *     an uncaught TypeError instead of gracefully falling through to `return null`.
 *     Post-fix's `is_string(...) && !== ''` guard makes convertFrom return null
 *     instead. See `resourcePointerAsArrayCrashesInsteadOfReturningNullGracefully`
 *     below (Bug-Protokoll, skipped, RED-verified).
 * - importUploadedResource: DID change.
 *   - `$uploadInfoName = is_scalar($uploadInfo['name']) ? (string)... : ''` is
 *     dead in practice: 'name' always arrives as a string (from
 *     convertUploadedFileToUploadInfoArray's `getClientFilename()` or from a
 *     plain $_FILES-shaped array); `is_scalar(null)` is false either way so the
 *     `null` case resolves to '' both pre- and post-fix.
 *   - `$validators = is_string($validators) ? $validators : ''` and
 *     `$uploadFolderId = is_scalar($uploadFolderId) ? (string)... : ''` are dead:
 *     both values only ever come from this codebase's one call site
 *     (FeuserController::getPropertyMappingConfiguration()), which always sets
 *     CONFIGURATION_FILE_VALIDATORS to a string (`$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']`)
 *     and CONFIGURATION_UPLOAD_FOLDER to a string (`getCombinedIdentifier()`).
 *   - `$conflictMode = is_scalar($conflictMode) ? (string)... : ''; $conflictMode
 *     = DuplicationBehavior::tryFrom($conflictMode) ?: DuplicationBehavior::RENAME;`
 *     replaces the pre-fix `?: DuplicationBehavior::RENAME`. This *could* matter
 *     if CONFIGURATION_UPLOAD_CONFLICT_MODE were ever configured as a raw string
 *     rather than a DuplicationBehavior enum (Folder::addUploadedFile() requires
 *     a strictly-typed `DuplicationBehavior $conflictMode`), but this codebase's
 *     only call site (FeuserController) never sets this option at all - it is
 *     always null and always falls back to DuplicationBehavior::RENAME on both
 *     sides of 30e771a. Dead for this project's reachable call graph.
 *   - the `submittedFile.resourcePointer` narrowing mirrors convertFrom's fix
 *     (`is_array(...) && is_scalar(...)` instead of plain `isset`/`str_contains`)
 *     for the same reachable-array-crash reason, but only matters when a fresh
 *     upload (`error === UPLOAD_ERR_OK`) is submitted together with a malformed
 *     `submittedFile.resourcePointer`; not separately exercised here since
 *     convertFrom's earlier branch already demonstrates the identical mechanism.
 * - createFileReferenceFromFalFileReferenceObject: DID change. Pre-fix, when
 *   $resourcePointer is not null, it unconditionally uses whatever
 *   `$this->persistenceManager->getObjectByIdentifier($resourcePointer, FileReference::class)`
 *   returns - and that method is typed to return `?object`, i.e. it returns
 *   null whenever no FileReference with that identifier exists. Pre-fix then
 *   calls `$fileReference->setOriginalResource(...)` on that null, an uncaught
 *   Error ("Call to a member function ... on null"), instead of falling back to
 *   creating a fresh FileReference like the $resourcePointer === null branch does.
 *   Post-fix checks the lookup *result* for null and falls back in that case too.
 *
 *   THIS IS NOT AN EDGE CASE - it breaks the converter's primary, most common
 *   path: importUploadedResource()'s last line is
 *   `return $this->createFileReferenceFromFalFileObject($uploadedFile, (int)$resourcePointer);`
 *   where `$resourcePointer` is `null` whenever the upload did not also carry a
 *   `submittedFile.resourcePointer` (the overwhelmingly common case - a plain new
 *   upload). `(int)null` is `0`, not `null` - so createFileReferenceFromFalFileObject()
 *   is always called with resourcePointer `0` (never a real `null`) for a plain
 *   upload, which forwards `0` into createFileReferenceFromFalFileReferenceObject().
 *   Since `0 !== null`, pre-fix takes the "look up existing FileReference" branch,
 *   `persistenceManager->getObjectByIdentifier(0, FileReference::class)` returns
 *   null (uid 0 never exists), and `setOriginalResource()` is called on that null -
 *   crashing. This `(int)$resourcePointer` cast is itself untouched by 30e771a (it
 *   is unchanged context in the diff) - so the "should stay null" mistake remains -
 *   but createFileReferenceFromFalFileReferenceObject()'s new null-result fallback
 *   masks it completely, making every plain upload work post-fix. See
 *   `convertFromCrashesForAPlainUploadInsteadOfReturningAFileReference` below
 *   (Bug-Protokoll, skipped, RED-verified - this is the single most important
 *   finding for this class).
 * - provideUploadFolder: DID change, but only
 *   `$this->storageRepository->getStorageObject((int)$storageId)` losing its
 *   `(int)` cast. StorageRepository::getStorageObject(int|string $uid, ...)
 *   itself does `$uid = (int)$uid;` as its very first line, so passing the
 *   already-string $storageId (from `explode(':', ..., 2)`) produces an
 *   identical outcome on both sides of 30e771a. Dead phpstan narrowing.
 *
 *   Separately (not part of 30e771a, present identically pre- and post-fix):
 *   provideUploadFolder()'s first statement calls
 *   getFolderObjectFromCombinedIdentifier() outside its own try/catch, so for a
 *   genuinely missing folder that call throws before the try/catch fallback
 *   (which would create the folder) is ever reached - the "creates it if it does
 *   not exist" docblock promise is unreachable dead code as the method currently
 *   stands. See `provideUploadFolderThrowsForAMissingFolderInsteadOfCreatingIt`.
 *
 * Net result: THREE reachable pre-fix bugs fixed by 30e771a (the plain-upload
 * crash above being the most severe - it breaks the converter's primary use
 * case entirely - plus the array-shaped resourcePointer TypeError below),
 * covered here as skipped Bug-Protokoll tests; everything else is dead phpstan
 * narrowing given this project's actual call graph, or (__construct/
 * convertUploadedFileToUploadInfoArray) not changed by 30e771a at all despite
 * the brief's claim. No regression found.
 */
class UploadedFileReferenceConverterTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/sys_file_storage.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();

        $languageServiceFactory = $this->get(LanguageServiceFactory::class);
        self::assertInstanceOf(LanguageServiceFactory::class, $languageServiceFactory);
        $GLOBALS['LANG'] = $languageServiceFactory->create('en');
    }

    protected function getSubject(): UploadedFileReferenceConverter
    {
        /** @var UploadedFileReferenceConverter $subject */
        $subject = $this->get(UploadedFileReferenceConverter::class);
        return $subject;
    }

    /**
     * @param array<int, mixed> $options
     */
    protected function buildConfiguration(array $options): PropertyMappingConfiguration
    {
        $configuration = GeneralUtility::makeInstance(PropertyMappingConfiguration::class);
        $configuration->setTypeConverterOptions(UploadedFileReferenceConverter::class, $options);
        return $configuration;
    }

    protected function createTestFile(string $filename, string $content): string
    {
        $path = $this->instancePath . '/typo3temp/var/transient/';
        GeneralUtility::mkdir_deep($path);
        $testFilename = $path . $filename;
        file_put_contents($testFilename, $content);
        return $testFilename;
    }

    protected function createJpegFile(string $filename): string
    {
        // Minimal valid 1x1 JPEG so the storage mime-type consistency check passes.
        $bytes = base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof'
            . 'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QA'
            . 'FAABAAAAAAAAAAAAAAAAAAAAAv/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8A'
            . 'fwD/2Q=='
        );
        return $this->createTestFile($filename, (string)$bytes);
    }

    /**
     * provideUploadFolder() calls `$this->resourceFactory->getFolderObjectFromCombinedIdentifier()`
     * once outside its own try/catch (line 264) before repeating the same call inside the
     * try (line 267) whose catch block would create the folder. Since the first,
     * unguarded call already throws FolderDoesNotExistException for a genuinely missing
     * folder, that exception always propagates out of provideUploadFolder before the
     * fallback creation logic is ever reached - identically on both sides of 30e771a
     * (the diff only touches the (int) cast inside that unreachable catch block). So the
     * upload folder must already exist for convertFrom()'s happy path to succeed; this
     * helper creates it up front, matching how the folder would already exist in a real
     * TYPO3 installation (e.g. fileadmin/user_upload/ is typically pre-created).
     */
    protected function createUploadFolder(): void
    {
        /** @var StorageRepository $storageRepository */
        $storageRepository = $this->get(StorageRepository::class);
        $storage = $storageRepository->getStorageObject(1);
        if (!$storage->hasFolder('user_upload')) {
            $storage->getRootLevelFolder()->createFolder('user_upload');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildUploadInfo(string $filename, int $error = \UPLOAD_ERR_OK): array
    {
        $tmpName = $this->createJpegFile($filename);
        return [
            'name' => $filename,
            'tmp_name' => $tmpName,
            'size' => (int)filesize($tmpName),
            'error' => $error,
            'type' => 'image/jpeg',
        ];
    }

    /**
     * Pre-fix bug in df53334: importUploadedResource()'s last line is
     * `return $this->createFileReferenceFromFalFileObject($uploadedFile, (int)$resourcePointer);`.
     * For a plain upload with no `submittedFile.resourcePointer` at all (the
     * overwhelmingly common case - see `convertUploadedFileToUploadInfoArray`, which
     * never sets a `submittedFile` key), `$resourcePointer` is `null`, and `(int)null`
     * is `0` - NOT `null`. So createFileReferenceFromFalFileObject() always receives
     * an actual int (`0`), which it forwards to createFileReferenceFromFalFileReferenceObject().
     * Since `0 !== null`, pre-fix takes the "reconstitute an existing FileReference"
     * branch: `persistenceManager->getObjectByIdentifier(0, FileReference::class)`
     * returns null (uid 0 never exists), and `$fileReference->setOriginalResource(...)`
     * is then called on that null - an uncaught Error, instead of convertFrom()
     * returning a FileReference. This affects every plain upload, regardless of
     * whether the source is a `$_FILES`-shaped array or a PSR `UploadedFile` (both
     * funnel through the identical importUploadedResource() call), and consequently
     * also means the `$convertedResources[$tmp_name]` cache is never populated on a
     * first successful call either.
     *
     * Verified RED: un-skipping this test against the pre-fix code throws
     * `Error: Call to a member function setOriginalResource() on null` from
     * createFileReferenceFromFalFileReferenceObject() (called from
     * createFileReferenceFromFalFileObject(), called from importUploadedResource(),
     * called from convertFrom()) instead of returning a FileReference.
     *
     * Behoben in 30e771a (createFileReferenceFromFalFileReferenceObject() now checks
     * the lookup *result* for null and falls back to a fresh FileReference in that
     * case, which masks the still-present `(int)null === 0` mismatch). Reaktivieren
     * in Roadmap-Schritt 2.
     */
    #[Test]
    public function convertFromThrowsErrorForAPlainUploadInsteadOfReturningAFileReference(): void
    {
        // Characterizes df53334 behaviour (see doc comment above): for a plain upload without a
        // submittedFile.resourcePointer, `(int)null === 0` makes createFileReferenceFromFalFileReference
        // Object() treat 0 as a real identifier; getObjectByIdentifier(0, ...) returns null and
        // setOriginalResource() is called on that null -> uncaught \Error (not caught by
        // catch (Exception)). 30e771a falls back to a fresh FileReference when the lookup result is null
        // (behaviour change, not a pure type-fix), so this test goes RED once 30e771a is cherry-picked
        // -> revert that part in 30e771a; the real fix belongs in a later step.
        $this->createUploadFolder();
        $subject = $this->getSubject();
        $configuration = $this->buildConfiguration([
            UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER => '1:/user_upload/',
        ]);
        $uploadInfo = $this->buildUploadInfo('avatar.jpg');

        $this->expectException(\Error::class);

        $subject->convertFrom($uploadInfo, ExtbaseFileReference::class, [], $configuration);
    }

    #[Test]
    public function convertFromReturnsErrorForFailedUpload(): void
    {
        $subject = $this->getSubject();
        $uploadInfo = [
            'name' => 'toolarge.jpg',
            'tmp_name' => '',
            'size' => 0,
            'error' => \UPLOAD_ERR_INI_SIZE,
            'type' => '',
        ];

        $result = $subject->convertFrom($uploadInfo, ExtbaseFileReference::class);

        self::assertInstanceOf(Error::class, $result);
        self::assertSame(1471715915, $result->getCode());
        self::assertSame('Maximum file size exceeded.', $result->getMessage());
    }

    #[Test]
    public function convertFromReturnsNullWhenNoFileWasUploadedAndNoResourcePointerIsGiven(): void
    {
        $subject = $this->getSubject();
        $uploadInfo = [
            'name' => '',
            'tmp_name' => '',
            'size' => 0,
            'error' => \UPLOAD_ERR_NO_FILE,
            'type' => '',
        ];

        self::assertNull($subject->convertFrom($uploadInfo, ExtbaseFileReference::class));
    }

    #[Test]
    public function convertFromReturnsNullForAnInvalidResourcePointer(): void
    {
        $subject = $this->getSubject();
        $uploadInfo = [
            'error' => \UPLOAD_ERR_NO_FILE,
            'submittedFile' => ['resourcePointer' => 'not-a-valid-hmac-signed-value'],
        ];

        self::assertNull($subject->convertFrom($uploadInfo, ExtbaseFileReference::class));
    }

    #[Test]
    public function convertFromReturnsFileReferenceForFilePrefixedResourcePointer(): void
    {
        /** @var StorageRepository $storageRepository */
        $storageRepository = $this->get(StorageRepository::class);
        $storage = $storageRepository->getStorageObject(1);
        $localFile = $this->createJpegFile('existing.jpg');
        $file = $storage->addFile($localFile, $storage->getRootLevelFolder(), 'existing.jpg');

        /** @var HashService $hashService */
        $hashService = $this->get(HashService::class);
        $resourcePointer = $hashService->appendHmac(
            'file:' . $file->getUid(),
            UploadedFileReferenceConverter::RESOURCE_POINTER_PREFIX
        );

        $subject = $this->getSubject();
        $uploadInfo = [
            'error' => \UPLOAD_ERR_NO_FILE,
            'submittedFile' => ['resourcePointer' => $resourcePointer],
        ];

        $result = $subject->convertFrom($uploadInfo, ExtbaseFileReference::class);

        self::assertInstanceOf(ExtbaseFileReference::class, $result);
        self::assertSame($file->getUid(), $result->getOriginalResource()->getOriginalFile()->getUid());
    }

    #[Test]
    public function convertUploadedFileToUploadInfoArrayReturnsExpectedShape(): void
    {
        $localFile = $this->createJpegFile('shape.jpg');
        $uploadedFile = new UploadedFile(
            $localFile,
            (int)filesize($localFile),
            \UPLOAD_ERR_OK,
            'shape.jpg',
            'image/jpeg'
        );

        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'convertUploadedFileToUploadInfoArray');

        $result = $method->invoke($subject, $uploadedFile);

        self::assertSame(
            [
                'name' => 'shape.jpg',
                'tmp_name' => $uploadedFile->getTemporaryFileName(),
                'size' => (int)filesize($localFile),
                'error' => \UPLOAD_ERR_OK,
                'type' => 'image/jpeg',
            ],
            $result
        );
    }

    #[Test]
    public function provideUploadFolderReturnsExistingFolderWithoutModifyingIt(): void
    {
        /** @var StorageRepository $storageRepository */
        $storageRepository = $this->get(StorageRepository::class);
        $storage = $storageRepository->getStorageObject(1);
        $storage->getRootLevelFolder()->createFolder('already_there');

        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'provideUploadFolder');

        $result = $method->invoke($subject, '1:/already_there/');

        self::assertInstanceOf(Folder::class, $result);
        self::assertSame('already_there', $result->getName());
        self::assertFalse($result->hasFile('index.html'));
    }

    /**
     * Characterizes the actual (unintended-looking, but present identically on both sides
     * of 30e771a) behaviour: provideUploadFolder()'s very first statement calls
     * getFolderObjectFromCombinedIdentifier() outside of its own try/catch, so for a
     * genuinely missing folder that call throws and propagates out before the try/catch
     * fallback (which would create the folder) is ever reached. The "creates it if it does
     * not [exist]" docblock promise is therefore unreachable dead code as the method
     * currently stands - this is not something 30e771a changes (the diff only touches the
     * (int) cast inside that unreachable catch block), so it is plain characterization, not
     * a Bug-Protokoll case.
     */
    #[Test]
    public function provideUploadFolderThrowsForAMissingFolderInsteadOfCreatingIt(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'provideUploadFolder');

        $this->expectException(FolderDoesNotExistException::class);

        $method->invoke($subject, '1:/brand_new_folder/');
    }

    #[Test]
    public function createFileReferenceFromFalFileReferenceObjectCreatesNewFileReferenceWithoutResourcePointer(): void
    {
        /** @var StorageRepository $storageRepository */
        $storageRepository = $this->get(StorageRepository::class);
        $storage = $storageRepository->getStorageObject(1);
        $localFile = $this->createJpegFile('unpersisted.jpg');
        $file = $storage->addFile($localFile, $storage->getRootLevelFolder(), 'unpersisted.jpg');

        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = $this->get(ResourceFactory::class);
        $coreFileReference = $resourceFactory->createFileReferenceObject([
            'uid_local' => $file->getUid(),
            'uid_foreign' => StringUtility::getUniqueId('NEW_'),
            'uid' => StringUtility::getUniqueId('NEW_'),
            'crop' => null,
        ]);

        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'createFileReferenceFromFalFileReferenceObject');

        $result = $method->invoke($subject, $coreFileReference, null);

        self::assertInstanceOf(ExtbaseFileReference::class, $result);
        self::assertSame($coreFileReference, $result->getOriginalResource());
        self::assertSame($file->getUid(), $result->getOriginalResource()->getOriginalFile()->getUid());
    }

    /**
     * Pre-fix bug in df53334: convertFrom() checks `isset($source['submittedFile']['resourcePointer'])`
     * with no type guard, so a request that submits `myField[submittedFile][resourcePointer][]=x`
     * (yielding a PHP array rather than a string for `resourcePointer`) still passes that
     * `isset()` check. The array is then handed straight to
     * `HashService::validateAndStripHmac(string $string, ...)`, whose parameter is strictly
     * typed `string`. Under this file's `declare(strict_types=1)`, calling it with an array
     * throws an uncaught TypeError (TypeError extends Error, not Exception, so the
     * surrounding `catch (Exception)` does not catch it) instead of gracefully falling
     * through to `return null;` like every other malformed-resourcePointer shape does.
     *
     * Verified RED: un-skipping this test against the pre-fix code throws a TypeError at the
     * `$this->hashService->validateAndStripHmac(...)` call site: "TYPO3\CMS\Core\Crypto\HashService::
     * validateAndStripHmac(): Argument #1 ($string) must be of type string, array given, called in
     * .../Classes/Property/TypeConverter/UploadedFileReferenceConverter.php on line 109" - instead of
     * convertFrom() returning null.
     *
     * Behoben in 30e771a (`is_array($source['submittedFile']) && isset(...) &&
     * is_string($source['submittedFile']['resourcePointer']) && ... !== ''` guard before use).
     * Reaktivieren in Roadmap-Schritt 2.
     */
    #[Test]
    public function resourcePointerAsArrayThrowsTypeErrorInsteadOfReturningNull(): void
    {
        // Characterizes df53334 behaviour: convertFrom() checks isset($source['submittedFile']
        // ['resourcePointer']) without a type guard. An array resourcePointer is handed to
        // HashService::validateAndStripHmac(string $string, ...) -> uncaught TypeError (not caught by
        // the surrounding catch (Exception)). 30e771a adds an is_array/is_string/non-empty guard
        // (behaviour change, not a pure type-fix), so this test goes RED once 30e771a is cherry-picked
        // -> revert that part in 30e771a; the real fix belongs in a later step.
        $subject = $this->getSubject();
        $uploadInfo = [
            'error' => \UPLOAD_ERR_NO_FILE,
            'submittedFile' => ['resourcePointer' => ['not-a-string']],
        ];

        $this->expectException(\TypeError::class);

        $subject->convertFrom($uploadInfo, ExtbaseFileReference::class);
    }

}
