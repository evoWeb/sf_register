<?php

namespace Evoweb\SfRegister\Validation\Validator;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator as ExtbaseConjunctionValidator;

/**
 * Validator to chain many validators in a conjunction (logical and).
 */
class ConjunctionValidator extends ExtbaseConjunctionValidator implements SettableInterface
{
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
     * Checks if the given value is valid according to the validators of the conjunction.
     * Every validator has to be valid, to make the whole conjunction valid.
     *
     * @param mixed $value The value that should be validated
     *
     * @return Result
     */
    public function validate($value)
    {
        $validators = $this->getValidators();
        if ($validators->count() > 0) {
            /** @var Result $result */
            $result = null;
            /** @var AbstractValidator $validator */
            foreach ($validators as $validator) {
                if (method_exists($validator, 'setModel')) {
                    $validator->setModel($this->model);
                }

                if ($result === null) {
                    $result = $validator->validate($value);
                } else {
                    $result->merge($validator->validate($value));
                }
            }
        } else {
            $result = new Result();
        }

        return $result;
    }
}
