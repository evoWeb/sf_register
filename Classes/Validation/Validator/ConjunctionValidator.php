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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractCompositeValidator;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator to chain many validators in a conjunction (logical and).
 */
class ConjunctionValidator extends AbstractCompositeValidator implements SettableInterface
{
    /**
     * Model to take repeated value of
     *
     * @var FrontendUser|Password
     */
    protected FrontendUser|Password $model;

    protected string $propertyName = '';

    public function setModel(FrontendUser|Password $model)
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
    public function validate(mixed $value): Result
    {
        $result = new Result();
        $validators = $this->getValidators();
        if ($validators->count() > 0) {
            /** @var AbstractValidator $validator */
            foreach ($validators as $validator) {
                if (method_exists($validator, 'setModel')) {
                    $validator->setModel($this->model);
                }

                $result->merge($validator->validate($value));
            }
        }

        return $result;
    }
}
