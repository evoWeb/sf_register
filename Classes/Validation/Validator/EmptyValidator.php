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
 * A empty validator this is used in validation of a new created user to ensure that the uid is empty
 */
class EmptyValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * If the given value is empty
     *
     * @param string $value The value
     */
    public function isValid($value)
    {
        if (!empty($value)) {
            $this->addError(
                $this->translateErrorMessage('error_notempty', 'SfRegister'),
                1305008423
            );
        }
    }
}
