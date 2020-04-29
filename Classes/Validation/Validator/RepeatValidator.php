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

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * A repeated value validator
 */
class RepeatValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator implements SettableInterface
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
     * If the given value is equal to the repetition
     *
     * @param string $value The value
     */
    public function isValid($value)
    {
        $propertyName = str_replace('Repeat', '', $this->propertyName);
        if ($value != $this->getPropertyValue($this->model, $propertyName)) {
            $this->addError(
                $this->translateErrorMessage(
                    'error_repeatitionwasnotequal',
                    'SfRegister',
                    [$this->translateErrorMessage($propertyName, 'SfRegister')]
                ),
                1307965971
            );
        }
    }

    /**
     * Load the property value to be used for validation.
     *
     * In case the object is a doctrine proxy, we need to load the real instance first.
     *
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     */
    protected function getPropertyValue($object, string $propertyName)
    {
        // @todo add support for lazy loading proxies, if needed
        return ObjectAccess::getProperty($object, $propertyName);
    }
}
