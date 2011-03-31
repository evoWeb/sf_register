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
 * A Passwordvalidator
 *
 * @scope singleton
 */
class Tx_SfRegister_Domain_Validator_BadWordValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * If the given passwords are valid
	 *
	 * @param string $value The value
	 * @return boolean
	 */
	public function isValid($value) {
		$result = TRUE;

		$settings = Tx_SfRegister_Domain_Validator_UserValidator::getSettings();
		$badWordItems = t3lib_div::trimExplode(',', $settings['badWordList']);

		if (in_array(strtolower($value), $badWordItems)) {
			$this->addError(
				vsprintf(Tx_Extbase_Utility_Localization::translate('error.badword.password', 'SfRegister'), $this->options),
				1296591068
			);
			$result = FALSE;
		}

		return $result;
	}
}

?>