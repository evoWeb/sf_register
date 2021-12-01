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

use Evoweb\SfRegister\Controller\Event\EditAcceptEvent;
use Evoweb\SfRegister\Controller\Event\EditConfirmEvent;
use Evoweb\SfRegister\Controller\Event\EditFormEvent;
use Evoweb\SfRegister\Controller\Event\EditPreviewEvent;
use Evoweb\SfRegister\Controller\Event\EditSaveEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Services\Session as SessionService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

/**
 * An frontend user edit controller
 */
class FeuserEditController extends FeuserController
{
    protected string $controller = 'edit';

    protected array $ignoredActions = ['confirmAction', 'acceptAction'];

    public function formAction(FrontendUser $user = null): ResponseInterface
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
            /** @var FrontendUser $userData */
            $userData = $this->request->hasArgument('user') ?
                $this->request->getArgument('user') :
                $originalRequest->getArgument('user');
            if ($userData instanceof FrontendUser && $userData->getUid() != $userId) {
                $user = null;
            }
        }

        if ($user == null) {
            /** @var FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
        }

        if ($originalRequest !== null && $originalRequest->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $originalRequest->getArgument('temporaryImage'));
        }

        $this->eventDispatcher->dispatch(new EditFormEvent($user, $this->settings));

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Preview action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function previewAction(FrontendUser $user): ResponseInterface
    {
        if ($this->request->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
        }

        $this->eventDispatcher->dispatch(new EditPreviewEvent($user, $this->settings));

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Save action
     *
     * @param FrontendUser $user
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function saveAction(FrontendUser $user): ResponseInterface
    {
        if ($this->settings['confirmEmailPostEdit'] || $this->settings['acceptEmailPostEdit']) {
            // Remove user object from session to fetch it really from database
            /** @var Session $session */
            $session = GeneralUtility::makeInstance(Session::class);
            $session->unregisterObject($user);

            /** @var FrontendUser $userBeforeEdit */
            $userBeforeEdit = $this->userRepository->findByUid($user->getUid());

            // Now remove the fresh fetched and add the updated one to make it known again
            $session->unregisterObject($userBeforeEdit);
            $session->registerObject($user, 'sf-register-' . $user->getUid());

            $user->setEmailNew($user->getEmail());
            $user->setEmail($userBeforeEdit->getEmail() ?: $user->getEmail());
        } elseif ($this->settings['useEmailAddressAsUsername']) {
            $user->setUsername($user->getEmail());
        }

        $this->eventDispatcher->dispatch(new EditSaveEvent($user, $this->settings));

        $user = $this->sendEmails($user, __FUNCTION__);

        $this->userRepository->update($user);
        $this->persistAll();

        /** @var SessionService $session */
        $session = GeneralUtility::makeInstance(SessionService::class);
        $session->remove('captchaWasValidPreviously');

        if ($this->settings['forwardToEditAfterSave']) {
            $response = new ForwardResponse('form');
        } else {
            $this->view->assign('user', $user);
            $response = new HtmlResponse($this->view->render());
        }

        return $response;
    }

    public function confirmAction(FrontendUser $user = null, string $hash = null): ResponseInterface
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof FrontendUser)) {
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

                $this->eventDispatcher->dispatch(new EditConfirmEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                $this->view->assign('userConfirmed', 1);
            }

            if ($this->settings['autologinPostConfirmation']) {
                $this->persistAll();
                $this->autoLogin($user, (int)$this->settings['redirectPostActivationPageId']);
            }

            if ($this->settings['redirectPostActivationPageId']) {
                $this->redirectToPage((int)$this->settings['redirectPostActivationPageId']);
            }
        }

        return new HtmlResponse($this->view->render());
    }

    public function acceptAction(FrontendUser $user = null, string $hash = null): ResponseInterface
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof FrontendUser)) {
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

                $this->eventDispatcher->dispatch(new EditAcceptEvent($user, $this->settings));

                $this->userRepository->update($user);

                $this->sendEmails($user, __FUNCTION__);

                if ($this->settings['redirectPostActivationPageId']) {
                    $this->redirectToPage((int)$this->settings['redirectPostActivationPageId']);
                }

                $this->view->assign('adminAccept', 1);
            }
        }

        return new HtmlResponse($this->view->render());
    }
}
