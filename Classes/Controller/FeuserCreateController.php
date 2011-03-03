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
 * An frontend user create controller
 */
class Tx_SfRegister_Controller_FeuserCreateController extends Tx_SfRegister_Controller_FeuserController {
	/**
	 * @var Tx_Extbase_Domain_Model_FrontendUserGroupRepository
	 */
	protected $userGroupRepository = null;
	
	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $passwordAgain
	 * @return string An HTML form
	 * @dontvalidate $user
	 * @dontvalidate $passwordAgain
	 */
	public function formAction(Tx_SfRegister_Domain_Model_FrontendUser $user = NULL, $passwordAgain = NULL) {
		if ($user == NULL && $GLOBALS['TSFE']->fe_user->user != FALSE) {
			$user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		}

		$this->view->assign('user', $user);
		$this->view->assign('passwordAgain', $passwordAgain);
	}

	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $passwordAgain
	 * @return void
	 * @validate $user Tx_SfRegister_Domain_Validator_UserValidator
	 * @validate $passwordAgain Tx_SfRegister_Domain_Validator_PasswordAgainValidator
	 */
	public function previewAction(Tx_SfRegister_Domain_Model_FrontendUser $user, $passwordAgain) {
		$this->view->assign('user', $user);
		$this->view->assign('passwordAgain', $passwordAgain);
	}

	/**
	 * @return void
	 */
	protected function initializeSaveAction() {
		$this->userGroupRepository = t3lib_div::makeInstance('Tx_Extbase_Domain_Model_FrontendUserGroupRepository');
	}
	
	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return void
	 */
	public function saveAction(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$password = $this->encryptPassword($user->getPassword());
		$user->setPassword($password);

		if (intval($this->settings['activationOnRegistrationNeeded']) > 0) {
			$user->setDisable(TRUE);
			$user = $this->setUsergroupBeforeActivation($user);
		} else {
			$user = $this->addUsergroup($user, $this->settings['usergroupOnRegistrationWithoutActivation']);
		}

		$user = t3lib_div::makeInstance('Tx_SfRegister_Services_Mail')
			->injectSettings($this->settings)
			->injectObjectManager($this->objectManager)
			->sendConfirmationMail($user);

		$this->userRepository->add($user);

		if ($this->settings['forwardToEditAfterSave']) {
			$this->forward('form', 'FeuserEdit');
		}
	}

	/**
	 * @param string $authCode
	 * @return void
	 */
	public function confirmAction($authCode) {
		if (intval($this->settings['activationOnRegistrationNeeded']) > 0) {
			$user = $this->changeUsergroupAfterActivation($user);
			$user->setDisable(FALSE);
		}

		$this->userRepository->update($user);

		if ($autologin) {
			
		}
	}
	
	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param integer $usergroupUid
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function addUsergroup(Tx_SfRegister_Domain_Model_FrontendUser $user, $usergroupUid) {
		$usergroupToAdd = $this->userGroupRepository->findByUid($usergroupUid);
		$user->addUsergroup($usergroupToAdd);

		return $user;
	}

	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function setUsergroupBeforeActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if (intval($this->settings['usergroupBeforeActivation']) > 0) {
			$user = $this->addUsergroup($user, $this->settings['usergroupBeforeActivation']);
		}

		return $user;
	}
	
	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function changeUsergroupAfterActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if (intval($this->settings['usergroupAfterActivation']) > 0 &&
				intval($this->settings['usergroupAfterActivation']) != intval($this->settings['usergroupBeforeActivation'])) {
			$user = $this->addUsergroup($user, $this->settings['usergroupBeforeActivation']);

			$usergroupToRemove = $this->userGroupRepository->findByUid($this->settings['usergroupBeforeActivation']);
			$user->removeUsergroup($usergroupToRemove);
		}

		return $user;
	}
}

?>