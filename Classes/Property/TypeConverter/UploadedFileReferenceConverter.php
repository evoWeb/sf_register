<?php
namespace Evoweb\SfRegister\Property\TypeConverter;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Helmut Hummel
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFileNameException;
use TYPO3\CMS\Core\Resource\File as FalFile;
use TYPO3\CMS\Core\Resource\FileReference as FalFileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

/**
 * Class UploadedFileReferenceConverter
 */
class UploadedFileReferenceConverter extends AbstractTypeConverter
{
    /**
     * Folder where the file upload should go to (including storage).
     */
    const CONFIGURATION_UPLOAD_FOLDER = 1;

    /**
     * How to handle a upload when the name of the uploaded file conflicts.
     */
    const CONFIGURATION_UPLOAD_CONFLICT_MODE = 2;

    /**
     * Whether to replace an already present resource.
     * Useful for "maxitems = 1" fields and properties
     * with no ObjectStorage annotation.
     */
    const CONFIGURATION_ALLOWED_FILE_EXTENSIONS = 4;

    /**
     * @var string
     */
    protected $defaultUploadFolder = '1:/user_upload/';

    /**
     * @var array<string>
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = \TYPO3\CMS\Extbase\Domain\Model\FileReference::class;

    /**
     * Take precedence over the available FileReferenceConverter
     *
     * @var integer
     */
    protected $priority = 31;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var \TYPO3\CMS\Extbase\Security\Cryptography\HashService
     */
    protected $hashService;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var \TYPO3\CMS\Core\Resource\FileInterface[]
     */
    protected $convertedResources = [];

