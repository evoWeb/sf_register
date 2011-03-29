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
		$errors = $this->request->getErrors();
		debug($errors['oldPassword']->getMessage());
	}

	/**
	 * Save action
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $passwordAgain
	 * @param string $oldPassword
	 * @return void
	 * @validate $user Tx_SfRegister_Domain_Validator_UserValidator(type = password)
	 * @validate $passwordAgain Tx_SfRegister_Domain_Validator_PasswordAgainValidator
	 * @dontvalidate $oldPassword
	 */
	public function saveAction(Tx_SfRegister_Domain_Model_FrontendUser $user, $passwordAgain, $oldPassword) {
		if ($GLOBALS['TSFE']->fe_user->user != FALSE) {
			$user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

			if ($this->checkOldpassword($oldPassword, $user)) {
				$password = $this->encryptPassword($passwordAgain);

				$user->setPassword($password);

				$this->userRepository->update($user);
			}
		}
	}

	/**
	 * Check if the old password needs to be validated
	 * Check if the old password is empty
	 * Check if the old password is unequal the password in the user object
	 *
	 * @param string $oldPassword
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return boolean
	 */
	protected function checkOldpassword($oldPassword, Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$result = TRUE;

		if (!empty($this->settings['checkOldPasswordOnChange'])) {
			$error = NULL;

			if ($oldPassword === '') {
				$error = t3lib_div::makeInstance('Tx_Extbase_Validation_Error', Tx_Extbase_Utility_Localization::translate('error.oldpassword.empty', 'SfRegister'), 1301429597);
				$result = FALSE;
			} elseif ($oldPassword !== $user->getPassword()) {
				$error = t3lib_div::makeInstance('Tx_Extbase_Validation_Error', Tx_Extbase_Utility_Localization::translate('error.oldpassword.notequal', 'SfRegister'), 1301429598);
				$result = FALSE;
			}

			if ($result === FALSE) {
				$errors = $this->request->getErrors();
				$errors['oldPassword'] = $error;
				$this->request->setErrors($errors);

				if ($this->request->hasArgument('__referrer')) {
					$referrer = $this->request->getArgument('__referrer');
					$this->forward($referrer['actionName'], $referrer['controllerName'], $referrer['extensionName'], $this->request->getArguments());
				}
			}
		}

		return $result;
	}
}

?>