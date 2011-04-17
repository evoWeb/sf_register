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
	 * @var Tx_Extbase_Domain_Repository_FrontendUserGroupRepository
	 */
	protected $userGroupRepository = NULL;

	/**
	 * Form action
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $passwordAgain
	 * @return string An HTML form
	 * @dontvalidate $user
	 */
	public function formAction(Tx_SfRegister_Domain_Model_FrontendUser $user = NULL) {
		if ($user === NULL) {
			$user = t3lib_div::makeInstance('Tx_SfRegister_Domain_Model_FrontendUser');
		} else {
			$user = $this->moveTempFile($user);
		}

		if ($this->request->hasArgument('temporaryImage')) {
			$this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
		}

		$this->view->assign('user', $user);
	}

	/**
	 * Preview action
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $passwordAgain
	 * @return void
	 * @validate $user Tx_SfRegister_Domain_Validator_UserValidator(type = create)
	 * @validate $passwordAgain Tx_SfRegister_Domain_Validator_PasswordsEqualValidator
	 */
	public function previewAction(Tx_SfRegister_Domain_Model_FrontendUser $user, $passwordAgain) {
		$user = $this->moveTempFile($user);

		if ($this->request->hasArgument('temporaryImage')) {
			$this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
		}

		$this->view->assign('user', $user);
	}

	/**
	 * Initialization of save action
	 *
	 * @return void
	 */
	protected function initializeSaveAction() {
		$this->userGroupRepository = t3lib_div::makeInstance('Tx_Extbase_Domain_Repository_FrontendUserGroupRepository');
	}

	/**
	 * Save action
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return void
	 */
	public function saveAction(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$user->setPassword($this->encryptPassword($user->getPassword()));

		if ($this->isNotifyPreActivationToUser() || $this->isNotifyPreActivationToAdmin()) {
			$user->setDisable(TRUE);
			$user = $this->setUsergroupPreActivation($user);
		} else {
			$user = $this->moveImageFile($user);
			$user = $this->addUsergroup($user, $this->settings['usergroup']);
		}

		$user = $this->sendEmailsPreSave($user);

		$this->userRepository->add($user);

		if ($this->settings['forwardToEditAfterSave']) {
			$this->forward('form', 'FeuserEdit');
		}
	}

	/**
	 * Initialization confirm action
	 *
	 * @return void
	 */
	protected function initializeConfirmAction() {
		$this->userGroupRepository = t3lib_div::makeInstance('Tx_Extbase_Domain_Repository_FrontendUserGroupRepository');
	}

	/**
	 * Comnfirm action
	 *
	 * @param string $authCode
	 * @return void
	 */
	public function confirmAction($authCode) {
		$user = $this->userRepository->findByMailhash($authCode);

		if ($user instanceof Tx_SfRegister_Domain_Model_FrontendUser) {
			$user = $this->changeUsergroupPostActivation($user);
			$user = $this->moveImageFile($user);
			$user->setDisable(FALSE);
			$user->setMailhash('');

			$this->sendEmailsPostConfirm($user);

			if ($this->isNotifyPreActivationToUser() && $this->settings['autologinPostActivation']) {
			}
		} else {
			$this->view->assign('userNotFoundByAuthCode', 1);
		}
	}


	/**
	 * Add usergroup to user
	 *
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
	 * Set usergroup to user before activation
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function setUsergroupPreActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if (intval($this->settings['usergroupPreActivation']) > 0) {
			$user = $this->addUsergroup($user, $this->settings['usergroupPreActivation']);
		}

		return $user;
	}

	/**
	 * Change usergroup of user after activation
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function changeUsergroupPostActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if (intval($this->settings['usergroupPostActivation']) > 0 &&
				intval($this->settings['usergroupAfterActivation']) != intval($this->settings['usergroupPreActivation'])) {
			$user = $this->addUsergroup($user, $this->settings['usergroupPostActivation']);

			$usergroupToRemove = $this->userGroupRepository->findByUid($this->settings['usergroupPreActivation']);
			$user->removeUsergroup($usergroupToRemove);
		}

		return $user;
	}


	/**
	 * Send emails to user and/or to admin
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function sendEmailsPreSave($user) {
		$mailService = $this->objectManager->get('Tx_SfRegister_Services_Mail');

		if ($this->isNotifyPreActivationToAdmin()) {
			$user = $mailService->sendAdminNotificationMailPreActivation($user);
		} elseif ($this->isNotifyPreActivationToUser()) {
			$user = $mailService->sendUserNotificationMailPreActivation($user);
		}

		if ($this->isNotifyToAdmin()) {
			$mailService->sendAdminNotificationMail($user);
		}
		if ($this->isNotifyToUser()) {
			$mailService->sendUserNotificationMail($user);
		}

		return $user;
	}

	/**
	 * Send emails to user and/or to admin
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function sendEmailsPostConfirm($user) {
		$mailService = $this->objectManager->get('Tx_SfRegister_Services_Mail');

		if ($this->isNotifyPostActivationToAdmin()) {
			$mailService->sendAdminNotificationMailPostActivation($user);
		}
		if ($this->isNotifyPostActivationToUser()) {
			$mailService->sendUserNotificationMailPostActivation($user);
		}

		return $user;
	}


	/**
	 * Check if the admin should get notified account registration
	 *
	 * @return boolean
	 */
	protected function isNotifyToAdmin() {
		$result = FALSE;

		if ($this->settings['notifyToAdmin'] && !$this->isNotifyPreActivationToAdmin()) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the admin should get notified about account activation
	 *
	 * @return boolean
	 */
	protected function isNotifyPostActivationToAdmin() {
		$result = FALSE;

		if ($this->settings['notifyPostActivationToAdmin']) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the admin need to activate the account
	 *
	 * @return boolean
	 */
	protected function isNotifyPreActivationToAdmin() {
		$result = FALSE;

		if ($this->settings['notifyPreActivationToAdmin']) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the user should get notified account registration
	 *
	 * @return boolean
	 */
	protected function isNotifyToUser() {
		$result = FALSE;

		if (($this->settings['notifyToUser'] && !$this->isNotifyPreActivationToUser()) ||
				($this->settings['notifyToUser'] && $this->isNotifyPreActivationToAdmin())) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the user should get notified about account activation
	 *
	 * @return boolean
	 */
	protected function isNotifyPostActivationToUser() {
		$result = FALSE;

		if ($this->settings['notifyPostActivationToUser']) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the user need to activate the account
	 *
	 * @return boolean
	 */
	protected function isNotifyPreActivationToUser() {
		$result = FALSE;

		if ($this->settings['notifyPreActivationToUser']) {
			$result = TRUE;
		}

		return $result;
	}
}

?>