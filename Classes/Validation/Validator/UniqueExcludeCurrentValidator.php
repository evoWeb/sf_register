<?php
namespace Evoweb\SfRegister\Validation\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * A validator to check if a value is unique only if current value has changed
 *
 * @scope singleton
 */
class UniqueExcludeCurrentValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var array
     */
    protected $supportedOptions = [
        'global' => [true, 'Whether to check uniqueness globally', 'boolean'],
    ];

    /**
     * Frontend user repository
     *
     * @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $userRepository = null;

    /**
     * propertyName
     *
     * @var string
     */
    protected $propertyName = '';

    /**
     * model
     *
     * @var \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected $model;

    /**
     * Setter for propertyName
     *
     * @param string $propertyName
     *
     * @return void
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Setter for model
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $model
     *
     * @return void
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * If the given passwords are valid
     *
     * @param string $value The value
     *
     * @return boolean
     */
    public function isValid($value)
    {
        $result = true;

        if (!$this->model->_isDirty($this->propertyName)) {
            $result = true;
        } elseif ($this->userRepository->countByField($this->propertyName, $value)) {
            $this->addError(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'error_notunique_local',
                    'SfRegister'
                ),
                1301599608
            );
            $result = false;
        } elseif ($this->options['global'] && $this->userRepository->countByFieldGlobal($this->propertyName, $value)) {
            $this->addError(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'error_notunique_global',
                    'SfRegister'
                ),
                1301599619
            );
            $result = false;
        }

        return $result;
    }
}
