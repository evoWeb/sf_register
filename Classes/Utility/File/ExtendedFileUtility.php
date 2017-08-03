<?php
namespace Evoweb\SfRegister\Utility\File;

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

/**
 * Extend of ExtenedFileUtility to have a public method for func_move
 */
class ExtendedFileUtility extends \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility
{
    /**
     * File service
     *
     * @var \Evoweb\SfRegister\Services\File
     */
    protected $fileService;

    /**
     * @param \Evoweb\SfRegister\Services\File $fileService
     *
     * @return void
     */
    public function injectFileService(\Evoweb\SfRegister\Services\File $fileService)
    {
        $this->fileService = $fileService;
    }

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
        if (strpos($identifier, $this->fileService->getImageFolder()) === 0
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