    /**
     * @param \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory
     */
    public function injectResourceFactory(\TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory)
    {
        $this->hashService = $resourceFactory;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Security\Cryptography\HashService $hashService
     */
    public function injectHashService(\TYPO3\CMS\Extbase\Security\Cryptography\HashService $hashService)
    {
        $this->hashService = $hashService;
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * @param array $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     * @throws \TYPO3\CMS\Extbase\Property\Exception
     * @return \TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder|Error
     * @api
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        if (!isset($source['error']) || $source['error'] === \UPLOAD_ERR_NO_FILE) {
            if (isset($source['submittedFile']['resourcePointer'])) {
                try {
                    $resourcePointer = $this->hashService->validateAndStripHmac(
                        $source['submittedFile']['resourcePointer']
                    );
                    if (strpos($resourcePointer, 'file:') === 0) {
                        $fileUid = substr($resourcePointer, 5);

                        return $this->createFileReferenceFromFalFileObject(
                            $this->resourceFactory->getFileObject($fileUid)
                        );
                    } else {
                        return $this->createFileReferenceFromFalFileReferenceObject(
                            $this->resourceFactory->getFileReferenceObject($resourcePointer),
                            $resourcePointer
                        );
                    }
                } catch (\InvalidArgumentException $e) {
                    // Nothing to do. No file is uploaded and resource pointer is invalid. Discard!
                }
            }

            return null;
        }

        if ($source['error'] !== \UPLOAD_ERR_OK) {
            switch ($source['error']) {
                case \UPLOAD_ERR_INI_SIZE:
                case \UPLOAD_ERR_FORM_SIZE:
                case \UPLOAD_ERR_PARTIAL:
                    return new Error(self::getUploadErrorMessage($source['error']), 1264440823);
                default:
                    return new Error(
                        'An error occurred while uploading. Please try again or contact the administrator
                         if the problem remains',
                        1340193849
                    );
            }
        }

        if (isset($this->convertedResources[$source['tmp_name']])) {
            return $this->convertedResources[$source['tmp_name']];
        }

        try {
            $resource = $this->importUploadedResource($source, $configuration);
        } catch (\Exception $e) {
            return new Error($e->getMessage(), $e->getCode());
        }

        $this->convertedResources[$source['tmp_name']] = $resource;

        return $resource;
    }

    /**
     * Returns a human-readable message for the given PHP file upload error
     * constant.
     *
     * @param integer $errorCode One of the UPLOAD_ERR_ constants
     * @return string
     */
    public static function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case \UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case \UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
            case \UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case \UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case \UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case \UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case \UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Import a resource and respect configuration given for properties
     *
     * @param array $uploadInfo
     * @param PropertyMappingConfigurationInterface $configuration
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     * @throws TypeConverterException
     * @throws ExistingTargetFileNameException
     */
    protected function importUploadedResource(array $uploadInfo, PropertyMappingConfigurationInterface $configuration)
    {
        if (!GeneralUtility::verifyFilenameAgainstDenyPattern($uploadInfo['name'])) {
            throw new TypeConverterException('Uploading files with PHP file extensions is not allowed!', 1399312430);
        }

        $allowedFileExtensions = $configuration->getConfigurationValue(
            \Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter::class,
            self::CONFIGURATION_ALLOWED_FILE_EXTENSIONS
        );

        if ($allowedFileExtensions !== null) {
            $filePathInfo = PathUtility::pathinfo($uploadInfo['name']);
            if (!GeneralUtility::inList($allowedFileExtensions, strtolower($filePathInfo['extension']))) {
                throw new TypeConverterException('File extension is not allowed!', 1399312430);
            }
        }

        $uploadFolderId = $configuration->getConfigurationValue(
            \Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter::class,
            self::CONFIGURATION_UPLOAD_FOLDER
        ) ?: $this->defaultUploadFolder;

        if (class_exists('TYPO3\\CMS\\Core\\Resource\\DuplicationBehavior')) {
            $defaultConflictMode = \TYPO3\CMS\Core\Resource\DuplicationBehavior::RENAME;
        } else {
            // @deprecated since 7.6 will be removed once 6.2 support is removed
            $defaultConflictMode = 'changeName';
        }
        $conflictMode = $configuration->getConfigurationValue(
            \Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter::class,
            self::CONFIGURATION_UPLOAD_CONFLICT_MODE
        ) ?: $defaultConflictMode;

        $uploadFolder = $this->resourceFactory->retrieveFileOrFolderObject($uploadFolderId);
        $uploadedFile = $uploadFolder->addUploadedFile($uploadInfo, $conflictMode);

        $resourcePointer = isset($uploadInfo['submittedFile']['resourcePointer'])
            && strpos($uploadInfo['submittedFile']['resourcePointer'], 'file:') === false ?
            $this->hashService->validateAndStripHmac($uploadInfo['submittedFile']['resourcePointer']) :
            null;

        $fileReferenceModel = $this->createFileReferenceFromFalFileObject($uploadedFile, $resourcePointer);

        return $fileReferenceModel;
    }

    /**
     * @param FalFile $file
     * @param int $resourcePointer
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected function createFileReferenceFromFalFileObject(FalFile $file, $resourcePointer = null)
    {
        $fileReference = $this->resourceFactory->createFileReferenceObject(
            [
                'uid_local' => $file->getUid(),
                'uid_foreign' => uniqid('NEW_'),
                'uid' => uniqid('NEW_'),
                'crop' => null,
            ]
        );

        return $this->createFileReferenceFromFalFileReferenceObject($fileReference, $resourcePointer);
    }

    /**
     * @param FalFileReference $falFileReference
     * @param int $resourcePointer
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected function createFileReferenceFromFalFileReferenceObject(
        FalFileReference $falFileReference,
        $resourcePointer = null
    ) {
        if ($resourcePointer === null) {
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $fileReference */
            $fileReference = $this->objectManager->get(\TYPO3\CMS\Extbase\Domain\Model\FileReference::class);
        } else {
            $fileReference = $this->persistenceManager->getObjectByIdentifier(
                $resourcePointer,
                \TYPO3\CMS\Extbase\Domain\Model\FileReference::class,
                false
            );
        }

        $fileReference->setOriginalResource($falFileReference);

        return $fileReference;
    }
}
