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
 * A repeated value validator
 */
class RepeatValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * Model to take repeated value of
     *
     * @var \Evoweb\SfRegister\Domain\Model\FrontendUser|\Evoweb\SfRegister\Domain\Model\Password
     */
    protected $model;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * Setter for model
     *
     * @param mixed $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    public function setPropertyName(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * If the given value is equal to the repetition
     *
     * @param string $value The value
     *
     * @return bool
     */
    public function isValid($value): bool
    {
        $result = true;

        $propertyName = str_replace('Repeat', '', $this->propertyName);
        $getterMethod = 'get' . ucfirst($propertyName);
        if ($value != $this->model->{$getterMethod}()) {
            $this->addError(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'error_repeatitionwasnotequal',
                    'SfRegister',
                    [\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($propertyName, 'SfRegister')]
                ),
                1307965971
            );
            $result = false;
        }

        return $result;
    }
}
