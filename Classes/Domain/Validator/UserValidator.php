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
	 * @return void
	 */
	public function __construct() {
		$this->getSettings();
		$this->getValidatorResolver();
	}

	/**
	 * @return void
	 */
	protected function getSettings() {
		$this->settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.'];
	}

	/**
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
			foreach ($this->settings['validation.'] as $fieldName => $rule) {
				$methodName = 'get' . ucfirst($fieldName);
				if (!method_exists($user, $methodName)) {
					$this->addError(Tx_Extbase_Utility_Localization::translate('error.notexists', 'SfRegister'), 1296594373);
					$result = FALSE;
				} else {
					$this->currentFieldName = $fieldName;
					$result = $this->validateValueWithRule($user->{$methodName}(), $rule) && $result ? TRUE : FALSE;
				}
			}
		}

		return $result;
	}

	/**
	 * @param mixed $value
	 * @param string $rule
	 * @return boolean
	 */
	protected function validateValueWithRule($value, $rule) {
		$result = TRUE;

		$validator = $this->getValidator($rule);
		if ($validator instanceof Tx_Extbase_Validation_Validator_ValidatorInterface AND
				!$validator->isValid($value)) {
			$this->mergeErrorsIntoLokalErrors($validator->getErrors());
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * @param array $errors
	 * @return void
	 */
	protected function mergeErrorsIntoLokalErrors($errors) {
		foreach ($errors as $error) {
			$localizedFieldName = Tx_Extbase_Utility_Localization::translate($this->currentFieldName, 'SfRegister');
			$localizedErrorMessage = Tx_Extbase_Utility_Localization::translate('error.' . $error->getCode(), 'SfRegister');

			$markers = array_merge((array) $localizedFieldName, $this->currentValidatorOptions);
			$messageWithReplacedMarkers = vsprintf($localizedErrorMessage, $markers);

			$this->addError($messageWithReplacedMarkers, $error->getCode());
		}
	}

	/**
	 * Parse the rule and instanciate an validator with the name and the options returned
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