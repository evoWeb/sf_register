<?php

declare(strict_types=1);

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

namespace Evoweb\SfRegister\Validation\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * An empty validator this is used in validation of a new created user to ensure that the uid is empty
 */
class EmptyValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * If the given value is empty
     */
    public function isValid(mixed $value): void
    {
        if (!empty($value)) {
            $this->addError(
                $this->translateErrorMessage('error_notempty', 'SfRegister'),
                1305008423
            );
        }
    }
}
