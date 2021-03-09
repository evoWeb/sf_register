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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use Evoweb\SfRegister\Services\File;

/**
 * Validator to check if the uploaded image could be handled
 */
class ImageUploadValidator extends AbstractValidator
{
    protected File $fileService;

    public function injectFileService(File $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * If the given value is set
     *
     * @param bool $value The value
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
