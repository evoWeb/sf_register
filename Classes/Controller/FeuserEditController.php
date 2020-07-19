<?php

namespace Evoweb\SfRegister\Controller;

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Evoweb\SfRegister\Controller\Event;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An frontend user edit controller
 */
class FeuserEditController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'edit';

    /**
     * @var array
     */
    protected $ignoredActions = ['confirmAction', 'acceptAction'];

    public function formAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null)
    {
        $userId = $this->context->getAspect('frontend.user')->get('id');

        $originalRequest = $this->request->getOriginalRequest();
        if (
            (
                $this->request->hasArgument('user')
                || ($originalRequest !== null && $originalRequest->hasArgument('user'))
            )
            && $this->userIsLoggedIn()
        ) {
            /** @var array $userData */
            $userData = $this->request->hasArgument('user') ?
                $this->request->getArgument('user') :
                $originalRequest->getArgument('user');

            // only reconstitute user object if given user uid equals logged in user uid
            if (is_array($userData) && $userData['uid'] == $userId) {
                /** @var \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper */
                $propertyMapper = GeneralUtility::getContainer()
                    ->get(\TYPO3\CMS\Extbase\Property\PropertyMapper::class);
                $user = $propertyMapper->convert($userData, \Evoweb\SfRegister\Domain\Model\FrontendUser::class);
            }
        }

        if ($user == null) {
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
        }

        if ($originalRequest && $originalRequest->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $originalRequest->getArgument('temporaryImage'));
        }

        $this->eventDispatcher->dispatch(new Event\EditFormEvent($user, $this->settings));

        $this->view->assign('user', $user);
    }

    /**
     * Preview action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function previewAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        if ($this->request->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
        }

        $this->eventDispatcher->dispatch(new Event\EditPreviewEvent($user, $this->settings));

        $this->view->assign('user', $user);
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function saveAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        if (
            ($this->isNotifyAdmin('PostEditSave') || $this->isNotifyUser('PostEditSave'))
            && ($this->settings['confirmEmailPostEdit'] || $this->settings['acceptEmailPostEdit'])
        ) {
            // Remove user object from session to fetch it really from database
            /** @var \TYPO3\CMS\Extbase\Persistence\Generic\Session $session */
            $session = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Session::class);
            $session->unregisterObject($user);

            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $userBeforeEdit */
            $userBeforeEdit = $this->userRepository->findByUid($user->getUid());

            // Now remove the fresh fetched and add the updated one to make it known again
            $session->unregisterObject($userBeforeEdit);
            $session->registerObject($user, 'sf-register-' . $user->getUid());

            $user->setEmailNew($user->getEmail());
            $user->setEmail($userBeforeEdit->getEmail() ?: $user->getEmail());
        } elseif ($this->settings['useEmailAddressAsUsername']) {
            $user->setUsername($user->getEmail());
        }

        $this->eventDispatcher->dispatch(new Event\EditSaveEvent($user, $this->settings));

        $user = $this->sendEmails($user, __FUNCTION__);

        $this->userRepository->update($user);
        $this->persistAll();

        /** @var \Evoweb\SfRegister\Services\Session $session */
        $session = GeneralUtility::makeInstance(\Evoweb\SfRegister\Services\Session::class);
        $session->remove('captchaWasValidPreviously');

        if ($this->settings['forwardToEditAfterSave']) {
            $this->forward('form');
        }

        $this->view->assign('user', $user);
    }

    public function confirmAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            $userEmailNew = $user->getEmailNew();
            if ($user->getDisable()) {
                $this->view->assign('userNotConfirmed', 1);
            } elseif (empty($userEmailNew)) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                if (!$this->settings['acceptEmailPostEdit']) {
                    $user->setEmail($user->getEmailNew());
                    $user->setEmailNew('');

                    if ($this->settings['useEmailAddressAsUsername']) {
                        $user->setUsername($user->getEmail());
                    }
                }

                $this->eventDispatcher->dispatch(new Event\EditConfirmEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                $this->view->assign('userConfirmed', 1);
            }

            if ($this->settings['autologinPostConfirmation']) {
                $this->persistAll();
                $this->autoLogin($user, (int) $this->settings['redirectPostActivationPageId']);
            }

            if ($this->settings['redirectPostActivationPageId']) {
                $this->redirectToPage((int) $this->settings['redirectPostActivationPageId']);
            }
        }
    }

    public function acceptAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (!$user->getDisable()) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                if (!empty($user->getEmailNew())) {
                    $user->setEmail($user->getEmailNew());
                    $user->setEmailNew('');
                }

                if ($this->settings['useEmailAddressAsUsername']) {
                    $user->setUsername($user->getEmail());
                }

                $this->eventDispatcher->dispatch(new Event\EditAcceptEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                if ($this->settings['redirectPostActivationPageId']) {
                    $this->redirectToPage((int) $this->settings['redirectPostActivationPageId']);
                }

                $this->view->assign('adminAccept', 1);
            }
        }
    }
}
