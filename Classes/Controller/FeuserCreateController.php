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
	 * @dontvalidate $passwordAgain
	 */
	public function formAction(Tx_SfRegister_Domain_Model_FrontendUser $user = NULL, $passwordAgain = NULL) {
		if ($user === NULL) {
			$user = t3lib_div::makeInstance('Tx_SfRegister_Domain_Model_FrontendUser');
		}

		$this->view->assign('user', $user);
		$this->view->assign('passwordAgain', $passwordAgain);
	}

	/**
	 * Preview action
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $passwordAgain
	 * @return void
	 * @validate $user Tx_SfRegister_Domain_Validator_UserValidator(type = create)
	 * @validate $passwordAgain Tx_SfRegister_Domain_Validator_PasswordAgainValidator
	 */
	public function previewAction(Tx_SfRegister_Domain_Model_FrontendUser $user, $passwordAgain) {
		if ($this->request->hasArgument('removeImage')) {
			$this->forward('removeImage');
		} else {
			$imagePath = $this->fileService->moveTempFileToUploadfolder();
			if ($imagePath) {
				$user->setImage($imagePath);
			}

			$this->view->assign('user', $user);
			$this->view->assign('passwordAgain', $passwordAgain);
		}
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
		$password = $this->encryptPassword($user->getPassword());
		$user->setPassword($password);

		if ($this->isActivateByUser() || $this->isActivateByAdmin()) {
			$user->setDisable(TRUE);
			$user = $this->setUsergroupBeforeActivation($user);
		} else {
			$user = $this->addUsergroup($user, $this->settings['usergroupWithoutActivation']);
		}

		$user = $this->sendEmails($user);

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
			$user = $this->changeUsergroupAfterActivation($user);
			$user->setDisable(FALSE);
			$user->setMailhash('');

			if ($autologin) {
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
	protected function setUsergroupBeforeActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if (intval($this->settings['usergroupBeforeActivation']) > 0) {
			$user = $this->addUsergroup($user, $this->settings['usergroupBeforeActivation']);
		}

		return $user;
	}

	/**
	 * Change usergroup of user after activation
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function changeUsergroupAfterActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if (intval($this->settings['usergroupAfterActivation']) > 0 &&
				intval($this->settings['usergroupAfterActivation']) != intval($this->settings['usergroupBeforeActivation'])) {
			$user = $this->addUsergroup($user, $this->settings['usergroupAfterActivation']);

			$usergroupToRemove = $this->userGroupRepository->findByUid($this->settings['usergroupBeforeActivation']);
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
	protected function sendEmails($user) {
		$mailService = t3lib_div::makeInstance('Tx_SfRegister_Services_Mail')
			->injectSettings($this->settings)
			->injectObjectManager($this->objectManager);

		if ($this->isActivateByAdmin()) {
			$user = $mailService->sendAdminActivationMail($user);
		} elseif ($this->isActivateByUser()) {
			$user = $mailService->sendUserActivationMail($user);
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
	 * Check if the user needs to activate
	 *
	 * @return boolean
	 */
	protected function isActivateByUser() {
		$result = FALSE;

		if ($this->settings['activateByUser'] && !$this->settings['activateByAdmin']) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the admin needs to activate
	 *
	 * @return boolean
	 */
	protected function isActivateByAdmin() {
		$result = FALSE;

		if ($this->settings['activateByAdmin']) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the user should get notified
	 *
	 * @return boolean
	 */
	protected function isNotifyToUser() {
		$result = FALSE;

		if ($this->settings['notifyToUser'] && !$this->isActivateByUser()) {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Check if the admin should get notified
	 *
	 * @return boolean
	 */
	protected function isNotifyToAdmin() {
		$result = FALSE;

		if ($this->settings['notifyToAdmin'] && !$this->isActivateByAdmin()) {
			$result = TRUE;
		}

		return $result;
	}
}

?>