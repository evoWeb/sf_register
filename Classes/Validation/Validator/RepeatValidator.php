<?php
namespace Evoweb\SfRegister\Validation\Validator;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

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
    protected function getPropertyValue($object, $propertyName)
    {
        // @todo add support for lazy loading proxies, if needed
        if (ObjectAccess::isPropertyGettable($object, $propertyName)) {
            return ObjectAccess::getProperty($object, $propertyName);
        }
        return ObjectAccess::getProperty($object, $propertyName, true);
    }
}
