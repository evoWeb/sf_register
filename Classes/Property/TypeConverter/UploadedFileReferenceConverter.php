<?php

namespace Evoweb\SfRegister\Property\TypeConverter;

/*
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
 */

use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\File as FalFile;
use TYPO3\CMS\Core\Resource\FileReference as FalFileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;

class UploadedFileReferenceConverter extends AbstractTypeConverter
{
    /**
     * Folder where the file upload should go to (including storage).
     */
    public const CONFIGURATION_UPLOAD_FOLDER = '1';

    /**
     * How to handle an upload when the name of the uploaded file conflicts.
     */
    public const CONFIGURATION_UPLOAD_CONFLICT_MODE = '2';

    /**
     * Whether to replace an already present resource.
     * Useful for "maxitems = 1" fields and properties
     * with no ObjectStorage annotation.
     */
    public const CONFIGURATION_ALLOWED_FILE_EXTENSIONS = '4';

    protected string $defaultUploadFolder = '1:/user_upload/';

    /**
     * @var array<string>
     */
    protected $sourceTypes = ['array'];

    /**
     * @var string
     */
    protected $targetType = FileReference::class;

    /**
     * Take precedence over the available FileReferenceConverter
     *
     * @var int
     */
    protected $priority = 31;

    protected ResourceFactory $resourceFactory;

    protected HashService $hashService;

    protected PersistenceManager $persistenceManager;

    /**
     * @var FileReference[]
     */
    protected array $convertedResources = [];

    public function __construct(
        ResourceFactory $resourceFactory,
        HashService $hashService,
        PersistenceManager $persistenceManager
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->hashService = $hashService;
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * @param array $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param ?PropertyMappingConfigurationInterface $configuration
     *
     * @return FileReference|Error
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        ?PropertyMappingConfigurationInterface $configuration = null
    ) {
        if (!isset($source['error']) || $source['error'] === \UPLOAD_ERR_NO_FILE) {
            $result = null;
            if (isset($source['submittedFile']['resourcePointer'])) {
                try {
                    $resourcePointer = $this->hashService->validateAndStripHmac(
                        $source['submittedFile']['resourcePointer']
                    );
                    if (str_starts_with($resourcePointer, 'file:')) {
                        $fileUid = (int)substr($resourcePointer, 5);

                        $result = $this->createFileReferenceFromFalFileObject(
                            $this->resourceFactory->getFileObject($fileUid)
                        );
                    } else {
                        $result = $this->createFileReferenceFromFalFileReferenceObject(
                            $this->resourceFactory->getFileReferenceObject((int)$resourcePointer),
                            (int)$resourcePointer
                        );
                    }
                } catch (\InvalidArgumentException $e) {
                    // Nothing to do. No file is uploaded and resource pointer is invalid. Discard!
                }
            }

            return $result;
        }

        if ($source['error'] !== \UPLOAD_ERR_OK) {
            return match ($source['error']) {
                \UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE, \UPLOAD_ERR_PARTIAL =>
                    new Error(self::getUploadErrorMessage($source['error']), 1264440823),
                default => new Error(
                    'An error occurred while uploading. Please try again or contact the administrator
                         if the problem remains',
                    1340193849
                ),
            };
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

    public static function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            \UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            \UPLOAD_ERR_FORM_SIZE =>
                'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            \UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            \UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            \UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            \UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            \UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
            default => 'Unknown upload error',
        };
    }

    /**
     * Import a resource and respect configuration given for properties
     *
     * @param array $uploadInfo
     * @param PropertyMappingConfigurationInterface $configuration
     *
     * @return FileReference
     *
     * @throws TypeConverterException
     */
    protected function importUploadedResource(
        array $uploadInfo,
        PropertyMappingConfigurationInterface $configuration
    ): FileReference {
        /** @var FileNameValidator $fileNameValidator */
        $fileNameValidator = GeneralUtility::makeInstance(FileNameValidator::class);
        if (!$fileNameValidator->isValid((string)$uploadInfo['name'])) {
            throw new TypeConverterException('Uploading files with PHP file extensions is not allowed!', 1399312430);
        }

        $allowedFileExtensions = $configuration->getConfigurationValue(
            UploadedFileReferenceConverter::class,
            self::CONFIGURATION_ALLOWED_FILE_EXTENSIONS
        );

        if ($allowedFileExtensions !== null) {
            $filePathInfo = PathUtility::pathinfo($uploadInfo['name']);
            if (!GeneralUtility::inList($allowedFileExtensions, strtolower($filePathInfo['extension']))) {
                throw new TypeConverterException('File extension is not allowed!', 1399312430);
            }
        }

        $uploadFolderId = $configuration->getConfigurationValue(
            self::class,
            self::CONFIGURATION_UPLOAD_FOLDER
        ) ?: $this->defaultUploadFolder;

        $conflictMode = $configuration->getConfigurationValue(
            self::class,
            self::CONFIGURATION_UPLOAD_CONFLICT_MODE
        ) ?: DuplicationBehavior::RENAME;

        $uploadFolder = $this->resourceFactory->retrieveFileOrFolderObject($uploadFolderId);
        $uploadedFile = $uploadFolder->addUploadedFile($uploadInfo, $conflictMode);

        $resourcePointer = isset($uploadInfo['submittedFile']['resourcePointer'])
            && !str_contains($uploadInfo['submittedFile']['resourcePointer'], 'file:') ?
            $this->hashService->validateAndStripHmac($uploadInfo['submittedFile']['resourcePointer']) :
            null;

        return $this->createFileReferenceFromFalFileObject($uploadedFile, (int)$resourcePointer);
    }

    protected function createFileReferenceFromFalFileObject(
        FalFile $file,
        int $resourcePointer = 0
    ): FileReference {
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

    protected function createFileReferenceFromFalFileReferenceObject(
        FalFileReference $falFileReference,
        int $resourcePointer = 0
    ): FileReference {
        if ($resourcePointer === 0) {
            /** @var FileReference $fileReference */
            $fileReference = GeneralUtility::makeInstance(FileReference::class);
        } else {
            $fileReference = $this->persistenceManager->getObjectByIdentifier(
                $resourcePointer,
                FileReference::class
            );
        }

        $fileReference->setOriginalResource($falFileReference);

        return $fileReference;
    }
}
