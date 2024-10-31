<?php

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

use Evoweb\SfRegister\Domain\Model\ValidatableInterface;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractCompositeValidator;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator to chain many validators in a conjunction (logical and).
 */
class ConjunctionValidator extends AbstractCompositeValidator implements SetModelInterface, SetPropertyNameInterface
{
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
     * Checks if the given value is valid according to the validators of the conjunction.
     * Every validator has to be valid, to make the whole conjunction valid.
     */
    public function validate(mixed $value): Result
    {
        $result = new Result();

        /** @var AbstractValidator $validator */
        foreach ($this->getValidators() as $validator) {
            if ($validator instanceof SetModelInterface) {
                $validator->setModel($this->model);
            }

            $result->merge($validator->validate($value));
        }

        return $result;
    }
}
