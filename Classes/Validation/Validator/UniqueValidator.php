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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;

class UniqueValidator extends AbstractValidator implements InjectableInterface, SettableInterface
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var array
     */
    protected $supportedOptions = [
        'global' => [
            true,
            'Whether to check uniqueness globally',
            'bool'
        ],
    ];

    protected ?FrontendUserRepository $userRepository = null;

    /**
     * Model to take repeated value of
     *
     * @var FrontendUser|Password
     */
    protected FrontendUser|Password $model;

    protected string $propertyName = '';

    public function __construct(FrontendUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Setter for model
     *
     * @param FrontendUser|Password $model
     */
    public function setModel(FrontendUser|Password $model)
    {
        $this->model = $model;
    }

    public function setPropertyName(string $propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * If the given passwords are valid
     *
     * @param string $value The value
     */
    public function isValid(mixed $value): void
    {
        if ($this->userRepository->countByField($this->propertyName, $value)) {
            $this->addError(
                $this->translateErrorMessage(
                    'error_notunique_local',
                    'SfRegister',
                    [$this->translateErrorMessage($this->propertyName, 'SfRegister')]
                ),
                1301599608
            );
        } elseif (
            isset($this->options['global'])
            && $this->options['global']
            && $this->userRepository->countByFieldGlobal($this->propertyName, $value)
        ) {
            $this->addError(
                $this->translateErrorMessage(
                    'error_notunique_global',
                    'SfRegister',
                    [$this->translateErrorMessage($this->propertyName, 'SfRegister')]
                ),
                1301599619
            );
        }
    }
}
