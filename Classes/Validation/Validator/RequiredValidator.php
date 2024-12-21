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
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * A required validator to check that a value is set
 */
class RequiredValidator extends AbstractValidator implements SetModelInterface, SetPropertyNameInterface
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
     * If the given value is not empty
     */
    public function isValid(mixed $value): void
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
