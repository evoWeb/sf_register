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
 * A required validator to check that a value is set
 */
class RequiredValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator implements SettableInterface
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
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser|\Evoweb\SfRegister\Domain\Model\Password $model
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
     * If the given value is empty
     *
     * @param string $value The value
     */
    public function isValid($value)
    {
        if (empty($value)) {
            $this->addError(
                $this->translateErrorMessage(
                    'error_required',
                    'SfRegister',
                    [$this->translateErrorMessage($this->propertyName, 'SfRegister')]
                ),
                1305008423
            );
        }
    }
}
