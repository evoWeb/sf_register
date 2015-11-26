<?php
namespace Evoweb\SfRegister\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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
class FeuserCreateController extends FeuserController
{
    /**
     * Form action
     *
     * @return void
     */
    public function formAction()
    {
        /** @var \TYPO3\CMS\Extbase\Mvc\Request $originalRequest */
        $originalRequest = $this->request->getOriginalRequest();
        if ($this->request->hasArgument('user')
            || ($originalRequest !== null && $originalRequest->hasArgument('user'))
        ) {
            $userData = $this->request->hasArgument('user') ?
                $this->request->getArgument('user') :
                $originalRequest->getArgument('user');
            if (isset($userData['uid'])) {
                unset($userData['uid']);
            }

            /** @var \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper */
            $propertyMapper = $this->objectManager->get(\TYPO3\CMS\Extbase\Property\PropertyMapper::class);
            $user = $propertyMapper->convert($userData, \Evoweb\SfRegister\Domain\Model\FrontendUser::class);
            $user = $this->moveTempFile($user);
        } else {
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->objectManager->get(\Evoweb\SfRegister\Domain\Model\FrontendUser::class);
        }

        if ($originalRequest !== null && $originalRequest->hasArgument('temporaryImage')) {
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
    public function previewAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        $user = $this->moveTempFile($user);

        $user->prepareDateOfBirth();

        if ($this->request->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
        }

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            array('user' => &$user, 'settings' => $this->settings)
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
    public function saveAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        // if preview step is skiped the temp file isn't moved yet
        $user = $this->moveTempFile($user);

        if ($this->isNotifyUser('PostCreateSave') && $this->settings['confirmEmailPostCreate']) {
            $user->setDisable(true);
            $user = $this->changeUsergroup($user, (int) $this->settings['usergroupPostSave']);
            $type = 'PostCreateConfirm';
        } elseif ($this->isNotifyAdmin('PostCreateSave') && $this->settings['acceptEmailPostCreate']) {
            $user->setDisable(true);
            $user = $this->changeUsergroup($user, (int) $this->settings['usergroupPostSave']);
            $type = 'PostCreateAccept';
        } else {
            $user = $this->moveImageFile($user);
            $user = $this->changeUsergroup($user, (int) $this->settings['usergroup']);
            $type = 'PostCreateSave';
        }

        if ($this->settings['useEmailAddressAsUsername']) {
            $user->setUsername($user->getEmail());
        }

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            array('user' => &$user, 'settings' => $this->settings)
        );

        // Persist user to get valid uid
        $plainPassword = $user->getPassword();
        // Avoid plain password being persisted
        $user->setPassword('');
        $this->userRepository->add($user);
        $this->persistAll();

        // Write back plain password
        $user->setPassword($plainPassword);
        $user = $this->sendEmails($user, $type);

        // Encrypt plain password
        $user->setPassword($this->encryptPassword($user->getPassword(), $this->settings));
        $this->userRepository->update($user);
        $this->persistAll();

        $this->objectManager->get(\Evoweb\SfRegister\Services\Session::class)->remove('captchaWasValidPreviously');

        if ($this->settings['autologinPostRegistration']) {
            $this->autoLogin($user);
        }

        if ($this->settings['redirectPostRegistrationPageId']) {
            $this->redirectToPage((int) $this->settings['redirectPostRegistrationPageId']);
        }

        $this->view->assign('user', $user);
    }

    /**
     * Initialization confirm action
     *
     * @return void
     */
    protected function initializeConfirmAction()
    {
        $this->userRepository = $this->objectManager->get(
            'Evoweb\\SfRegister\\Domain\\Repository\\FrontendUserRepository'
        );
    }

    /**
     * Confirm registration process by user
     * Could be followed by acceptance of admin
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $hash
     * @return void
     */
    public function confirmAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (!$user->getDisable() || $this->isUserInUserGroups(
                $user,
                $this->getFollowingUserGroups((int) $this->settings['usergroupPostConfirm'])
            )) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                $user = $this->changeUsergroup($user, (int) $this->settings['usergroupPostConfirm']);
                $user = $this->moveImageFile($user);

                if (!$this->settings['acceptEmailPostCreate']) {
                    $user->setDisable(false);
                }

                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    __FUNCTION__,
                    array('user' => &$user, 'settings' => $this->settings)
                );

                $this->userRepository->update($user);

                $this->sendEmails($user, 'PostCreateConfirm');

                if ($this->settings['autologinPostConfirmation']) {
                    $this->persistAll();
                    $this->autoLogin($user);
                }

                if ($this->settings['redirectPostActivationPageId']) {
                    $this->redirectToPage((int) $this->settings['redirectPostActivationPageId']);
                }

                $this->view->assign('userConfirmed', 1);
            }
        }
    }

    /**
     * Refuse registration process by user with removing the user data
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $hash
     * @return void
     */
    public function refuseAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__,
                array('user' => &$user, 'settings' => $this->settings)
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
     * @param string $hash
     * @return void
     */
    public function acceptAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if ($user->getActivatedOn() || $this->isUserInUserGroups(
                $user,
                $this->getFollowingUserGroups((int) $this->settings['usergroupPostAccept'])
            )) {
                $this->view->assign('userAlreadyAccepted', 1);
            } else {
                $user = $this->changeUsergroup($user, (int) $this->settings['usergroupPostAccept']);
                $user->setActivatedOn(new \DateTime('now'));
                $user->setDisable(false);

                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    __FUNCTION__,
                    array('user' => &$user, 'settings' => $this->settings)
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
     * @param string $hash
     * @return void
     */
    public function declineAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__,
                array('user' => &$user, 'settings' => $this->settings)
            );

            $this->userRepository->remove($user);

            $this->sendEmails($user, 'PostCreateDecline');

            $this->view->assign('userDeclined', 1);
        }
    }
}
