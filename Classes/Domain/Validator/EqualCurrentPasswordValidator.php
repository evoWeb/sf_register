<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
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
 * Abstract validator
 *
 * @package Extbase
 * @subpackage Validation\Validator
 * @scope singleton
 */
class Tx_SfRegister_Domain_Validator_EqualCurrentPasswordValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * If the given value is set
	 *
	 * @param boolean $password The value
	 * @return boolean
	 */
	public function isValid($password) {
		$result = FALSE;

		if (!$this->isUserLoggedIn()) {
			$this->addError(Tx_Extbase_Utility_Localization::translate('error.needloggedintochangepassword', 'SfRegister'), 1296591065);
			$result = FALSE;
		} else {
			$userRepository = t3lib_div::makeInstance('Tx_SfRegister_Domain_Repository_FrontendUserRepository');
			$user = $userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

			$password = $this->encryptPassword($password);

			if ($user->getPassword() !== $password) {
				$this->addError(Tx_Extbase_Utility_Localization::translate('error.notequalcurrentpassword', 'SfRegister'), 1296591065);
				$result = FALSE;
			}
		}

		return $result;
	}

	/**
	 * Check if the user is logged in
	 *
	 * @return boolean
	 */
	protected function isUserLoggedIn() {
		return $GLOBALS['TSFE']->fe_user->user === FALSE ? FALSE : TRUE;
	}

	/**
	 * Encrypt the password
	 *
	 * @param string $password
	 * @return string
	 */
	protected function encryptPassword($password) {
		$this->settings = Tx_SfRegister_Domain_Validator_UserValidator::getSettings();

		if (t3lib_extMgm::isLoaded('saltedpasswords') && tx_saltedpasswords_div::isUsageEnabled('FE')) {
			$saltObject = tx_saltedpasswords_salts_factory::getSaltingInstance(NULL);

			if (is_object($saltObject)) {
				$password = $saltObject->getHashedPassword($password);
			}
		} elseif ($this->settings['encryptPassword'] === 'md5') {
			$password = md5($password);
		} elseif ($this->settings['encryptPassword'] === 'sha1') {
			$password = sha1($password);
		}

		return $password;
	}
}

?>