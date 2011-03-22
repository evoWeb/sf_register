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
 *
 * @scope singleton
 */
class Tx_SfRegister_Domain_Validator_UserValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var string
	 */
	protected $currentFieldName = '';

	/**
	 * @var string
	 */
	protected $currentValidatorName = '';

	/**
	 * @var array
	 */
	protected $currentValidatorOptions = array();

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->getSettings();
		$this->getValidatorResolver();
	}

	/**
	 * Get settings
	 *
	 * @return void
	 */
	protected function getSettings() {
		$global = Tx_Extbase_Dispatcher::getConfigurationManager()->loadTypoScriptSetup();
		$this->settings = $global['plugin.']['tx_sfregister.']['settings.'];
	}

	/**
	 * Add an error with message and code to the property errors
	 *
	 * @param array $propertyName name of the property to add the error to
	 * @param string $message Message to be shwon
	 * @param string $code Error code to identify the error
	 * @return void
	 */
	protected function addErrorsForProperty($propertyName, $message, $code) {
		if (!isset($this->errors[$propertyName])) {
			$this->errors[$propertyName] = t3lib_div::makeInstance('Tx_Extbase_Validation_PropertyError', $propertyName);
		}

		$errors = array(
			t3lib_div::makeInstance('Tx_Extbase_Validation_Error', $message, $code)
		);

		$this->errors[$propertyName]->addErrors($errors);
	}

	/**
	 * Get validator resolver
	 *
	 * @return void
	 */
	protected function getValidatorResolver() {
		$this->validatorResolver = t3lib_div::makeInstance('Tx_SfRegister_Validation_ValidatorResolver');
		$this->validatorResolver->injectObjectManager(t3lib_div::makeInstance('Tx_Extbase_Object_Manager'));
	}

	/**
	 * If the given user are valid
	 *
	 * @param object $user
	 * @return boolean
	 */
	public function isValid($user) {
		$result = TRUE;

		if (! $user instanceof Tx_Extbase_Domain_Model_FrontendUser) {
			$this->addError(Tx_Extbase_Utility_Localization::translate('error.notauser', 'SfRegister'), 1296594373);
			$result = FALSE;
		} else {
			$result = $this->validateRules($user);
		}

		return $result;
	}

	/**
	 * Validate all rules
	 *
	 * @param object $user
	 * @return boolean
	 */
	protected function validateRules($user) {
		$result = TRUE;

		foreach ($this->settings['validation.'] as $fieldName => $rule) {
			$fieldName = str_replace('.', '', $fieldName);
			$methodName = 'get' . ucfirst($fieldName);

			if (!method_exists($user, $methodName)) {
				$this->addError(Tx_Extbase_Utility_Localization::translate('error.notexists', 'SfRegister'), 1296594373);
				$result = FALSE;
			} else {
				$this->currentFieldName = $fieldName;
				$fieldValue = $user->{$methodName}();

				if (!is_array($rule)) {
					$result = $this->validateValueWithRule($fieldValue, $rule) && $result ? TRUE : FALSE;
				} else {
					$result = $this->validateRuleArray($fieldValue, $rule) && $result ? TRUE : FALSE;
				}
			}
		}

		return $result;
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
		if (method_exists($validator, 'setFieldname')) {
			$validator->setFieldname($this->currentFieldName);
		}
		if ($validator instanceof Tx_Extbase_Validation_Validator_ValidatorInterface AND
				!$validator->isValid($value)) {
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
		$this->parseRule($rule);

		$validator = $this->validatorResolver->createValidator(
			$this->currentValidatorName,
			$this->currentValidatorOptions
		);

		return $validator;
	}

	/**
	 * Parse rule
	 *
	 * @param string $rule
	 * @return void
	 */
	protected function parseRule($rule) {
		$parsedRules = $this->validatorResolver->getParsedValidatorAnnotation($rule);

		$this->currentValidatorName = $parsedRules['validators'][0]['validatorName'];
		$this->currentValidatorOptions = (array) $parsedRules['validators'][0]['validatorOptions'];
	}
}

?>