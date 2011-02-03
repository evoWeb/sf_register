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
 * An frontend user password controller
 */
class Tx_SfRegister_Controller_FeuserPasswordController extends Tx_SfRegister_Controller_FeuserController {
	/**
	 * @return void
	 */
	public function formAction() {
	}

	/**
	 * @param array $passwords
	 * @return void
	 * @validate $passwords Tx_SfRegister_Domain_Validator_PasswordValidator(minimum = 8, maximum = 40)
	 */
	public function saveAction(array $passwords) {
		$password = $this->encryptPassword($passwords['newPassword1']);

		if ($GLOBALS['TSFE']->fe_user->user != FALSE) {
			$user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

			$user->setPassword($password);

			$this->userRepository->update($user);
		}
	}

	/**
	 * @param string $password
	 * @return string
	 */
	protected function encryptPassword($password) {
		if (t3lib_extMgm::isLoaded('saltedpasswords')) {
			if (tx_saltedpasswords_div::isUsageEnabled('FE')) {
				$saltObject = tx_saltedpasswords_salts_factory::getSaltingInstance(NULL);

				if (is_object($saltObject)) {
					$password = $saltObject->getHashedPassword($password);
				}
			}
		}

		return $password;
	}
}

?>