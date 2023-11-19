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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * A validator to check if the userid is equal to the id of the logged-in user
 */
class EqualCurrentUserValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    public function __construct(protected Context $context)
    {
    }

    /**
     * If the given value is not equal to logged-in user id
     */
    public function isValid(mixed $value): void
    {
        try {
            if ($value != $this->context->getAspect('frontend.user')->get('id')) {
                $this->addError(
                    $this->translateErrorMessage('error_notequalcurrentuser', 'SfRegister'),
                    1305009260
                );
            }
        } catch (\Exception $exception) {
            $this->addError($exception->getMessage(), $exception->getCode());
        }
    }
}
