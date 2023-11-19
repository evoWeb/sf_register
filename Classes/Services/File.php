<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Services;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;

/**
 * Service to handle file upload and deletion
 */
class File implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected array $settings = [];

    protected ServerRequestInterface $request;

    protected string $namespace = '';

    protected string $allowedFileExtensions = '';

    protected int $maxFilesize = 0;

    protected array $errors = [];

    protected int $storageUid = 1;

    protected ?ResourceStorage $storage = null;

    protected string $tempFolderIdentifier = 'frontendusers/_temp_/';

    protected ?Folder $tempFolder = null;

    protected string $imageFolderIdentifier = 'frontendusers/';

    protected ?Folder $imageFolder = null;

    public function __construct(protected ConfigurationManager $configurationManager)
    {
        try {
            $this->settings = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'SfRegister'
            );
        } catch (\Exception) {
        }

        if (($this->settings['imageFolder'] ?? '') !== '') {
            $this->setImageFolderIdentifier($this->settings['imageFolder']);
        }

        $this->allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
        $uploadMaxFileSize = $this->convertSizeStringToBytes((string)ini_get('upload_max_filesize'));
        $postMaxFileSize = $this->convertSizeStringToBytes((string)ini_get('post_max_size'));
        $this->maxFilesize = min($uploadMaxFileSize, $postMaxFileSize);
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getStorage(): ?ResourceStorage
    {
        if (!$this->storage) {
            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $this->storage = $resourceFactory->getStorageObject($this->storageUid);
        }

        return $this->storage;
    }

    public function setImageFolderIdentifier(string $imageFolder): void
    {
        $parts = GeneralUtility::trimExplode(':', $imageFolder);
        $this->storageUid = (int)$parts[0];
        $this->imageFolderIdentifier = $parts[1];
        $this->tempFolderIdentifier = rtrim($this->imageFolderIdentifier, '/') . '/_temp_/';
    }

    public function getImageFolder(): Folder
    {
        if (!$this->imageFolder) {
            $this->createFolderIfNotExist($this->imageFolderIdentifier);

            try {
                $this->imageFolder = $this->getStorage()->getFolder($this->imageFolderIdentifier);
            } catch (\Exception) {
            }
        }
        return $this->imageFolder;
    }

    public function getTempFolder(): Folder
    {
        if (!$this->tempFolder) {
            $this->createFolderIfNotExist($this->tempFolderIdentifier);

            try {
                $this->tempFolder = $this->getStorage()->getFolder($this->tempFolderIdentifier);
            } catch (\Exception) {
            }
        }
        return $this->tempFolder;
    }

    protected function convertSizeStringToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower(preg_replace('/[^gmk]/i', '', $value));
        $value = (int)preg_replace('/\D/', '', $value);
        switch ($last) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;

            case 'm':
                $value *= 1024 * 1024;
                break;

            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function addError(string $message, int $code): void
    {
        $this->errors[] = GeneralUtility::makeInstance(Error::class, $message, $code);
    }

    protected function getNamespace(): string
    {
        if ($this->namespace === '') {
            try {
                $frameworkSettings = $this->configurationManager->getConfiguration(
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
                );
                $this->namespace = strtolower(
                    'tx_' . $frameworkSettings['extensionName'] . '_' . $frameworkSettings['pluginName']
                );
            } catch (\Exception) {
                $this->namespace = 'tx_sfregister_create';
            }
        }

        return $this->namespace;
    }

    protected function getUploadedFileInfo(): ?UploadedFile
    {
        $fileData = $this->request->getUploadedFiles()['user']['image'][0] ?? null;

        if ($fileData instanceof UploadedFile) {
            $filename = str_replace([chr(0), ' '], ['', '_'], $fileData->getClientFilename());
            if ($filename !== '' && GeneralUtility::validPathStr($filename)) {
                if (($this->settings['useEncryptedFilename'] ?? false)) {
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $filename = sha1(
                        $filename . uniqid('sfregister')
                        . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']
                    )  . '.' . $extension;
                }
                if ($fileData->getClientFilename() !== $filename) {
                    $fileData = new UploadedFile(
                        $fileData->getStream(),
                        $fileData->getSize(),
                        $fileData->getError(),
                        $filename,
                        $fileData->getClientMediaType(),
                    );
                }
            }
        }

        return $fileData;
    }

    public function isValid(): bool
    {
        $fileData = $this->getUploadedFileInfo();
        if ($fileData instanceof UploadedFile) {
            $fileExtension = pathinfo($fileData->getClientFilename(), PATHINFO_EXTENSION);

            $result = $this->isAllowedFilesize((int)$fileData->getSize());
            $result = $result && $this->isAllowedFileExtension($fileExtension);
        } else {
            $result = true;
        }
        return $result;
    }

    protected function isAllowedFilesize(int $filesize): bool
    {
        $result = true;

        if ($filesize > $this->maxFilesize) {
            $this->addError(LocalizationUtility::translate('error_image_filesize', 'SfRegister'), 1296591064);
            $result = false;
        }

        return $result;
    }

    protected function isAllowedFileExtension(string $fileExtension): bool
    {
        $result = true;

        if (
            $fileExtension !== ''
            && !GeneralUtility::inList($this->allowedFileExtensions, strtolower($fileExtension))
        ) {
            $this->addError(LocalizationUtility::translate('error_image_extension', 'SfRegister'), 1296591065);
            $result = false;
        }

        return $result;
    }

    /**
     * Move a temporary uploaded file to the upload folder
     *
     * @return ?FileInterface
     */
    public function moveTempFileToTempFolder(): ?FileInterface
    {
        // @todo where is this called?
        $result = null;
        $fileData = $this->getUploadedFileInfo();

        if ($fileData instanceof UploadedFile) {
            try {
                /** @var ResourceStorage $resourceStorage */
                $resourceStorage = GeneralUtility::makeInstance(ResourceStorage::class);
                $result = $resourceStorage->addFile(
                    $fileData->getTemporaryFileName(),
                    $this->getTempFolder(),
                    $fileData->getClientFilename()
                );
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage(), $exception->getTrace());
            }
        }

        return $result;
    }

    protected function createFolderIfNotExist(string $uploadFolder): void
    {
        if (!$this->getStorage()->hasFolder($uploadFolder)) {
            try {
                $this->getStorage()->createFolder($uploadFolder);
            } catch (\Exception) {
            }
        }
    }

    public function moveFileFromTempFolderToUploadFolder(?FileReference $image): void
    {
        if (!empty($image)) {
            $file = $image->getOriginalResource()->getOriginalFile();
            try {
                $file->getStorage()->moveFile($file, $this->getImageFolder());
            } catch (\Exception $exception) {
                $this->logger->info(
                    'sf_register: Image ' . $file->getName() . ' could not be moved! ' . $exception->getMessage()
                );
            }
        }
    }

    public function removeFile(FileReference $fileReference): string
    {
        $image = $fileReference->getOriginalResource()->getOriginalFile();
        $folder = $image->getParentFolder();
        $imageNameAndPath = Environment::getPublicPath() . '/'
            . $folder->getName() . '/' . $image->getIdentifier();

        if (@file_exists($imageNameAndPath)) {
            unlink($imageNameAndPath);
        }

        return $image->getIdentifier();
    }

    protected function getFilepath(string $filename): string
    {
        $filenameParts = GeneralUtility::trimExplode('/', $filename, true);

        $result = implode('/', array_slice($filenameParts, 0, -1));
        if (!in_array($result, [$this->tempFolderIdentifier, $this->imageFolderIdentifier])) {
            $result = '';
        }

        return $result;
    }

    protected function getFilename(string $filename): string
    {
        $filenameParts = GeneralUtility::trimExplode('/', $filename, true);

        return array_pop($filenameParts);
    }
}
