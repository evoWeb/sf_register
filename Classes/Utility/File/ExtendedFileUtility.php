<?php
namespace Evoweb\SfRegister\Utility\File;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Extend of ExtenedFileUtility to have a public method for func_move
 */
class ExtendedFileUtility extends \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility
{
    /**
     * File service
     *
     * @var \Evoweb\SfRegister\Services\File
     * @inject
     */
    protected $fileService;

    /**
     * Move file function
     *
     * @param array $commands Commands
     *
     * @return \TYPO3\CMS\Core\Resource\File
     */
    public function funcMove(array $commands)
    {
        return $this->func_move($commands);
    }

    /**
     * Get file object
     *
     * @param string $identifier Identifier
     *
     * @return \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileInterface|\TYPO3\CMS\Core\Resource\Folder
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFileAccessPermissionsException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InvalidFileException
     */
    protected function getFileObject($identifier)
    {
        $object = $this->fileFactory->retrieveFileOrFolderObject($identifier);
        if (!is_object($object)) {
            throw new \TYPO3\CMS\Core\Resource\Exception\InvalidFileException(
                'The item ' . $identifier . ' was not a file or directory!!',
                1320122453
            );
        }

        // early escape for fe_users path
        if (strpos($identifier, $this->fileService->getUploadFolder()) === 0
            || strpos($identifier, $this->fileService->getTempFolder()) === 0
        ) {
            return $object;
        }

        // continue like the original one....
        if ($object->getStorage()->getUid() === 0) {
            throw new \TYPO3\CMS\Core\Resource\Exception\InsufficientFileAccessPermissionsException(
                'You are not allowed to access files outside your storages',
                1375889830
            );
        }

        return $object;
    }
}
