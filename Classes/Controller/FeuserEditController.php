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
 * An frontend user edit controller
 */
class FeuserEditController extends FeuserController
{
    /**
     * Form action
     *
     * @return void
     */
    public function formAction()
    {
        $user = null;

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $originalRequest */
        $originalRequest = $this->request->getOriginalRequest();
        if ((
                $this->request->hasArgument('user')
                || ($originalRequest !== null && $originalRequest->hasArgument('user'))
            )
            && \Evoweb\SfRegister\Services\Login::isLoggedIn()
        ) {
            $userData = $this->request->hasArgument('user') ?
                $this->request->getArgument('user') :
                $originalRequest->getArgument('user');

            if ($userData['uid'] == $GLOBALS['TSFE']->fe_user->user['uid']) {
                /** @var \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper */
                $propertyMapper = $this->objectManager->get(\TYPO3\CMS\Extbase\Property\PropertyMapper::class);
                $user = $propertyMapper->convert($userData, \Evoweb\SfRegister\Domain\Model\FrontendUser::class);
                $user = $this->moveTempFile($user);
            }
        }

        if ($user == null) {
            /** @var $user \Evoweb\SfRegister\Domain\Model\FrontendUser */
            $user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
        }

        if ($originalRequest && $originalRequest->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $originalRequest->getArgument('temporaryImage'));
        }

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            array('user' => &$user, 'settings' => $this->settings)
        );

        $this->view->assign('user', $user);
    }

    /**
     * Preview action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @validate $user Evoweb.SfRegister:User
     * @return void
     */
    public function previewAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        $user = $this->moveTempFile($user);

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
        $user = $this->moveImageFile($user);

        if (($this->isNotifyAdmin('PostEditSave') || $this->isNotifyUser('PostEditSave'))
            && ($this->settings['confirmEmailPostEdit'] || $this->settings['acceptEmailPostEdit'])
        ) {
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $userBeforeEdit */
            $userBeforeEdit = $this->userRepository->findByUid($user->getUid());

            $user->setEmailNew($user->getEmail());
            $user->setEmail($userBeforeEdit->getEmail());
        } elseif ($this->settings['useEmailAddressAsUsername']) {
            $user->setUsername($user->getEmail());
        }

        $user->prepareDateOfBirth();

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            array('user' => &$user, 'settings' => $this->settings)
        );

        $user = $this->sendEmails($user, 'PostEditSave');

        $this->userRepository->update($user);

        $this->objectManager->get(\Evoweb\SfRegister\Services\Session::class)
            ->remove('captchaWasValidPreviously');

        if ($this->settings['forwardToEditAfterSave']) {
            $this->forward('form');
        }

        $this->view->assign('user', $user);
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

            if (!$user->getDisable()) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                if (!$this->settings['acceptEmailPostEdit']) {
                    $user->setEmail($user->getEmailNew());
                    $user->setEmailNew('');

                    if ($this->settings['useEmailAddressAsUsername']) {
                        $user->setUsername($user->getEmail());
                    }
                }

                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    __FUNCTION__,
                    array('user' => &$user, 'settings' => $this->settings)
                );

                $this->userRepository->update($user);

                $this->sendEmails($user, 'PostEditConfirm');

                $this->view->assign('userConfirmed', 1);
            }

            if ($this->settings['autologinPostConfirmation']) {
                $this->persistAll();
                $this->autoLogin($user);
            }

            if ($this->settings['redirectPostActivationPageId']) {
                $this->redirectToPage((int) $this->settings['redirectPostActivationPageId']);
            }
        }
    }

    /**
     * Confirm registration process by user
     * Could be followed by acceptance of admin
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

            if (!$user->getDisable()) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                $user->setEmail($user->getEmailNew());
                $user->setEmailNew('');

                if ($this->settings['useEmailAddressAsUsername']) {
                    $user->setUsername($user->getEmail());
                }

                $this->signalSlotDispatcher->dispatch(
                    __CLASS__,
                    __FUNCTION__,
                    array('user' => &$user, 'settings' => $this->settings)
                );

                $this->userRepository->update($user);

                $this->sendEmails($user, 'PostEditAccept');

                if ($this->settings['redirectPostActivationPageId']) {
                    $this->redirectToPage((int) $this->settings['redirectPostActivationPageId']);
                }

                $this->view->assign('adminAccept', 1);
            }
        }
    }
}
