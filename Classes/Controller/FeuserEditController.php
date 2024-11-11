<?php

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

namespace Evoweb\SfRegister\Controller;

use Evoweb\SfRegister\Controller\Event\EditAcceptEvent;
use Evoweb\SfRegister\Controller\Event\EditConfirmEvent;
use Evoweb\SfRegister\Controller\Event\EditFormEvent;
use Evoweb\SfRegister\Controller\Event\EditPreviewEvent;
use Evoweb\SfRegister\Controller\Event\EditSaveEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Services\Session as SessionService;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

/**
 * A frontend user edit controller
 */
class FeuserEditController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, preview, proxy, save, confirm, accept, removeImage';

    /**
     * @var string[]
     */
    protected array $ignoredActions = ['confirmAction', 'acceptAction'];

    public function __construct(
        protected ModifyValidator $modifyValidator,
        protected FileService $fileService,
        protected FrontendUserRepository $userRepository,
        protected MailService $mailService,
        protected FrontendUserService $frontendUserService,
        protected SessionService $sessionService,
    ) {
        parent::__construct($modifyValidator, $fileService, $userRepository);
    }

    public function formAction(FrontendUser $user = null): ResponseInterface
    {
        if ($user === null) {
            $user = $this->frontendUserService->getLoggedInRequestUser($this->request);
        }

        if ($user instanceof FrontendUser) {
            $user = $this->eventDispatcher->dispatch(new EditFormEvent($user, $this->settings))->getUser();
        }

        $this->view->assign('user', $user);

        $originalRequest = $this->request->getAttribute('extbase')->getOriginalRequest();
        if ($originalRequest !== null && $originalRequest->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $originalRequest->getArgument('temporaryImage'));
        }

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function previewAction(FrontendUser $user): ResponseInterface
    {
        if ($this->request->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
        }

        $user = $this->eventDispatcher->dispatch(new EditPreviewEvent($user, $this->settings))->getUser();
        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function saveAction(FrontendUser $user): ResponseInterface
    {
        if (
            ($this->settings['confirmEmailPostEdit'] ?? false)
            || ($this->settings['acceptEmailPostEdit'] ?? false)
        ) {
            // Remove user object from session to fetch it really from database
            /** @var Session $session */
            $session = GeneralUtility::makeInstance(Session::class);
            $session->unregisterObject($user);

            /** @var FrontendUser $userBeforeEdit */
            $userBeforeEdit = $this->userRepository->findByIdentifier($user->getUid());

            // Now remove the fresh fetched and add the updated one to make it known again
            $session->unregisterObject($userBeforeEdit);
            $session->registerObject($user, 'sf-register-' . $user->getUid());

            $user->setEmailNew($user->getEmail());
            $user->setEmail($userBeforeEdit->getEmail() ?: $user->getEmail());
        } else {
            $this->fileService->moveTemporaryImage($user);
        }

        if ($this->settings['useEmailAddressAsUsername'] ?? false) {
            $user->setUsername($user->getEmail());
        }

        $user = $this->eventDispatcher->dispatch(new EditSaveEvent($user, $this->settings))->getUser();
        /** @var FrontendUser $user */
        $user = $this->mailService->sendEmails(
            $this->request,
            $this->settings,
            $user,
            $this->getControllerName(),
            __FUNCTION__
        );

        try {
            $this->userRepository->update($user);
        } catch (\Exception) {}
        $this->persistAll();

        $this->sessionService->remove('captchaWasValid');

        if ($this->settings['forwardToEditAfterSave'] ?? false) {
            $response = new ForwardResponse('form');
        } else {
            $this->view->assign('user', $user);
            $response = new HtmlResponse($this->view->render());
        }

        return $response;
    }

    public function confirmAction(FrontendUser $user = null, string $hash = null): ResponseInterface
    {
        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        $redirectResponse = null;
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
                $this->fileService->moveTemporaryImage($user);

                if (!($this->settings['acceptEmailPostEdit'] ?? false)) {
                    $user->setEmail($user->getEmailNew());
                    $user->setEmailNew('');

                    if ($this->settings['useEmailAddressAsUsername'] ?? false) {
                        $user->setUsername($user->getEmail());
                    }
                }

                $user = $this->eventDispatcher->dispatch(new EditConfirmEvent($user, $this->settings))->getUser();
                try {
                    $this->userRepository->update($user);
                } catch (\Exception) {}

                $this->mailService->sendEmails(
                    $this->request,
                    $this->settings,
                    $user,
                    $this->getControllerName(),
                    __FUNCTION__
                );

                $this->view->assign('userConfirmed', 1);
            }

            $redirectPageId = (int)($this->settings['redirectPostActivationPageId'] ?? 0);
            if ($this->settings['autologinPostConfirmation'] ?? false) {
                $this->persistAll();
                $this->frontendUserService->autoLogin($this->request, $user, $redirectPageId);
            }

            if ($redirectResponse === null && $redirectPageId > 0) {
                $redirectResponse = $this->frontendUserService->redirectToPage($this->request, $redirectPageId);
            }
        }

        return $redirectResponse ?: new HtmlResponse($this->view->render());
    }

    public function acceptAction(FrontendUser $user = null, string $hash = null): ResponseInterface
    {
        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        $redirectResponse = null;
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

                if ($this->settings['useEmailAddressAsUsername'] ?? false) {
                    $user->setUsername($user->getEmail());
                }

                $user = $this->eventDispatcher->dispatch(new EditAcceptEvent($user, $this->settings))->getUser();
                try {
                    $this->userRepository->update($user);
                } catch (\Exception) {}

                $this->mailService->sendEmails(
                    $this->request,
                    $this->settings,
                    $user,
                    $this->getControllerName(),
                    __FUNCTION__
                );

                $redirectPageId = (int)($this->settings['redirectPostActivationPageId'] ?? 0);
                if ($redirectPageId > 0) {
                    $redirectResponse = $this->frontendUserService->redirectToPage($this->request, $redirectPageId);
                }

                $this->view->assign('adminAccept', 1);
            }
        }

        return $redirectResponse ?: new HtmlResponse($this->view->render());
    }
}
