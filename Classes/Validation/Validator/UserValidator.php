<?php

declare(strict_types=1);

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
use SplObjectStorage;
use Traversable;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractGenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ObjectValidatorInterface;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class UserValidator extends AbstractGenericObjectValidator
{
    /**
     * Model to access user properties
     */
    protected ValidatableInterface $model;

    /**
     * Checks if the given value is valid according to the property validators.
     */
    protected function isValid(mixed $object): void
    {
        // Behaviour-preserving: keep df53334 behaviour where a non-ValidatableInterface object is
        // assigned to the typed $model property, raising an uncaught TypeError. 30e771a's early-return
        // guard changed behaviour and is deferred to a later fix step.
        // @phpstan-ignore-next-line assign.propertyType
        $this->model = $object;
        foreach ($this->propertyValidators as $propertyName => $validators) {
            $propertyValue = $this->getPropertyValue($object, $propertyName);
            $this->checkProperty($propertyValue, $validators, $propertyName);
        }
    }

    /**
     * Checks if the specified property of the given object is valid, and adds
     * found errors to the $messages object.
     *
     * @param SplObjectStorage<ValidatorInterface, mixed> $validators The validators to be called on the value
     */
    protected function checkProperty(mixed $value, Traversable $validators, string $propertyName): void
    {
        /** @var ?Result $result */
        $result = null;
        foreach ($validators as $validator) {
            if ($validator instanceof SetModelInterface) {
                $validator->setModel($this->model);
            }

            if ($validator instanceof ObjectValidatorInterface) {
                $validator->setValidatedInstancesContainer($this->validatedInstancesContainer);
            }
            $currentResult = $validator->validate($value);
            if ($currentResult->hasMessages()) {
                if ($result == null) {
                    $result = $currentResult;
                } else {
                    $result->merge($currentResult);
                }
            }
        }
        if ($result != null) {
            $this->result->forProperty($propertyName)->merge($result);
        }
    }

    /**
     * Checks if validator can validate the object
     */
    public function canValidate(mixed $object): bool
    {
        return $object instanceof ValidatableInterface;
    }
}
