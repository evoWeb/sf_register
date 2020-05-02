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
 * An frontend user create controller
 */
class FeuserCreateController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'create';

    /**
     * @var array
     */
    protected $ignoredActions = ['confirmAction', 'refuseAction', 'acceptAction', 'declineAction'];

    public function formAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null)
    {
        $originalRequest = $this->request->getOriginalRequest();
        if ($originalRequest !== null && $originalRequest->hasArgument('user')) {
            /** @var array $userData */
            $userData = $this->request->hasArgument('user') ?
                $this->request->getArgument('user') :
                $originalRequest->getArgument('user');
            if (isset($userData['uid'])) {
                unset($userData['uid']);
            }

            $propertyMappingConfiguration = $this->getPropertyMappingConfiguration(null, $userData);

            /** @var \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper */
            $propertyMapper = GeneralUtility::getContainer()
                ->get(\TYPO3\CMS\Extbase\Property\PropertyMapper::class);
            $user = $propertyMapper->convert(
                $userData,
                \Evoweb\SfRegister\Domain\Model\FrontendUser::class,
                $propertyMappingConfiguration
            );
        }

        if ($user) {
            $this->eventDispatcher->dispatch(new Event\CreateFormEvent($user, $this->settings));
            $this->view->assign('user', $user);
        }
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

        $this->eventDispatcher->dispatch(new Event\CreatePreviewEvent($user, $this->settings));

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
        if ($this->settings['confirmEmailPostCreate'] || $this->settings['acceptEmailPostCreate']) {
            $user->setDisable(true);
            $user = $this->changeUsergroup($user, (int) $this->settings['usergroupPostSave']);
        } else {
            $user = $this->changeUsergroup($user, (int) $this->settings['usergroup']);
            $this->moveTemporaryImage($user);
        }

        if ($this->settings['useEmailAddressAsUsername']) {
            $user->setUsername($user->getEmail());
        }

        $this->eventDispatcher->dispatch(new Event\CreateSaveEvent($user, $this->settings));

        // Persist user to get valid uid
        $plainPassword = $user->getPassword();
        // Avoid plain password being persisted
        $user->setPassword('');
        $this->userRepository->add($user);
        $this->persistAll();

        // Write back plain password
        $user->setPassword($plainPassword);
        $user = $this->sendEmails($user, __FUNCTION__);

        // Encrypt plain password
        if ($user->getPassword()) {
            $user->setPassword($this->encryptPassword($user->getPassword()));
        }
        $this->userRepository->update($user);
        $this->persistAll();

        /** @var \Evoweb\SfRegister\Services\Session $session */
        $session = GeneralUtility::makeInstance(\Evoweb\SfRegister\Services\Session::class);
        $session->remove('captchaWasValidPreviously');

        if ($this->settings['autologinPostRegistration']) {
            $this->autoLogin($user, (int) $this->settings['redirectPostRegistrationPageId']);
        }

        if ($this->settings['redirectPostRegistrationPageId']) {
            $this->redirectToPage((int) $this->settings['redirectPostRegistrationPageId']);
        }

        $this->view->assign('user', $user);
    }


    /**
     * Confirm registration process by user
     * Could be followed by acceptance of admin
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $hash
     */
    public function confirmAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (
                $user->getActivatedOn() || $this->isUserInUserGroups(
                    $user,
                    $this->getFollowingUserGroups((int) $this->settings['usergroupPostConfirm'])
                )
            ) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                $user = $this->changeUsergroup($user, (int) $this->settings['usergroupPostConfirm']);
                $this->moveTemporaryImage($user);
                $user->setActivatedOn(new \DateTime('now'));

                if (!$this->settings['acceptEmailPostConfirm']) {
                    $user->setDisable(false);
                }

                $this->eventDispatcher->dispatch(new Event\CreateConfirmEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                if ($this->settings['autologinPostConfirmation']) {
                    $this->persistAll();
                    $this->autoLogin($user, (int) $this->settings['redirectPostActivationPageId']);
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
     */
    public function refuseAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            $this->eventDispatcher->dispatch(new Event\CreateRefuseEvent($user, $this->settings));

            $this->userRepository->remove($user);

            $this->sendEmails($user, __FUNCTION__);

            $this->view->assign('userRefused', 1);
        }
    }


    /**
     * Accept registration process by admin after user confirmation
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $hash
     */
    public function acceptAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (
                !$user->getDisable() || $this->isUserInUserGroups(
                    $user,
                    $this->getFollowingUserGroups((int) $this->settings['usergroupPostAccept'])
                )
            ) {
                $this->view->assign('userAlreadyAccepted', 1);
            } else {
                $user = $this->changeUsergroup($user, (int) $this->settings['usergroupPostAccept']);
                $user->setDisable(false);

                if (!$this->settings['confirmEmailPostAccept']) {
                    $user->setActivatedOn(new \DateTime('now'));
                }

                $this->eventDispatcher->dispatch(new Event\CreateAcceptEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                $this->view->assign('userAccepted', 1);
            }
        }
    }

    /**
     * Decline registration process by admin with removing the user data
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $hash
     */
    public function declineAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            $this->eventDispatcher->dispatch(new Event\CreateDeclineEvent($user, $this->settings));

            $this->userRepository->remove($user);

            $this->sendEmails($user, __FUNCTION__);

            $this->view->assign('userDeclined', 1);
        }
    }
}
