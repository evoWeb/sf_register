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
class FeuserCreateController extends FeuserController {
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

		if ($this->isNotifyUser('PostCreateSave') || $this->isNotifyAdmin('PostCreateSave') &&
				($this->settings['confirmEmailPostCreate'] || $this->settings['acceptEmailPostCreate'])) {
			$user->setDisable(TRUE);
			$user->setActivatedOn(new \DateTime('1970-01-01'));
			$user = $this->changeUsergroup($user, 0, $this->settings['usergroupPostSave']);
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

		// Persist user to get valid uid
		$plainPassword = $user->getPassword();
		// Avoid plain password being persisted
		$user->setPassword('');
		$this->userRepository->add($user);
		$this->persistAll();

		// Write back plain password
		$user->setPassword($plainPassword);
		$user = $this->sendEmails($user, 'PostCreateSave');

		// Encrypt plain password
		$user->setPassword($this->encryptPassword($user->getPassword(), $this->settings));
		$this->userRepository->update($user);
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

		$this->view->assign('user', $user);
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
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @param string $authCode
	 * @return void
	 */
	public function confirmAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = NULL, $authCode = NULL) {
		$user = $this->determineFrontendUser($user, $authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} else {
			$this->view->assign('user', $user);

			if (!$user->getDisable()) {
				$this->view->assign('userAlreadyConfirmed', 1);
			} elseif ($user->getMailhash() === $authCode) {
				$user = $this->changeUsergroup(
					$user,
					$this->settings['usergroupPostSave'],
					$this->settings['usergroupPostConfirm']
				);
				$user = $this->moveImageFile($user);
				$user->setMailhash('');

				if (!$this->settings['acceptEmailPostCreate']) {
					$user->setDisable(FALSE);
				}

				$this->signalSlotDispatcher->dispatch(
					__CLASS__,
					__FUNCTION__,
					array(
						'user' => &$user,
						'settings' => $this->settings,
					)
				);

				$this->userRepository->update($user);

				$this->sendEmails($user, 'PostCreateConfirm');

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
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @param string $authCode
	 * @return void
	 */
	public function refuseAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = NULL, $authCode = NULL) {
		$user = $this->determineFrontendUser($user, $authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} elseif ($user->getMailhash() === $authCode) {
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

			$this->sendEmails($user, 'PostCreateRefuse');

			$this->view->assign('userRefused', 1);
		}
	}

	/**
	 * Accept registration process by admin after user confirmation
	 *
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @param string $authCode
	 * @return void
	 */
	public function acceptAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = NULL, $authCode = NULL) {
		$user = $this->determineFrontendUser($user, $authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} else {
			$this->view->assign('user', $user);

			if ($user->getActivatedOn()) {
				$this->view->assign('userAlreadyAccepted', 1);
			} elseif ($user->getMailhash() === $authCode) {
				$user = $this->changeUsergroup(
					$user,
					$this->settings['usergroupPostConfirm'],
					$this->settings['usergroupPostAccept']
				);
				$user->setActivatedOn(new \DateTime('now'));
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

				$this->sendEmails($user, 'PostCreateAccept');

				$this->view->assign('userAccepted', 1);
			}
		}
	}

	/**
	 * Decline registration process by admin with removing the user data
	 *
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @param string $authCode
	 * @return void
	 */
	public function declineAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = NULL, $authCode = NULL) {
		$user = $this->determineFrontendUser($user, $authCode);

		if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
			$this->view->assign('userNotFound', 1);
		} elseif ($user->getMailhash() === $authCode) {
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

			$this->sendEmails($user, 'PostCreateDecline');

			$this->view->assign('userDeclined', 1);
		}
	}

	/**
	 * Determines the frontend user, either if it's
	 * already submitted, or by looking up the mail hash code.
	 *
	 * @param NULL|\Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @param NULL|string $authCode
	 * @return NULL|\Evoweb\SfRegister\Domain\Model\FrontendUser
	 */
	protected function determineFrontendUser(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = NULL, $authCode = NULL) {
		if ($user !== NULL) {
			return $user;
		}

		if (!empty($authCode)) {
			$user = $this->userRepository->findByMailhash($authCode);
		}

		return $user;
	}

	/**
	 * Change usergroup of user after activation
	 *
	 * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
	 * @param integer $usergroupIdToBeRemoved
	 * @param integer $usergroupIdToAdd
	 * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
	 */
	protected function changeUsergroup(
		\Evoweb\SfRegister\Domain\Model\FrontendUser $user,
		$usergroupIdToBeRemoved,
		$usergroupIdToAdd
	) {
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
