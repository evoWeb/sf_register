<?php

declare(strict_types=1);

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

namespace Evoweb\SfRegister\Property\TypeConverter;

use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Core\Http\UploadedFile;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\File as File;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

class UploadedFileReferenceConverter extends AbstractTypeConverter
{
    /**
     * Folder where the file upload should go to (including storage).
     */
    public const CONFIGURATION_UPLOAD_FOLDER = 1;

    /**
     * How to handle an upload when the name of the uploaded file conflicts.
     */
    public const CONFIGURATION_UPLOAD_CONFLICT_MODE = 2;

    /**
     * Whether to replace an already present resource.
     * Useful for "maxitems = 1" fields and properties
     * with no ObjectStorage annotation.
     */
    public const CONFIGURATION_FILE_VALIDATORS = 4;

    protected string $defaultUploadFolder = '1:/user_upload/';

    /**
     * @var FileReference[]
     */
    protected array $convertedResources = [];

    public const RESOURCE_POINTER_PREFIX = 'sf-register-upload';

    public function __construct(
        protected ResourceFactory $resourceFactory,
        protected HashService $hashService,
        protected PersistenceManager $persistenceManager
    ) {}

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * @param array|UploadedFile $source
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        ?PropertyMappingConfigurationInterface $configuration = null
    ): FileReference|Error|null {
        if ($source instanceof UploadedFile) {
            $source = $this->convertUploadedFileToUploadInfoArray($source);
        }
        if (!isset($source['error']) || $source['error'] === \UPLOAD_ERR_NO_FILE) {
            if (isset($source['submittedFile']['resourcePointer'])) {
                try {
                    $resourcePointer = $this->hashService->validateAndStripHmac(
                        $source['submittedFile']['resourcePointer'],
                        self::RESOURCE_POINTER_PREFIX
                    );
                    if (str_starts_with($resourcePointer, 'file:')) {
                        $fileUid = (int)substr($resourcePointer, 5);
                        $resource = $this->createFileReferenceFromFalFileObject(
                            $this->resourceFactory->getFileObject($fileUid)
                        );
                    } else {
                        $resource = $this->createFileReferenceFromFalFileReferenceObject(
                            $this->resourceFactory->getFileReferenceObject((int)$resourcePointer),
                            (int)$resourcePointer
                        );
                    }
                    return $resource;
                } catch (\Exception) {
                    // Nothing to do. No file is uploaded and resource pointer is invalid. Discard!
                }
            }
            return null;
        }

        if ($source['error'] !== \UPLOAD_ERR_OK) {
            return GeneralUtility::makeInstance(
                Error::class,
                $this->getUploadErrorMessage($source['error']),
                1471715915
            );
        }

        if (isset($this->convertedResources[$source['tmp_name']])) {
            return $this->convertedResources[$source['tmp_name']];
        }

        if ($configuration === null) {
            throw new \InvalidArgumentException('Argument $configuration must not be null', 1589183114);
        }

        try {
            $resource = $this->importUploadedResource($source, $configuration);
        } catch (\Exception $e) {
            return GeneralUtility::makeInstance(Error::class, $e->getMessage(), $e->getCode());
        }

        $this->convertedResources[$source['tmp_name']] = $resource;
        return $resource;
    }

    /**
     * Import a resource and respect configuration given for properties
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

        $uploadFolderId = $configuration->getConfigurationValue(
            self::class,
            (string)self::CONFIGURATION_UPLOAD_FOLDER
        ) ?: $this->defaultUploadFolder;
        $conflictMode = $configuration->getConfigurationValue(
            self::class,
            (string)self::CONFIGURATION_UPLOAD_CONFLICT_MODE
        ) ?: DuplicationBehavior::RENAME;

        $validators = $configuration->getConfigurationValue(
            self::class,
            (string)self::CONFIGURATION_FILE_VALIDATORS
        );
        if ($validators !== null) {
            $fileExtension = PathUtility::pathinfo($uploadInfo['name'], PATHINFO_EXTENSION);
            if (!GeneralUtility::inList($validators, strtolower($fileExtension))) {
                throw new TypeConverterException('File extension is not allowed!', 1399312430);
            }
        }

        $uploadFolder = $this->provideUploadFolder($uploadFolderId);
        /** @var File $uploadedFile */
        $uploadedFile = $uploadFolder->addUploadedFile($uploadInfo, $conflictMode);

        $resourcePointer = isset($uploadInfo['submittedFile']['resourcePointer'])
            && !str_contains($uploadInfo['submittedFile']['resourcePointer'], 'file:')
            ? (int)$this->hashService->validateAndStripHmac(
                $uploadInfo['submittedFile']['resourcePointer'],
                self::RESOURCE_POINTER_PREFIX
            )
            : null;

        return $this->createFileReferenceFromFalFileObject($uploadedFile, (int)$resourcePointer);
    }

    protected function createFileReferenceFromFalFileObject(
        File $file,
        ?int $resourcePointer = null
    ): FileReference {
        $fileReference = $this->resourceFactory->createFileReferenceObject(
            [
                'uid_local' => $file->getUid(),
                'uid_foreign' => StringUtility::getUniqueId('NEW_'),
                'uid' => StringUtility::getUniqueId('NEW_'),
                'crop' => null,
            ]
        );

        return $this->createFileReferenceFromFalFileReferenceObject($fileReference, $resourcePointer);
    }

    /**
     * In case no $resourcePointer is given a new file reference domain object
     * will be returned. Otherwise, the file reference is reconstituted from
     * storage and will be updated(!) with the provided $falFileReference.
     */
    protected function createFileReferenceFromFalFileReferenceObject(
        CoreFileReference $falFileReference,
        ?int $resourcePointer = null
    ): FileReference {
        if ($resourcePointer === null) {
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

    protected function getUploadErrorMessage(int $errorCode): string
    {
        $key = match ($errorCode) {
            \UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE => 'upload.error.150530345',
            \UPLOAD_ERR_PARTIAL => 'upload.error.150530346',
            \UPLOAD_ERR_NO_FILE => 'upload.error.150530347',
            default => 'upload.error.150530348',
        };
        return $this->getLanguageService()->sL(
            'LLL:EXT:sf_register/Resources/Private/Language/locallang.xlf:' . $key
        );
    }

    /**
     * Ensures that upload folder exists, creates it if it does not.
     */
    protected function provideUploadFolder(string $uploadFolderIdentifier): Folder
    {
        $this->resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderIdentifier);

        try {
            return $this->resourceFactory->getFolderObjectFromCombinedIdentifier($uploadFolderIdentifier);
        } catch (\Exception) {
            [$storageId, $storagePath] = explode(':', $uploadFolderIdentifier, 2);
            $storage = $this->resourceFactory->getStorageObject((int)$storageId);
            $folderNames = GeneralUtility::trimExplode('/', $storagePath, true);
            $uploadFolder = $this->provideTargetFolder($storage->getRootLevelFolder(), ...$folderNames);
            $this->provideFolderInitialization($uploadFolder);
            return $uploadFolder;
        }
    }

    /**
     * Ensures that particular target folder exists, creates it if it does not.
     */
    protected function provideTargetFolder(Folder $parentFolder, string $folderName): Folder
    {
        return $parentFolder->hasFolder($folderName)
            ? $parentFolder->getSubfolder($folderName)
            : $parentFolder->createFolder($folderName);
    }

    /**
     * Creates empty index.html file to avoid directory indexing,
     * in case it does not exist yet.
     */
    protected function provideFolderInitialization(Folder $parentFolder): void
    {
        if (!$parentFolder->hasFile('index.html')) {
            $parentFolder->createFile('index.html');
        }
    }

    protected function convertUploadedFileToUploadInfoArray(UploadedFile $uploadedFile): array
    {
        return [
            'name' => $uploadedFile->getClientFilename(),
            'tmp_name' => $uploadedFile->getTemporaryFileName(),
            'size' => $uploadedFile->getSize(),
            'error' => $uploadedFile->getError(),
            'type' => $uploadedFile->getClientMediaType(),
        ];
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
