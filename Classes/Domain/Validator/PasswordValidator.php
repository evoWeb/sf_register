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
class Tx_SfRegister_Domain_Validator_PasswordValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * If the given passwords are valid
	 *
	 * @param array $passwords The passwords
	 * @return boolean
	 */
	public function isValid($passwords) {
		$result = TRUE;

		if ($passwords['newPassword1'] === '') {
			$this->addError(Tx_Extbase_Utility_Localization::translate('error.empty.password1', 'SfRegister'), 1296591064);
			$result = FALSE;
		}

		if ($passwords['newPassword2'] === '') {
			$this->addError(Tx_Extbase_Utility_Localization::translate('error.empty.password2', 'SfRegister'), 1296591065);
			$result = FALSE;
		}

		if ($passwords['newPassword1'] !== $passwords['newPassword2']) {
			$this->addError(Tx_Extbase_Utility_Localization::translate('error.notequal.passwords', 'SfRegister'), 1296591066);
			$result = FALSE;
		}

		if (strlen($passwords['newPassword1']) < $this->options['minimum'] || strlen($passwords['newPassword1']) > $this->options['maximum']) {
			$this->addError(vsprintf(Tx_Extbase_Utility_Localization::translate('error.length.password', 'SfRegister'), $this->options), 1296591067);
			$result = FALSE;
		}

		if ($this->inBadWordList($passwords['newPassword1'])) {
			$this->addError(vsprintf(Tx_Extbase_Utility_Localization::translate('error.badword.password', 'SfRegister'), $this->options), 1296591068);
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * @return boolean
	 */
	protected function inBadWordList($word) {
		$global = Tx_Extbase_Dispatcher::getConfigurationManager()->loadTypoScriptSetup();
		$badWordItems = t3lib_div::trimExplode(',', $global['plugin.']['tx_sfregister.']['settings.']['badWordList']);
		return in_array(strtolower($word), $badWordItems);
	}
}

?>