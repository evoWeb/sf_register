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

namespace Evoweb\SfRegister\Tests\Functional\Services;

use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;

class FileTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/sys_file_storage.csv');

        $this->createServerRequest();
        $this->initializeFrontendTypoScript();

        // The service resolves validation error labels via LocalizationUtility::translate(),
        // which needs a resolvable language on the current request.
        $language = new SiteLanguage(0, 'en_US.UTF-8', new Uri('https://typo3-testing.local/'), ['title' => 'English']);
        $this->request = $this->request->withAttribute('language', $language);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;
    }

    protected function getSubject(): File
    {
        /** @var File $subject */
        $subject = $this->get(File::class);
        $subject->setRequest($this->request);
        return $subject;
    }

    #[Test]
    public function isAllowedFileExtensionReturnsTrueForConfiguredExtension(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'isAllowedFileExtension');

        self::assertTrue($method->invoke($subject, 'jpg'));
    }

    #[Test]
    public function isAllowedFileExtensionIsCaseInsensitive(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'isAllowedFileExtension');

        self::assertTrue($method->invoke($subject, 'JPG'));
    }

    #[Test]
    public function isAllowedFileExtensionReturnsTrueForEmptyExtension(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'isAllowedFileExtension');

        self::assertTrue($method->invoke($subject, ''));
    }

    #[Test]
    public function isAllowedFileExtensionReturnsFalseForDisallowedExtension(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'isAllowedFileExtension');

        self::assertFalse($method->invoke($subject, 'exe'));
    }

    /**
     * @return array<string, array{0: int, 1: bool}>
     */
    public static function isAllowedFilesizeDataProvider(): array
    {
        return [
            'below limit is allowed' => [-1, true],
            'equal to limit is allowed' => [0, true],
            'above limit is not allowed' => [1, false],
        ];
    }

    #[DataProvider('isAllowedFilesizeDataProvider')]
    #[Test]
    public function isAllowedFilesizeRespectsBoundary(int $offsetFromLimit, bool $expected): void
    {
        $subject = $this->getSubject();
        $maxFilesize = $this->getPrivateProperty($subject, 'maxFilesize')->getValue($subject);
        self::assertIsInt($maxFilesize);
        $method = $this->getPrivateMethod($subject, 'isAllowedFilesize');

        self::assertSame($expected, $method->invoke($subject, $maxFilesize + $offsetFromLimit));
    }

    #[Test]
    public function getImageFolderReturnsConfiguredFolder(): void
    {
        $subject = $this->getSubject();

        $imageFolder = $subject->getImageFolder();

        self::assertInstanceOf(Folder::class, $imageFolder);
        self::assertSame('frontendusers', $imageFolder->getName());
    }

    #[Test]
    public function getTempFolderReturnsConfiguredFolder(): void
    {
        $subject = $this->getSubject();

        $tempFolder = $subject->getTempFolder();

        self::assertInstanceOf(Folder::class, $tempFolder);
        self::assertSame('_temp_', $tempFolder->getName());
        self::assertStringContainsString('frontendusers/_temp_', $tempFolder->getIdentifier());
    }

    #[Test]
    public function getFilenameReturnsLastPathSegment(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getFilename');

        self::assertSame('avatar.jpg', $method->invoke($subject, 'frontendusers/_temp_/avatar.jpg'));
    }

    #[Test]
    public function getUploadedFileInfoReturnsUploadedFileFromRequest(): void
    {
        $uploadedFile = $this->createUploadedFile('avatar.jpg', 'image content');
        $this->request = $this->request->withUploadedFiles(['user' => ['image' => [0 => $uploadedFile]]]);

        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getUploadedFileInfo');

        /** @var UploadedFile $result */
        $result = $method->invoke($subject);
        self::assertInstanceOf(UploadedFile::class, $result);
        self::assertSame('avatar.jpg', $result->getClientFilename());
    }

    #[Test]
    public function getUploadedFileInfoReplacesSpacesInFilename(): void
    {
        $uploadedFile = $this->createUploadedFile('my avatar.jpg', 'image content');
        $this->request = $this->request->withUploadedFiles(['user' => ['image' => [0 => $uploadedFile]]]);

        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getUploadedFileInfo');

        /** @var UploadedFile $result */
        $result = $method->invoke($subject);
        self::assertInstanceOf(UploadedFile::class, $result);
        self::assertSame('my_avatar.jpg', $result->getClientFilename());
    }

    #[Test]
    public function getUploadedFileInfoReturnsNullWithoutUpload(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getUploadedFileInfo');

        self::assertNull($method->invoke($subject));
    }

    #[Test]
    public function isValidReturnsTrueForAllowedUpload(): void
    {
        $uploadedFile = $this->createUploadedFile('avatar.jpg', 'image content');
        $this->request = $this->request->withUploadedFiles(['user' => ['image' => [0 => $uploadedFile]]]);

        $subject = $this->getSubject();

        self::assertTrue($subject->isValid());
    }

    #[Test]
    public function isValidReturnsFalseForDisallowedExtension(): void
    {
        $uploadedFile = $this->createUploadedFile('malware.exe', 'binary content');
        $this->request = $this->request->withUploadedFiles(['user' => ['image' => [0 => $uploadedFile]]]);

        $subject = $this->getSubject();

        self::assertFalse($subject->isValid());
    }

    #[Test]
    public function isValidReturnsTrueWithoutUpload(): void
    {
        $subject = $this->getSubject();

        self::assertTrue($subject->isValid());
    }

    #[Test]
    public function moveFileFromTempFolderToUploadFolderMovesFileToImageFolder(): void
    {
        $subject = $this->getSubject();
        $storage = $subject->getStorage();
        self::assertInstanceOf(ResourceStorage::class, $storage);

        $tempFolder = $subject->getTempFolder();
        $imageFolder = $subject->getImageFolder();

        $localFile = $this->createJpegFile('avatar.jpg');
        $file = $storage->addFile($localFile, $tempFolder, 'avatar.jpg');

        self::assertTrue($tempFolder->hasFile('avatar.jpg'));
        self::assertFalse($imageFolder->hasFile('avatar.jpg'));

        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = $this->get(ResourceFactory::class);
        $extbaseFileReference = new ExtbaseFileReference();
        $extbaseFileReference->setOriginalResource(
            $resourceFactory->createFileReferenceObject([
                'uid' => 0,
                'uid_local' => $file->getUid(),
            ])
        );

        $subject->moveFileFromTempFolderToUploadFolder($extbaseFileReference);

        self::assertTrue($imageFolder->hasFile('avatar.jpg'));
        self::assertFalse($tempFolder->hasFile('avatar.jpg'));
    }

    protected function createUploadedFile(string $filename, string $content): UploadedFile
    {
        $localFile = $this->createTestFile($filename, $content);
        return new UploadedFile($localFile, (int)filesize($localFile), UPLOAD_ERR_OK, $filename, 'image/jpeg');
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
}
