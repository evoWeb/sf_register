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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * A validator to check if a value is unique only if current value has changed
 */
class UniqueExcludeCurrentValidator extends AbstractValidator implements SettableInterface
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
            'boolean'
        ],
    ];

    /**
     * @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository
     */
    protected $userRepository;

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

    public function injectUserRepository(\Evoweb\SfRegister\Domain\Repository\FrontendUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * If the given passwords are valid
     *
     * @param string $value The value
     */
    public function isValid($value)
    {
        if (!$this->model->_isDirty($this->propertyName)) {
            return;
        }

        if ($this->userRepository->countByField($this->propertyName, $value)) {
            $this->addError(
                $this->translateErrorMessage(
                    'error_notunique_local',
                    'SfRegister',
                    [$this->translateErrorMessage($this->propertyName, 'SfRegister')]
                ),
                1301599608
            );
        } elseif ($this->options['global'] && $this->userRepository->countByFieldGlobal($this->propertyName, $value)) {
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
