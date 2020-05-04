<?php

namespace Evoweb\SfRegister\Services;

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

use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Service to handle file upload and deletion
 */
class File implements \TYPO3\CMS\Core\SingletonInterface, \Psr\Log\LoggerAwareInterface
{
    use \Psr\Log\LoggerAwareTrait;


    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * @var string
     */
    protected $allowedFileExtensions = '';

    /**
     * @var int
     */
    protected $maxFilesize = 0;


    /**
     * @var array
     */
    protected $errors = [];


    /**
     * @var int
     */
    protected $storageUid = 1;

    /**
     * @var ResourceStorage
     */
    protected $storage;

    /**
     * @var string
     */
    protected $tempFolderIdentifier = 'frontendusers/_temp_/';

    /**
     * @var Folder
     */
    protected $tempFolder;

    /**
     * @var string
     */
    protected $imageFolderIdentifier = 'frontendusers/';

    /**
     * @var Folder
     */
    protected $imageFolder;

    public function __construct(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;

        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );

        if (isset($this->settings['imageFolder']) && !empty($this->settings['imageFolder'])) {
            $this->setImageFolderIdentifier($this->settings['imageFolder']);
        }

        $this->allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
        $this->maxFilesize = $this->returnBytes(ini_get('upload_max_filesize') < ini_get('post_max_size') ?
            ini_get('upload_max_filesize') :
            ini_get('post_max_size'));
    }


    public function getStorage(): ResourceStorage
    {
        if (!$this->storage) {
            /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
            /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
            $this->storage = $resourceFactory->getStorageObject($this->storageUid);
        }

        return $this->storage;
    }

    public function setImageFolderIdentifier(string $imageFolder)
    {
        list($this->storageUid, $this->imageFolderIdentifier) = GeneralUtility::trimExplode(':', $imageFolder);
        $this->tempFolderIdentifier = rtrim($this->imageFolderIdentifier, '/') . '/_temp_/';
    }

    public function getImageFolder(): Folder
    {
        if (!$this->imageFolder) {
            $this->createFolderIfNotExist($this->imageFolderIdentifier);

            /** @var \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
            $this->imageFolder = $resourceFactory->retrieveFileOrFolderObject($this->imageFolderIdentifier);
        }
        return $this->imageFolder;
    }

    public function getTempFolder(): Folder
    {
        if (!$this->tempFolder) {
            $this->createFolderIfNotExist($this->tempFolderIdentifier);

            $this->tempFolder = $this->getStorage()->getFolder($this->tempFolderIdentifier);
        }
        return $this->tempFolder;
    }

    /**
     * @param string|int $value
     *
     * @return int
     */
    protected function returnBytes($value): int
    {
        $last = strtolower(substr(trim($value), -1));
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

        return (int) $value;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function addError(string $message, int $code)
    {
        $this->errors[] = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Validation\Error::class, $message, $code);
    }

    protected function getNamespace(): string
    {
        if ($this->namespace === '') {
            $frameworkSettings = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
            );
            $this->namespace = strtolower(
                'tx_' . $frameworkSettings['extensionName'] . '_' . $frameworkSettings['pluginName']
            );
        }

        return $this->namespace;
    }

    protected function getUploadedFileInfo(): array
    {
        $uploadData = $_FILES[$this->getNamespace()];
        $fileData = [];

        if (is_array($uploadData) && count($uploadData) > 0) {
            $filename = str_replace(chr(0), '', $uploadData['name']['image']);
            $type = $uploadData['type']['image'];
            $tmpName = $uploadData['tmp_name']['image'];
            $error = $uploadData['error']['image'];
            $size = $uploadData['size']['image'];

            if ($filename !== null && $filename !== '' && GeneralUtility::validPathStr($filename)) {
                if ($this->settings['useEncryptedFilename']) {
                    $filenameParts = GeneralUtility::trimExplode('.', $filename);
                    $extension = array_pop($filenameParts);
                    $filename = md5($GLOBALS['EXEC_TIME'] . mt_rand() . $filename . $tmpName . '.' . $extension
                        . $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
                }

                $fileData = [
                    'filename' => $filename,
                    'type' => $type,
                    'tmp_name' => $tmpName,
                    'error' => $error,
                    'size' => $size,
                ];
            }
        }

        return $fileData;
    }


    public function isValid(): bool
    {
        $result = true;

        $fileData = $this->getUploadedFileInfo();
        $filePathInfo = pathinfo($fileData['filename']);

        $result = $this->isAllowedFilesize((int) $fileData['size']) && $result;
        $result = $this->isAllowedFileExtension($filePathInfo['extension'] ?? '') && $result;

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
     * Move an temporary uploaded file to the upload folder
     *
     * @return \TYPO3\CMS\Core\Resource\FileInterface|NULL
     */
    public function moveTempFileToTempFolder()
    {
        $result = null;
        $fileData = $this->getUploadedFileInfo();

        if (count($fileData)) {
            $fileExtension = pathinfo($fileData['filename'], PATHINFO_EXTENSION);
            $filename = uniqid('sf_register') . '.' .  $fileExtension;

            /** @var ResourceStorage $resourceStorage */
            $resourceStorage = GeneralUtility::makeInstance(ResourceStorage::class);
            $result = $resourceStorage->addFile($fileData['tmp_name'], $this->getTempFolder(), $filename);
        }

        return $result;
    }

    protected function createFolderIfNotExist(string $uploadFolder)
    {
        if (!$this->getStorage()->hasFolder($uploadFolder)) {
            $this->getStorage()->createFolder($uploadFolder);
        }
    }

    public function moveFileFromTempFolderToUploadFolder(\TYPO3\CMS\Extbase\Domain\Model\FileReference $image)
    {
        if (empty($image)) {
            return;
        }

        $file = $image->getOriginalResource()->getOriginalFile();
        try {
            $file->getStorage()->moveFile($file, $this->imageFolder);
        } catch (\Exception $e) {
            $this->logger->info('sf_register: Image ' . $file->getName() . ' could not be moved');
        }
    }

    public function removeFile(\TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference): string
    {
        $image = $fileReference->getOriginalResource()->getOriginalFile();
        $folder = $image->getParentFolder();
        $imageNameAndPath = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/'
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
