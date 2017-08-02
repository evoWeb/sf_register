<?php
namespace Evoweb\SfRegister\Services;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Service to handle file upload and deletion
 */
class File implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * Fieldname
     *
     * @var string
     */
    protected $fieldname;

    /**
     * Configuration manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Namespace
     *
     * @var  string
     */
    protected $namespace = '';

    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Allowed file extensions
     *
     * @var string
     */
    protected $allowedFileExtensions = '';

    /**
     * Temporary folder
     *
     * @var string
     */
    protected $tempFolder = 'sf_register/_temp_/';

    /**
     * Upload folder
     *
     * @var string
     */
    protected $uploadFolder = 'sf_register/';

    /**
     * Maximal filesize
     *
     * @var integer
     */
    protected $maxFilesize = 0;

    /**
     * @var Folder
     */
    protected $imageFolder;


    /**
     * Injection of configuration manager
     *
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );

        if (isset($this->settings['imageFolder']) && !empty($this->settings['imageFolder'])) {
            $this->setImageFolder($this->settings['imageFolder']);
        }
    }


    /**
     * @return \TYPO3\CMS\Core\Resource\Folder
     */
    public function getTempFolderObject()
    {
        $this->createFolderIfNotExist($this->tempFolder);

        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
        return $resourceFactory->retrieveFileOrFolderObject('1:' . $this->tempFolder);
    }

    /**
     * @return \TYPO3\CMS\Core\Resource\Folder
     */
    public function getUploadFolderObject()
    {
        $this->createFolderIfNotExist($this->uploadFolder);

        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
        return $resourceFactory->retrieveFileOrFolderObject($this->uploadFolder);
    }

    /**
     * Setter for fieldname
     *
     * @param string $imageFolder
     *
     * @return void
     */
    public function setImageFolder($imageFolder)
    {
        list($storageUid, $folderIdentifier) = GeneralUtility::trimExplode(':', $imageFolder);
        /** @var ResourceFactory $resourceFactory */
        $resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
        /** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
        $storage = $resourceFactory->getStorageObject($storageUid);
        if ($storage->hasFolder($folderIdentifier)) {
            $folder = $storage->getFolder($folderIdentifier);
        } else {
            $folder = $storage->createFolder($folderIdentifier);
        }

        $this->imageFolder = $folder;

        $this->allowedFileExtensions = $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'];
        $this->maxFilesize = $this->returnBytes(ini_get('upload_max_filesize') < ini_get('post_max_size') ?
            ini_get('upload_max_filesize') :
            ini_get('post_max_size'));
    }

    /**
     * @param string|int $value
     *
     * @return int
     */
    protected function returnBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        switch ($last) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'g':
                $value *= 1024;
                // fallthrough intended

            /** @noinspection PhpMissingBreakStatementInspection */
            case 'm':
                $value *= 1024;
                // fallthrough intended

            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Returns an array of errors which occurred during the last isValid() call.
     *
     * @return array An array of \TYPO3\CMS\Extbase\Validation\Error objects
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Creates a new validation error object and adds it to $this->errors
     *
     * @param string $message The error message
     * @param integer $code The error code (a unix timestamp)
     *
     * @return void
     */
    protected function addError($message, $code)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->errors[] = $this->objectManager->get(\TYPO3\CMS\Extbase\Validation\Error::class, $message, $code);
    }

    /**
     * Get the namespace of the uploaded file
     *
     * @return string
     */
    protected function getNamespace()
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

    /**
     * Get file info of uploaded file
     *
     * @return array
     */
    protected function getUploadedFileInfo()
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


    /**
     * Validation of image uploads
     *
     * @return boolean
     */
    public function isValid()
    {
        $result = true;

        $fileData = $this->getUploadedFileInfo();
        $filePathinfo = pathinfo($fileData['filename']);

        $result = $this->isAllowedFilesize($fileData['size']) && $result ? true : false;
        $result = $this->isAllowedFileExtension($filePathinfo['extension']) && $result ? true : false;

        return $result;
    }

    /**
     * Check if the file size is in allowed limit
     *
     * @param integer $filesize
     *
     * @return boolean
     */
    protected function isAllowedFilesize($filesize)
    {
        $result = true;

        if ($filesize > $this->maxFilesize) {
            $this->addError(LocalizationUtility::translate('error_image_filesize', 'SfRegister'), 1296591064);
            $result = false;
        }

        return $result;
    }

    /**
     * Check if the file has an allowed file extension
     *
     * @param string $fileExtension
     *
     * @return boolean
     */
    protected function isAllowedFileExtension($fileExtension)
    {
        $result = true;

        if ($fileExtension !== null
            && !GeneralUtility::inList($this->allowedFileExtensions, strtolower($fileExtension))
        ) {
            $this->addError(LocalizationUtility::translate('error_image_extension', 'SfRegister'), 1296591064);
            $result = false;
        }

        return $result;
    }


    /**
     * Move an temporary uploaded file to the upload folder
     *
     * @return \TYPO3\CMS\Core\Resource\File|NULL
     */
    public function moveTempFileToTempFolder()
    {
        $result = null;
        $fileData = $this->getUploadedFileInfo();

        if (count($fileData)) {
            /** @var $basicFileFunctions \TYPO3\CMS\Core\Utility\File\BasicFileUtility */
            $basicFileFunctions = $this->objectManager->get(\TYPO3\CMS\Core\Utility\File\BasicFileUtility::Class);

            $fileExtension = pathinfo($fileData['filename'], PATHINFO_EXTENSION);
            $filename = uniqid('sf_register') . '.' .  $fileExtension;

            $this->createFolderIfNotExist($this->tempFolder);

            $uploadFolder = \TYPO3\CMS\Core\Utility\PathUtility::getCanonicalPath(PATH_site . $this->tempFolder);
            $uniqueFilename = $basicFileFunctions->getUniqueName($filename, $uploadFolder);

            if (GeneralUtility::upload_copy_move($fileData['tmp_name'], $uniqueFilename)) {
                /** @var ResourceFactory $resourceFactory */
                $resourceFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
                $result = $resourceFactory->retrieveFileOrFolderObject($uniqueFilename);
            }
        }

        return $result;
    }

    /**
     * Create upload folder if not exists
     *
     * @param string $uploadFolder
     *
     * @return void
     */
    protected function createFolderIfNotExist($uploadFolder)
    {
        $uploadFolder = \TYPO3\CMS\Core\Utility\PathUtility::getCanonicalPath($uploadFolder);
        if (!is_dir($uploadFolder)) {
            GeneralUtility::mkdir_deep(PATH_site . 'fileadmin/', $uploadFolder);
        }
    }

    /**
     * Move an temporary uploaded file to the upload folder
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $image
     *
     * @return void
     */
    public function moveFileFromTempFolderToUploadFolder($image)
    {
        if (empty($image)) {
            return;
        }

        $file = $image->getOriginalResource()->getOriginalFile();
        try {
            $file->getStorage()->moveFile($file, $this->imageFolder);
        } catch (\Exception $e) {
            GeneralUtility::devLog('Image ' . $file->getName() . ' could not be moved', 'sf_register');
        }
    }

    /**
     * Return image from upload folder
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference name of the file to remove
     *
     * @return string
     */
    public function removeFile($fileReference)
    {
        $image = $fileReference->getOriginalResource()->getOriginalFile();
        $folder = $image->getParentFolder();
        $imageNameAndPath = PATH_site . $folder->getName() . '/' . $image->getIdentifier();

        if (@file_exists($imageNameAndPath)) {
            unlink($imageNameAndPath);
        }

        return $image->getIdentifier();
    }

    /**
     * Getter for filepath
     *
     * @param string $filename
     *
     * @return string
     */
    protected function getFilepath($filename)
    {
        $filenameParts = GeneralUtility::trimExplode('/', $filename, true);

        $result = implode('/', array_slice($filenameParts, 0, -1));
        if (!in_array($result, [$this->tempFolder, $this->uploadFolder])) {
            $result = '';
        }

        return $result;
    }

    /**
     * Getter for filename
     *
     * @param string $filename
     *
     * @return string
     */
    protected function getFilename($filename)
    {
        $filenameParts = GeneralUtility::trimExplode('/', $filename, true);

        return array_pop($filenameParts);
    }
}
