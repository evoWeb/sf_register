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
 * Notable behavior:
 * - convertFrom()/importUploadedResource(): a `submittedFile.resourcePointer` value
 *   that arrives as an array (e.g. a malformed submission
 *   `myField[submittedFile][resourcePointer][]=x`) is guarded with
 *   `is_array(...) && is_string(...) && ... !== ''` and treated as absent, returning
 *   null gracefully instead of handing an array to
 *   `HashService::validateAndStripHmac(string $string, ...)` - a strictly-typed
 *   `string` parameter under this file's `declare(strict_types=1)` - which would
 *   otherwise throw an uncaught TypeError (TypeError extends Error, not Exception,
 *   so a surrounding `catch (Exception)` would not catch it). See
 *   `resourcePointerAsArrayReturnsNullGracefully`.
 * - createFileReferenceFromFalFileReferenceObject(): for a plain new upload (no
 *   `submittedFile.resourcePointer`), importUploadedResource()'s last line passes
 *   `(int)null` = `0` as $resourcePointer, so this method's "look up existing
 *   FileReference" branch is always taken for a plain upload, and
 *   `persistenceManager->getObjectByIdentifier(0, FileReference::class)` always
 *   returns null (uid 0 never exists). The method therefore checks the lookup
 *   result for null and falls back to creating a fresh FileReference in that case
 *   too - this is the converter's primary, most common path, not an edge case. See
 *   `convertFromReturnsFileReferenceForAPlainUpload`.
 * - provideUploadFolder(): its first statement calls
 *   getFolderObjectFromCombinedIdentifier() outside its own try/catch, so for a
 *   genuinely missing folder that call throws before the try/catch fallback (which
 *   would create the folder) is ever reached - the "creates it if it does not
 *   exist" docblock promise is unreachable dead code as the method currently
 *   stands. See `provideUploadFolderThrowsForAMissingFolderInsteadOfCreatingIt`.
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
     * fallback creation logic is ever reached. So the upload folder must already exist
     * for convertFrom()'s happy path to succeed; this helper creates it up front, matching
     * how the folder would already exist in a real TYPO3 installation (e.g.
     * fileadmin/user_upload/ is typically pre-created).
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
     * importUploadedResource()'s last line is
     * `return $this->createFileReferenceFromFalFileObject($uploadedFile, (int)$resourcePointer);`.
     * For a plain upload with no `submittedFile.resourcePointer` at all (the
     * overwhelmingly common case - see `convertUploadedFileToUploadInfoArray`, which
     * never sets a `submittedFile` key), `$resourcePointer` is `null`, and `(int)null`
     * is `0` - NOT `null`. So createFileReferenceFromFalFileObject() always receives
     * an actual int (`0`), which it forwards to createFileReferenceFromFalFileReferenceObject().
     * Since `0 !== null`, that method takes the "reconstitute an existing FileReference"
     * branch: `persistenceManager->getObjectByIdentifier(0, FileReference::class)`
     * returns null (uid 0 never exists), and the method's null-result fallback then
     * creates a fresh FileReference instead of calling `setOriginalResource()` on that
     * null. This affects every plain upload, regardless of whether the source is a
     * `$_FILES`-shaped array or a PSR `UploadedFile` (both funnel through the identical
     * importUploadedResource() call).
     */
    #[Test]
    public function convertFromReturnsFileReferenceForAPlainUpload(): void
    {
        // A plain upload (no submittedFile.resourcePointer) is converted into a fresh FileReference:
        // createFileReferenceFromFalFileReferenceObject() falls back to a new FileReference when the
        // (int)0 identifier resolves to null, instead of calling setOriginalResource() on null.
        $this->createUploadFolder();
        $subject = $this->getSubject();
        $configuration = $this->buildConfiguration([
            UploadedFileReferenceConverter::CONFIGURATION_UPLOAD_FOLDER => '1:/user_upload/',
        ]);
        $uploadInfo = $this->buildUploadInfo('avatar.jpg');

        $result = $subject->convertFrom($uploadInfo, ExtbaseFileReference::class, [], $configuration);

        self::assertInstanceOf(ExtbaseFileReference::class, $result);
        self::assertSame('avatar.jpg', $result->getOriginalResource()->getOriginalFile()->getName());

        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = $this->get(ResourceFactory::class);
        $uploadFolder = $resourceFactory->getFolderObjectFromCombinedIdentifier('1:/user_upload/');
        self::assertTrue($uploadFolder->hasFile('avatar.jpg'));

        // Repeating the same upload info should hit the $convertedResources cache.
        $second = $subject->convertFrom($uploadInfo, ExtbaseFileReference::class, [], $configuration);
        self::assertSame($result, $second);
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
     * Characterizes the actual (unintended-looking) behaviour: provideUploadFolder()'s
     * very first statement calls getFolderObjectFromCombinedIdentifier() outside of its
     * own try/catch, so for a genuinely missing folder that call throws and propagates
     * out before the try/catch fallback (which would create the folder) is ever reached.
     * The "creates it if it does not [exist]" docblock promise is therefore unreachable
     * dead code as the method currently stands.
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
     * convertFrom() guards `$source['submittedFile']['resourcePointer']` with
     * `is_array($source['submittedFile']) && isset(...) &&
     * is_string($source['submittedFile']['resourcePointer']) && ... !== ''` before use.
     * Without that guard, a request that submits
     * `myField[submittedFile][resourcePointer][]=x` (yielding a PHP array rather than a
     * string for `resourcePointer`) would pass a plain `isset()` check and be handed
     * straight to `HashService::validateAndStripHmac(string $string, ...)`, whose
     * parameter is strictly typed `string` - throwing an uncaught TypeError (TypeError
     * extends Error, not Exception, so a surrounding `catch (Exception)` would not catch
     * it) instead of gracefully falling through to `return null;` like every other
     * malformed-resourcePointer shape does.
     */
    #[Test]
    public function resourcePointerAsArrayReturnsNullGracefully(): void
    {
        // An array resourcePointer (e.g. from `myField[submittedFile][resourcePointer][]=x`) is
        // rejected by the is_array/is_string/non-empty guard, so convertFrom() returns null instead of
        // handing the array to HashService::validateAndStripHmac(string) and raising a TypeError.
        $subject = $this->getSubject();
        $uploadInfo = [
            'error' => \UPLOAD_ERR_NO_FILE,
            'submittedFile' => ['resourcePointer' => ['not-a-string']],
        ];

        self::assertNull($subject->convertFrom($uploadInfo, ExtbaseFileReference::class));
    }

}
