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
 * A repeated value validator
 *
 * @scope singleton
 */
class Tx_SfRegister_Domain_Validator_RepeatValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * @var mixed
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $fieldname;

	/**
	 * Setter for model
	 *
	 * @param mixed $model
	 * @return void
	 */
	public function setModel($model) {
		$this->model = $model;
	}

	/**
	 * Setter for fieldname
	 *
	 * @param string $fieldname
	 * @return void
	 */
	public function setFieldname($fieldname) {
		$this->fieldname = $fieldname;
	}

	/**
	 * If the given passwords are valid
	 *
	 * @param string $value The value
	 * @return boolean
	 */
	public function isValid($value) {
		$result = TRUE;

		$fieldname = str_replace('Repeat', '', $this->fieldname);
		$getterMethod = 'get' . ucfirst($fieldname);
		if ($value != $this->model->{$getterMethod}()) {
			$this->addError(
				Tx_Extbase_Utility_Localization::translate(
					'error.repeatitionwasnotequal',
					'SfRegister',
					array(Tx_Extbase_Utility_Localization::translate($fieldname, 'SfRegister'))
				),
				1307965971
			);
			$result = FALSE;
		}

		return $result;
	}
}

?>