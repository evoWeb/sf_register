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
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * A validator to check if the userid is equal to the id of the logged in user
 */
class EqualCurrentUserValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var \TYPO3\CMS\Core\Context\Context
     */
    protected $context;

    public function injectContext(\TYPO3\CMS\Core\Context\Context $context)
    {
        $this->context = $context;
    }

    /**
     * If the given value is not equal to logged in user id
     *
     * @param string $value The value
     */
    public function isValid($value)
    {
        if ($value != $this->context->getAspect('frontend.user')->get('id')) {
            $this->addError(
                $this->translateErrorMessage('error_notequalcurrentuser', 'SfRegister'),
                1305009260
            );
        }
    }
}
