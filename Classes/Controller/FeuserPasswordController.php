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
	 * Form action
	 *
	 * @return string An HTML form
	 */
	public function formAction() {
	}

	/**
	 * Save action
	 *
	 * @param Tx_SfRegister_Domain_Model_Password $password
	 * @return void
	 * @validate $password Tx_SfRegister_Domain_Validator_UserValidator
	 */
	public function saveAction(Tx_SfRegister_Domain_Model_Password $password) {
		if ($this->isUserLoggedIn()) {
			$user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

			$user->setPassword($this->encryptPassword($password->getPassword()));

			$this->userRepository->update($user);
		}
	}
}

?>