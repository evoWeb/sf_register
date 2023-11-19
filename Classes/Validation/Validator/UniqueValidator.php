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
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class UniqueValidator extends AbstractValidator implements SetModelInterface, SetPropertyNameInterface
{
    protected $acceptsEmptyValues = false;

    protected $supportedOptions = [
        'global' => [
            true,
            'Whether to check uniqueness globally',
            'bool'
        ],
    ];

    /**
     * Model to access user properties
     */
    protected ValidatableInterface $model;

    protected string $propertyName = '';

    public function __construct(protected FrontendUserRepository $userRepository)
    {
    }

    public function setModel(ValidatableInterface $model): void
    {
        $this->model = $model;
    }

    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * If the given value is unique either global or local
     */
    public function isValid(mixed $value): void
    {
        if ($this->options['global'] ?? false) {
            if ($this->userRepository->countByFieldGlobal($this->propertyName, $value)) {
                $this->addError(
                    $this->translateErrorMessage(
                        'error_notunique_global',
                        'SfRegister',
                        [$this->translateErrorMessage($this->propertyName, 'SfRegister')]
                    ),
                    1301599619
                );
            }
        } else {
            if ($this->userRepository->countByField($this->propertyName, $value)) {
                $this->addError(
                    $this->translateErrorMessage(
                        'error_notunique_local',
                        'SfRegister',
                        [$this->translateErrorMessage($this->propertyName, 'SfRegister')]
                    ),
                    1301599608
                );
            }
        }
    }
}
