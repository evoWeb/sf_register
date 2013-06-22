<?php
namespace Evoweb\SfRegister\Controller;
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * An frontend user create controller
 */
class FeuserCreateController extends \Evoweb\SfRegister\Controller\FeuserController {
	/**
	 * Usergroup repository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	 * @inject
	 */
	protected $userGroupRepository = NULL;

	/**
	 * Form action
	 *
	 * @return void
	 */
	public function formAction() {
		/** @var \TYPO3\CMS\Extbase\Mvc\Request $originalRequest */
		$originalRequest = $this->request->getOriginalRequest();
		if ($originalRequest !== NULL && $originalRequest->hasArgument('user')) {
			$userData = $originalRequest->getArgument('user');
			if (isset($userData['uid'])) {
				unset($userData['uid']);
			}

			/** @var \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper */
			$propertyMapper = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Property\\PropertyMapper');
			$user = $propertyMapper->convert($userData, 'Evoweb\\SfRegister\\Domain\\Model\\FrontendUser');
			$user = $this->moveTempFile($user);
		} else {
			/** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
			$user = $this->objectManager->get('Evoweb\\SfRegister\\Domain\\Model\\FrontendUser');
		}

		$user->prepareDateOfBirth();

		if ($originalRequest !== NULL && $originalRequest->hasArgument('temporaryImage')) {
			$this->view->assign('temporaryImage', $originalRequest->getArgument('temporaryImage'));
		}

		$this->signalSlotDispatcher->dispatch(
			__CLASS__,
			__FUNCTION__,
			array(
				'user' => &$user,
				'settings' => $this->settings,
			)
		);

		$this->view->assign('user', $user);
	}

	/**
	 * Preview action
	 *
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @return void
	 * @validate $user Evoweb.SfRegister:User
	 * -validate $user \Evoweb\SfRegister\Validation\Validator\UserValidator
	 */
	public function previewAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user) {
		$user = $this->moveTempFile($user);

		$user->prepareDateOfBirth();

		if ($this->request->hasArgument('temporaryImage')) {
			$this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
		}

		$this->signalSlotDispatcher->dispatch(
			__CLASS__,
			__FUNCTION__,
			array(
				'user' => &$user,
				'settings' => $this->settings,
			)
		);

		$this->view->assign('user', $user);
	}

	/**
	 * Save action
	 *
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @return void
	 * @validate $user Evoweb.SfRegister:User
	 */
	public function saveAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user) {
		$user->prepareDateOfBirth();

		if ($this->isNotifyUser('PreConfirmation') || $this->isNotifyAdmin('PreConfirmation')) {
			$user->setDisable(TRUE);
			$user->setActivatedOn(new \DateTime('1970-01-01'));
			$user = $this->changeUsergroup($user, 0, $this->settings['usergroupPreConfirmation']);
		} else {
			$user = $this->moveImageFile($user);
			$user = $this->changeUsergroup($user, 0, $this->settings['usergroup']);
		}

		if ($this->settings['useEmailAddressAsUsername']) {
			$user->setUsername($user->getEmail());
		}

		$this->signalSlotDispatcher->dispatch(
			__CLASS__,
			__FUNCTION__,
			array(
				'user' => &$user,
				'settings' => $this->settings,
			)
		);

		$user = $this->sendEmails($user, 'PreConfirmation');

		$user->setPassword($this->encryptPassword($user->getPassword(), $this->settings));

		$this->userRepository->add($user);
		$this->persistAll();

		$this->objectManager
			->get('Evoweb\\SfRegister\\Services\\Session')
			->remove('captchaWasValidPreviously');

		if ($this->settings['autologinPostRegistration']) {
			$this->autoLogin($user);
		}

		if ($this->settings['redirectPostRegistrationPageId']) {
			$this->redirectToPage($this->settings['redirectPostRegistrationPageId']);
		}
	}

	/**
	 * Initialization confirm action
	 *
	 * @return void
	 */
	protected function initializeConfirmAction() {
		$this->userRepository = $this->objectManager->get('Evoweb\\SfRegister\\Domain\\Repository\\FrontendUserRepository');
		$this->userGroupRepository = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Domain\\Repository\\FrontendUserGroupRepository');
	}

