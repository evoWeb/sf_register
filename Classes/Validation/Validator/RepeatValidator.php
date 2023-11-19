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

use Evoweb\SfRegister\Domain\Model\ValidatableInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * A repeated value validator
 */
class RepeatValidator extends AbstractValidator implements SetModelInterface, SetPropertyNameInterface
{
    protected $acceptsEmptyValues = false;

    /**
     * Model to access user properties
     */
    protected ValidatableInterface $model;

    protected string $propertyName = '';

    public function setModel(ValidatableInterface $model): void
    {
        $this->model = $model;
    }

    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * If the given value is equal to the repetition
     */
    public function isValid(mixed $value): void
    {
        try {
            $propertyName = str_replace('Repeat', '', $this->propertyName);
            if ($value != ObjectAccess::getProperty($this->model, $propertyName)) {
                $this->addError(
                    $this->translateErrorMessage(
                        'error_repeatitionwasnotequal',
                        'SfRegister',
                        [$this->translateErrorMessage($propertyName, 'SfRegister')]
                    ),
                    1307965971
                );
            }
        } catch (\Exception $exception) {
            $this->addError($exception->getMessage(), $exception->getCode());
        }
    }
}
