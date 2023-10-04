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

use Evoweb\SfRegister\Services\File;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator to check if the uploaded image could be handled
 */
class ImageUploadValidator extends AbstractValidator implements SetOptionsInterface
{
    public function __construct(protected File $fileService)
    {
    }

    /**
     * If the given value is a valid image
     */
    public function isValid(mixed $value): void
    {
        if (!$this->fileService->isValid()) {
            foreach ($this->fileService->getErrors() as $error) {
                $this->result->addError($error);
            }
        }
    }
}
