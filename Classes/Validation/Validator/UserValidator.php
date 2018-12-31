<?php
namespace Evoweb\SfRegister\Validation\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
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

use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class UserValidator extends GenericObjectValidator implements ValidatorInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    static protected $instancesCurrentlyUnderValidation;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = null;

    /**
     * @var array
     */
    protected $frameworkConfiguration = [];

    /**
     * @var \TYPO3\CMS\Extbase\Error\Result
     */
    protected $result;

    /**
     * @var \Evoweb\SfRegister\Validation\ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * Name of the current field to validate
     *
     * @var string
     */
    protected $currentPropertyName = '';

    /**
     * @var array
     */
    protected $currentValidatorOptions = [];

    /**
     * Model that gets validated currently
     *
     * @var \Evoweb\SfRegister\Domain\Model\FrontendUser|\Evoweb\SfRegister\Domain\Model\Password
     */
    protected $model;


    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        /** @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager configurationManager */
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );

        $this->frameworkConfiguration = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'SfRegister',
            'Form'
        );
    }

    public function injectResult(\TYPO3\CMS\Extbase\Error\Result $result)
    {
        $this->result = $result;
    }

    public function injectValidatorResolver(\Evoweb\SfRegister\Validation\ValidatorResolver $validatorResolver)
    {
        $this->validatorResolver = $validatorResolver;
    }

    public function validate($object): \TYPO3\CMS\Extbase\Error\Result
    {
        /** @var \TYPO3\CMS\Extbase\Error\Result $messages */
        $messages = $this->objectManager->get(\TYPO3\CMS\Extbase\Error\Result::class);
        if (self::$instancesCurrentlyUnderValidation === null) {
            self::$instancesCurrentlyUnderValidation = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        }
        if ($object === null) {
            return $messages;
        }
        if (!$this->canValidate($object)) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            /** @var \TYPO3\CMS\Extbase\Error\Error $error */
            $error = $this->objectManager->get(
                \TYPO3\CMS\Extbase\Error\Error::class,
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'error_notvalidatable',
                    'SfRegister'
                ),
                1301599551
            );
            $messages->addError($error);

            return $messages;
        }
        if (self::$instancesCurrentlyUnderValidation->contains($object)) {
            return $messages;
        } else {
            self::$instancesCurrentlyUnderValidation->attach($object);
        }

        $this->model = $object;

        $propertyValidators = $this->getValidationRulesFromSettings();
        foreach ($propertyValidators as $propertyName => $validatorsNames) {
            if (!property_exists($object, $propertyName)) {
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                /** @var \TYPO3\CMS\Extbase\Error\Error $error */
                $error = $this->objectManager->get(
                    \TYPO3\CMS\Extbase\Error\Error::class,
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'error_notexists',
                        'SfRegister'
                    ),
                    1301599575
                );
                $messages->addError($error);
            } else {
                $this->currentPropertyName = $propertyName;
                $propertyValue = $this->getPropertyValue($object, $propertyName);
                $this->checkUserProperty(
                    $propertyValue,
                    (array)$validatorsNames,
                    $messages->forProperty($propertyName)
                );
            }
        }

        self::$instancesCurrentlyUnderValidation->detach($object);

        return $messages;
    }

    /**
     * Checks if the specified property of the given object is valid, and adds
     * found errors to the $messages object.
     *
     * @param mixed $value The value to be validated
     * @param array $validatorNames Contains an array with validator names
     * @param \TYPO3\CMS\Extbase\Error\Result $messages the result object
     */
    protected function checkUserProperty($value, array $validatorNames, \TYPO3\CMS\Extbase\Error\Result $messages)
    {
        foreach ($validatorNames as $validatorName) {
            $messages->merge(
                $this->getValidator($validatorName)->validate($value)
            );
        }
    }

    /**
     * Checks if validator can validate the object
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser|\Evoweb\SfRegister\Domain\Model\Password $object
     *
     * @return bool
     */
    public function canValidate($object): bool
    {
        return (
            $object instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser
            || $object instanceof \Evoweb\SfRegister\Domain\Model\Password
        );
    }

    /**
     * Get validation rules from settings
     * Warning: Don't remove the validators added in this method
     *          These prevent that editing others data is possible
     */
    protected function getValidationRulesFromSettings(): array
    {
        $mode = str_replace('feuser', '', strtolower(key($GLOBALS['sf_register_controllerConfiguration'])));
        $rules = $this->settings['validation'][$mode] ?? [];

        if ($this->model instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser) {
            if ($mode == 'create') {
                // uid needs to be emtpy if FrontendUser should be valid on creation
                $rules = array_merge(
                    ['uid' => EmptyValidator::class],
                    $rules
                );
            } elseif ($mode == 'edit') {
                // add validation that the user to be edited is logged in
                $rules = array_merge(
                    ['uid' => EqualCurrentUserValidator::class],
                    $rules
                );
            }
        }

        return $rules;
    }

    /**
     * Parse the rule and instantiate an validator with the name and the options
     *
     * @param string $rule
     *
     * @return \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
     */
    protected function getValidator(string $rule): \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
    {
        $currentValidator = $this->parseRule($rule);
        $this->currentValidatorOptions = (array)$currentValidator['validatorOptions'];

        /** @var \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator $validator */
        $validator = $this->validatorResolver->createValidator(
            $currentValidator['validatorName'],
            $this->currentValidatorOptions
        );

        if (method_exists($validator, 'setModel')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $validator->setModel($this->model);
        }
        if (method_exists($validator, 'setPropertyName')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $validator->setPropertyName($this->currentPropertyName);
        }

        return $validator;
    }

    protected function parseRule(string $rule): array
    {
        $parsedRules = $this->validatorResolver->getParsedValidatorAnnotation($rule);

        return current($parsedRules['validators']);
    }
}
