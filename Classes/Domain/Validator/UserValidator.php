<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A Uservalidator
 */
class Tx_SfRegister_Domain_Validator_UserValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * Configuration manager
	 *
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * Settings
	 *
	 * @var array
	 */
	protected $settings = NULL;

	/**
	 * Configuration of the framework
	 *
	 * @var array
	 */
	protected $frameworkConfiguration = array();

	/**
	 * Validator resolver
	 *
	 * @var  Tx_SfRegister_Validation_ValidatorResolver
	 */
	protected $validatorResolver;

	/**
	 * Name of the current field to validate
	 *
	 * @var string
	 */
	protected $currentFieldName = '';

	/**
	 * Options for the current validation
	 *
	 * @var array
	 */
	protected $currentValidatorOptions = array();

	/**
	 * Model that gets validated currently
	 *
	 * @var object
	 */
	protected $model;

	/**
	 * Inject of configuration manager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		$this->frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
	}

	/**
	 * Initialize validator resolver
	 *
	 * @param Tx_SfRegister_Validation_ValidatorResolver $validatorResolver
	 * @return void
	 */
	public function injectValidatorResolver(Tx_SfRegister_Validation_ValidatorResolver $validatorResolver) {
		$this->validatorResolver = $validatorResolver;
	}

	/**
	 * Initialize error result
	 *
	 * @param Tx_Extbase_Error_Result $result
	 * @return void
	 */
	public function injectResult(Tx_Extbase_Error_Result $result) {
		$this->result = $result;
	}

	/**
	 * Add an error with message and code to the property errors
	 *
	 * @param string $propertyName name of the property to add the error to
	 * @param string $message Message to be shwon
	 * @param string $code Error code to identify the error
	 * @return void
	 */
	protected function addErrorsForProperty($propertyName, $message, $code) {
		if (!$this->result->forProperty($propertyName)->hasErrors()) {
			/** @var $error Tx_Extbase_Validation_PropertyError */
			$error = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', $propertyName);
			$error->addErrors(array(
				t3lib_div::makeInstance('Tx_Extbase_Validation_Error', $message, $code)
			));
			$this->result->addError($error);
		}
	}

	/**
	 * If the given user are valid
	 *
	 * @param object $model
	 * @return boolean
	 */
	public function isValid($model) {
		$this->model = $model;

		if (!($model instanceof Tx_SfRegister_Domain_Model_FrontendUser) &&
				!($model instanceof Tx_SfRegister_Domain_Model_Password)) {
			$this->addError(Tx_Extbase_Utility_Localization::translate('error.notvalidatable', 'SfRegister'), 1301599551);
			$result = FALSE;
		} else {
			$result = $this->validateRules($model);
		}

		return $result;
	}

	/**
	 * Validate all rules
	 *
	 * @param object $model
	 * @return boolean
	 */
	protected function validateRules($model) {
		$result = TRUE;

		foreach ($this->getRulesFromSettings() as $fieldName => $rule) {
			$methodName = 'get' . ucfirst($fieldName);

			if (!method_exists($model, $methodName)) {
				$this->addError(Tx_Extbase_Utility_Localization::translate('error.notexists', 'SfRegister'), 1301599575);
				$result = FALSE;
			} else {
				$this->currentFieldName = $fieldName;
				$fieldValue = $model->{$methodName}();

				if (is_array($rule)) {
					$result = $this->validateRuleArray($fieldValue, $rule) && $result ? TRUE : FALSE;
				} else {
					$result = $this->validateValueWithRule($fieldValue, $rule) && $result ? TRUE : FALSE;
				}
			}
		}

		return $result;
	}

	/**
	 * Get validation rules from settings
	 * Warning: Dont remove the validators added in this method
	 *          These prevent that editing others data is possible
	 *
	 * @return array
	 */
	protected function getRulesFromSettings() {
		$mode = str_replace('feuser', '', strtolower(key($this->frameworkConfiguration['controllerConfiguration'])));
		$rules = $this->settings['validation'][$mode];

		if ($this->model instanceof Tx_Extbase_Domain_Model_FrontendUser) {
			if ($mode == 'create') {
				$rules = array_merge(array('uid' => 'Tx_SfRegister_Domain_Validator_EmptyValidator'), $rules);
			} elseif ($mode == 'edit') {
				$rules = array_merge(array('uid' => 'Tx_SfRegister_Domain_Validator_EqualCurrentUserValidator'), $rules);
			}
		}

		return $rules;
	}

	/**
	 * Validate rules until one of them failes and then stop validating any further
	 *
	 * @param mixed $fieldValue
	 * @param array $rules
	 * @return boolean
	 */
	protected function validateRuleArray($fieldValue, Array $rules) {
		$result = TRUE;

		foreach ($rules as $rule) {
			$result = $this->validateValueWithRule($fieldValue, $rule) && $result ? TRUE : FALSE;

			if (!$result) {
				break;
			}
		}

		return $result;
	}

	/**
	 * Validate value with rule
	 *
	 * @param mixed $value
	 * @param string $rule
	 * @return boolean
	 */
	protected function validateValueWithRule($value, $rule) {
		$result = TRUE;

		$validator = $this->getValidator($rule);

		if ($validator instanceof Tx_Extbase_Validation_Validator_ValidatorInterface AND !$validator->isValid($value)) {
			$this->mergeErrorsIntoLocalErrors($validator->getErrors());
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Merge error into local errors
	 *
	 * @param array $errors
	 * @return void
	 */
	protected function mergeErrorsIntoLocalErrors($errors) {
		foreach ($errors as $error) {
			$localizedFieldName = Tx_Extbase_Utility_Localization::translate($this->currentFieldName, 'SfRegister');

			$errorMessage = $error->getMessage();
			$localizedErrorCode = Tx_Extbase_Utility_Localization::translate('error.' . $error->getCode(), 'SfRegister');
			$localizedErrorMessage = $localizedErrorCode ? $localizedErrorCode : $errorMessage;

			$markers = array_merge((array) $localizedFieldName, $this->currentValidatorOptions);
			$messageWithReplacedMarkers = vsprintf($localizedErrorMessage, $markers);

			$this->addErrorsForProperty(
				$this->currentFieldName,
				$messageWithReplacedMarkers,
				$error->getCode()
			);
		}
	}

	/**
	 * Parse the rule and instanciate an validator with the name and the options
	 *
	 * @param string $rule
	 * @return Tx_Extbase_Validation_Validator_ValidatorInterface
	 */
	protected function getValidator($rule) {
		$currentValidator = $this->parseRule($rule);
		$this->currentValidatorOptions = (array) $currentValidator['validatorOptions'];

		$validatorObject = $this->validatorResolver->createValidator(
			$currentValidator['validatorName'],
			$this->currentValidatorOptions
		);

		if (method_exists($validatorObject, 'setModel')) {
			$validatorObject->setModel($this->model);
		}
		if (method_exists($validatorObject, 'setFieldname')) {
			$validatorObject->setFieldname($this->currentFieldName);
		}

		return $validatorObject;
	}

	/**
	 * Parse rule
	 *
	 * @param string $rule
	 * @return array
	 */
	protected function parseRule($rule) {
		$parsedRules = $this->validatorResolver->getParsedValidatorAnnotation($rule);

		return current($parsedRules['validators']);
	}
}

?>