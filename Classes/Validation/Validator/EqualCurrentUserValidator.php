<?php
namespace Evoweb\SfRegister\Validation\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
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
     * If the given value is empty
     *
     * @param string $value The value
     *
     * @return bool
     */
    public function isValid($value): bool
    {
        $result = true;

        if ($value != $this->context->getAspect('frontend.user')->get('id')) {
            $this->addError(
                $this->translateErrorMessage('error_notequalcurrentuser', 'SfRegister'),
                1305009260
            );
            $result = false;
        }

        return $result;
    }
}
