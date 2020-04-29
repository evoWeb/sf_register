<?php

namespace Evoweb\SfRegister\Validation\Validator;

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

/**
 * Validator to check if the uploaded image could be handled
 */
class ImageUploadValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * @var \Evoweb\SfRegister\Services\File
     */
    protected $fileService;

    public function injectFileService(\Evoweb\SfRegister\Services\File $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * If the given value is set
     *
     * @param boolean $value The value
     */
    public function isValid($value)
    {
        if (!$this->fileService->isValid()) {
            foreach ($this->fileService->getErrors() as $error) {
                $this->result->addError($error);
            }
        }
    }
}
