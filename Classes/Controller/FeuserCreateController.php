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

use Evoweb\SfRegister\Controller\Event\CreateAcceptEvent;
use Evoweb\SfRegister\Controller\Event\CreateConfirmEvent;
use Evoweb\SfRegister\Controller\Event\CreateDeclineEvent;
use Evoweb\SfRegister\Controller\Event\CreateFormEvent;
use Evoweb\SfRegister\Controller\Event\CreatePreviewEvent;
use Evoweb\SfRegister\Controller\Event\CreateRefuseEvent;
use Evoweb\SfRegister\Controller\Event\CreateSaveEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Services\Session;
use Evoweb\SfRegister\Services\Setup\CheckFactory;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * A frontend user create controller
 */
class FeuserCreateController extends FeuserController
{
    protected string $controller = 'Create';

    protected array $ignoredActions = ['confirmAction', 'refuseAction', 'acceptAction', 'declineAction'];

    public function formAction(FrontendUser $user = null): ResponseInterface
    {
        $setupResponse = $this->setupCheck();

        if ($user) {
            $this->eventDispatcher->dispatch(new CreateFormEvent($user, $this->settings));
            $this->view->assign('user', $user);
        }

        return $setupResponse ?? new HtmlResponse($this->view->render());
    }

    /**
     * Preview action
     */
    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function previewAction(FrontendUser $user): ResponseInterface
    {
        if ($this->request->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
        }

        $this->eventDispatcher->dispatch(new CreatePreviewEvent($user, $this->settings));

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Save action
     */
    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function saveAction(FrontendUser $user): ResponseInterface
    {
        if (
            ($this->settings['confirmEmailPostCreate'] ?? false)
            || ($this->settings['acceptEmailPostCreate'] ?? false)
        ) {
            $user->setDisable(true);
            $user = $this->changeUsergroup($user, (int)($this->settings['usergroupPostSave'] ?? 0));
        } else {
            $user = $this->changeUsergroup($user, (int)($this->settings['usergroup'] ?? 0));
            $this->moveTemporaryImage($user);
        }

        if (($this->settings['useEmailAddressAsUsername'] ?? false)) {
            $user->setUsername($user->getEmail());
        }

        $this->eventDispatcher->dispatch(new CreateSaveEvent($user, $this->settings));

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

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class);
        $session->remove('captchaWasValidPreviously');

        if (($this->settings['autologinPostRegistration'] ?? false)) {
            $this->autoLogin($user, (int)($this->settings['redirectPostRegistrationPageId'] ?? 0));
        }

        if ((int)($this->settings['redirectPostRegistrationPageId'] ?? 0) > 0) {
            $this->redirectToPage((int)($this->settings['redirectPostRegistrationPageId'] ?? 0));
        }

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Confirm registration process by user
     * Could be followed by acceptance of admin
     */
    public function confirmAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (
                $user->getActivatedOn() || $this->isUserInUserGroups(
                    $user,
                    $this->getConfiguredUserGroups((int)($this->settings['usergroupPostConfirm'] ?? 0))
                )
            ) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                $user = $this->changeUsergroup($user, (int)($this->settings['usergroupPostConfirm'] ?? 0));
                $this->moveTemporaryImage($user);
                $user->setActivatedOn(new \DateTime('now'));

                if (!($this->settings['acceptEmailPostConfirm'] ?? false)) {
                    $user->setDisable(false);
                }

                $this->eventDispatcher->dispatch(new CreateConfirmEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                if (($this->settings['autologinPostConfirmation'] ?? false)) {
                    $this->persistAll();
                    $this->autoLogin($user, (int)($this->settings['redirectPostActivationPageId'] ?? 0));
                }

                if ((int)($this->settings['redirectPostActivationPageId'] ?? 0) > 0) {
                    $this->redirectToPage((int)($this->settings['redirectPostActivationPageId'] ?? 0));
                }

                $this->view->assign('userConfirmed', 1);
            }
        }

        return new HtmlResponse($this->view->render());
    }

    /**
     * Refuse registration process by user with removing the user data
     */
    public function refuseAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            $this->eventDispatcher->dispatch(new CreateRefuseEvent($user, $this->settings));

            $this->userRepository->remove($user);

            $this->sendEmails($user, __FUNCTION__);

            $this->view->assign('userRefused', 1);
        }

        return new HtmlResponse($this->view->render());
    }

    /**
     * Accept registration process by admin after user confirmation
     */
    public function acceptAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (
                !$user->getDisable() || $this->isUserInUserGroups(
                    $user,
                    $this->getConfiguredUserGroups((int)($this->settings['usergroupPostAccept'] ?? 0))
                )
            ) {
                $this->view->assign('userAlreadyAccepted', 1);
            } else {
                $user = $this->changeUsergroup($user, (int)($this->settings['usergroupPostAccept'] ?? 0));
                $user->setDisable(false);

                if (!($this->settings['confirmEmailPostAccept'] ?? false)) {
                    $user->setActivatedOn(new \DateTime('now'));
                }

                $this->eventDispatcher->dispatch(new CreateAcceptEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                $this->view->assign('userAccepted', 1);
            }
        }

        return new HtmlResponse($this->view->render());
    }

    /**
     * Decline registration process by admin with removing the user data
     */
    public function declineAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            $this->eventDispatcher->dispatch(new CreateDeclineEvent($user, $this->settings));

            $this->userRepository->remove($user);

            $this->sendEmails($user, __FUNCTION__);

            $this->view->assign('userDeclined', 1);
        }

        return new HtmlResponse($this->view->render());
    }

    protected function setupCheck(): ?ResponseInterface
    {
        $result = null;

        $setupChecks = GeneralUtility::makeInstance(CheckFactory::class)->getCheckInstances();
        foreach ($setupChecks as $setupCheck) {
            if (($result = $setupCheck->check($this->settings))) {
                break;
            }
        }

        return $result;
    }
}