	/**
	 * Confirm registration process by user
	 * Could be followed by acceptance of admin
	 *
	 * @param string $authCode
	 * @return void
	 */
	public function confirmAction($authCode) {
		$user = $this->userRepository->findByMailhash($authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} else {
			$this->view->assign('user', $user);

			if (!$user->getDisable()) {
				$this->view->assign('userAlreadyConfirmed', 1);
			} else {
				$user = $this->changeUsergroup(
					$user,
					$this->settings['usergroupPreConfirmation'],
					$this->settings['usergroupPostConfirmation']
				);
				$user = $this->moveImageFile($user);
				$user->setDisable(FALSE);
				$user->setMailhash('');

				$this->signalSlotDispatcher->dispatch(
					__CLASS__,
					__FUNCTION__,
					array(
						'user' => &$user,
						'settings' => $this->settings,
					)
				);

				$this->userRepository->update($user);

				$this->sendEmails($user, 'PostConfirmation');

				if ($this->settings['autologinPostConfirmation']) {
					$this->persistAll();
					$this->autoLogin($user);
				}

				if ($this->settings['redirectPostActivationPageId']) {
					$this->redirectToPage($this->settings['redirectPostActivationPageId']);
				}

				$this->view->assign('userConfirmed', 1);
			}
		}
	}

	/**
	 * Refuse registration process by user with removing the user data
	 *
	 * @param string $authCode
	 * @return void
	 */
	public function refuseAction($authCode) {
		$user = $this->userRepository->findByMailhash($authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} else {
			$this->view->assign('user', $user);

			$this->signalSlotDispatcher->dispatch(
				__CLASS__,
				__FUNCTION__,
				array(
					'user' => &$user,
					'settings' => $this->settings,
				)
			);

			$this->userRepository->remove($user);

			$this->sendEmails($user, 'PostRefuse');

			$this->view->assign('userRefused', 1);
		}
	}

	/**
	 * Accept registration process by admin after user confirmation
	 *
	 * @param string $authCode
	 * @return void
	 */
	public function acceptAction($authCode) {
		$user = $this->userRepository->findByMailhash($authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} else {
			$this->view->assign('user', $user);

			if (!$user->getActivatedOn()) {
				$this->view->assign('userAlreadyAccapted', 1);
			} else {
				$user = $this->changeUsergroup(
					$user,
					$this->settings['usergroupPreAcceptance'],
					$this->settings['usergroupPostAcceptance']
				);
				$user->setActivatedOn(new \DateTime('now'));
				$user->setMailhash('');

				$this->signalSlotDispatcher->dispatch(
					__CLASS__,
					__FUNCTION__,
					array(
						'user' => &$user,
						'settings' => $this->settings,
					)
				);

				$this->userRepository->update($user);

				$this->sendEmails($user, 'PostAccept');

				$this->view->assign('userAccepted', 1);
			}
		}
	}

	/**
	 * Decline registration process by admin with removing the user data
	 *
	 * @param string $authCode
	 * @return void
	 */
	public function declineAction($authCode) {
		$user = $this->userRepository->findByMailhash($authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} else {
			$this->view->assign('user', $user);

			$this->signalSlotDispatcher->dispatch(
				__CLASS__,
				__FUNCTION__,
				array(
					'user' => &$user,
					'settings' => $this->settings,
				)
			);

			$this->userRepository->remove($user);

			$this->sendEmails($user, 'PostDecline');

			$this->view->assign('userDeclined', 1);
		}
	}

	/**
	 * Login user with service
	 *
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @return void
	 */
	protected function autoLogin(\Evoweb\SfRegister\Domain\Model\FrontendUser $user) {
		$this->objectManager
			->get('TYPO3\\CMS\\SfRegister\\Services\\Login')
			->loginUserById($user->getUid());
	}

	/**
	 * Redirect to a page with given id
	 *
	 * @param integer $pageId
	 * @return void
	 */
	protected function redirectToPage($pageId) {
		$url = $this->uriBuilder
			->setTargetPageUid($pageId)
			->build();
		$this->redirectToUri($url);
	}

	/**
	 * Change usergroup of user after activation
	 *
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @param integer $usergroupIdToBeRemoved
	 * @param integer $usergroupIdToAdd
	 * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
	 */
	protected function changeUsergroup(\Evoweb\SfRegister\Domain\Model\FrontendUser $user, $usergroupIdToBeRemoved, $usergroupIdToAdd) {
		if (intval($usergroupIdToAdd) > 0 &&
				intval($usergroupIdToAdd) != intval($usergroupIdToBeRemoved)) {
			/** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroupToAdd */
			$usergroupToAdd = $this->userGroupRepository->findByUid($usergroupIdToAdd);
			$user->addUsergroup($usergroupToAdd);

			if (intval($usergroupIdToBeRemoved) > 0) {
				/** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroupToRemove */
				$usergroupToRemove = $this->userGroupRepository->findByUid($usergroupIdToBeRemoved);
				$user->removeUsergroup($usergroupToRemove);
			}
		}

		return $user;
	}
}

?>
