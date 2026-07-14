<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Controller;

use DateTime;
use Evoweb\SfRegister\Controller\Event\CreateAcceptEvent;
use Evoweb\SfRegister\Controller\Event\CreateConfirmEvent;
use Evoweb\SfRegister\Controller\Event\CreateDeclineEvent;
use Evoweb\SfRegister\Controller\Event\CreateFormEvent;
use Evoweb\SfRegister\Controller\Event\CreatePreviewEvent;
use Evoweb\SfRegister\Controller\Event\CreateRefuseEvent;
use Evoweb\SfRegister\Controller\Event\CreateSaveEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\FrontenUserGroup as FrontenUserGroupService;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Services\Session as SessionService;
use Evoweb\SfRegister\Services\Setup\CheckFactory;
use Evoweb\SfRegister\Services\Setup\CheckInterface;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Attribute;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Create frontend user controller
 */
class FeuserCreateController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, preview, proxy, save, confirm, refuse, accept, decline,'
        .  ' confirmForm, refuseForm, acceptForm, declineForm, removeImage';

    /**
     * @var string[]
     */
    protected array $ignoredActions = [
        'confirmAction',
        'refuseAction',
        'acceptAction',
        'declineAction',
        'confirmFormAction',
        'refuseFormAction',
        'acceptFormAction',
        'declineFormAction'
    ];

    public function __construct(
        protected ModifyValidator $modifyValidator,
        protected FileService $fileService,
        protected FrontendUserRepository $userRepository,
        protected MailService $mailService,
        protected FrontendUserService $frontendUserService,
        protected FrontenUserGroupService $frontenUserGroupService,
        protected SessionService $sessionService,
        protected CheckFactory $checkFactory,
    ) {
        parent::__construct($modifyValidator, $fileService, $userRepository);
    }

    public function formAction(?FrontendUser $user = null): ResponseInterface
    {
        $setupResponse = $this->setupCheck();
        if ($setupResponse) {
            return $setupResponse;
        }

        if ($user) {
            $event = new CreateFormEvent($user, $this->settings);
            $this->eventDispatcher->dispatch($event);
            $user = $event->getUser();
            $this->view->assign('user', $user);
        }

        return new HtmlResponse($this->view->render());
    }

    public function previewAction(
        #[Attribute\Validate(validator: UserValidator::class)]
        FrontendUser $user
    ): ResponseInterface {
        if ($this->request->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
        }

        $event = new CreatePreviewEvent($user, $this->settings);
        $this->eventDispatcher->dispatch($event);
        $user = $event->getUser();
        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    public function saveAction(
        #[Attribute\Validate(validator: UserValidator::class)]
        FrontendUser $user
    ): ResponseInterface {
        if (
            ($this->settings['confirmEmailPostCreate'] ?? false)
            || ($this->settings['acceptEmailPostCreate'] ?? false)
        ) {
            $user->setDisable(true);
            $user = $this->frontenUserGroupService->changeUsergroup(
                $this->settings,
                $user,
                (int)($this->settings['usergroupPostSave'] ?? 0)
            );
        } else {
            $user = $this->frontenUserGroupService->changeUsergroup(
                $this->settings,
                $user,
                (int)($this->settings['usergroup'] ?? 0)
            );
            $this->fileService->moveTemporaryImage($user);
        }

        if ($this->settings['useEmailAddressAsUsername'] ?? false) {
            $user->setUsername($user->getEmail());
        }

        $event = new CreateSaveEvent($user, $this->settings);
        $this->eventDispatcher->dispatch($event);
        $user = $event->getUser();

        try {
            // Persist user to get valid uid
            $plainPassword = $user->getPassword();
            // Avoid plain password being persisted
            $user->setPassword('');
            $this->userRepository->add($user);
            $this->persistAll();

            // Write back a plain password
            $user->setPassword($plainPassword);
            /** @var FrontendUser $user */
            $user = $this->mailService->sendEmails(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                __FUNCTION__
            );

            // Encrypt plain password
            if ($user->getPassword()) {
                $user->setPassword($this->encryptPassword($user->getPassword()));
            }

            $this->userRepository->update($user);
            $this->persistAll();
        } catch (IllegalObjectTypeException | UnknownObjectException) {
        }

        $this->sessionService->remove('captchaWasValid');

        $this->view->assign('user', $user);

        $redirectPageId = (int)($this->settings['redirectPostRegistrationPageId'] ?? 0);
        if ($this->settings['autologinPostRegistration'] ?? false) {
            $this->frontendUserService->autoLogin($this->request, $user, $redirectPageId);
        }

        $redirectResponse = null;
        if ($redirectPageId > 0) {
            $redirectResponse = $this->frontendUserService->redirectToPage($this->request, $redirectPageId);
        }

        return $redirectResponse ?: new HtmlResponse($this->view->render());
    }

    public function confirmFormAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        // Microsoft Safelinks is said to call the page by HEAD command for verification.
        // So: if not HEAD, proceed normally. Otherwise, show an intermediate page.
        if ($this->request->getMethod() !== 'HEAD' && !$this->settings['forceConfirmationButtonForEmailLinks']) {
            return $this->confirmAction($user, $hash);
        }

        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);
        }

        return $this->htmlResponse();
    }

    /**
     * Confirm the registration process by user. Can be followed by acceptance of admin
     */
    public function confirmAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        $redirectResponse = null;
        if ($user === null) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (
                $user->getActivatedOn() || $this->frontenUserGroupService->isUserInUserGroups(
                    $user,
                    $this->frontenUserGroupService->getConfiguredUserGroups(
                        $this->settings,
                        (int)($this->settings['usergroupPostConfirm'] ?? 0)
                    )
                )
            ) {
                $this->view->assign('userAlreadyConfirmed', 1);
            } else {
                $user = $this->frontenUserGroupService->changeUsergroup(
                    $this->settings,
                    $user,
                    (int)($this->settings['usergroupPostConfirm'] ?? 0)
                );
                $this->fileService->moveTemporaryImage($user);
                $user->setActivatedOn(new DateTime('now'));

                if (!($this->settings['acceptEmailPostConfirm'] ?? false)) {
                    $user->setDisable(false);
                }

                $event = new CreateConfirmEvent($user, $this->settings);
                $this->eventDispatcher->dispatch($event);
                $user = $event->getUser();
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
                    $this->persistAll();
                } catch (IllegalObjectTypeException | UnknownObjectException) {
                }

                $this->view->assign('userConfirmed', 1);

                $redirectPageId = (int)($this->settings['redirectPostActivationPageId'] ?? 0);
                if ($this->settings['autologinPostConfirmation'] ?? false) {
                    $this->frontendUserService->autoLogin($this->request, $user, $redirectPageId);
                }

                if ($redirectPageId > 0) {
                    $redirectResponse = $this->frontendUserService->redirectToPage($this->request, $redirectPageId);
                }
            }
        }

        return $redirectResponse ?: new HtmlResponse($this->view->render());
    }

    /**
     * @throws UnknownObjectException
     * @throws IllegalObjectTypeException
     */
    public function refuseFormAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        // Microsoft Safelinks is said to call the page by HEAD command for verification.
        // So: if not HEAD, proceed normally. Otherwise, show an intermediate page.
        if ($this->request->getMethod() !== 'HEAD' && !$this->settings['forceConfirmationButtonForEmailLinks']) {
            return $this->refuseAction($user, $hash);
        }

        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);
        }

        return $this->htmlResponse();
    }

    /**
     * Refuse registration process by user with removing the user data
     *
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function refuseAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $event = new CreateRefuseEvent($user, $this->settings);
            $this->eventDispatcher->dispatch($event);
            $user = $event->getUser();
            $this->view->assign('user', $user);

            if ($user->getImage()->count()) {
                $image = $user->getImage()->current();
                if ($image) {
                    $this->fileService->removeFile($image);
                    $this->removeImageFromUserAndRequest($user);
                }
            }

            $this->userRepository->remove($user);

            $this->mailService->sendEmails(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                __FUNCTION__
            );

            $this->view->assign('userRefused', 1);
        }

        return new HtmlResponse($this->view->render());
    }

    public function acceptFormAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        // Microsoft Safelinks is said to call the page by HEAD command for verification.
        // So: if not HEAD, proceed normally. Otherwise, show an intermediate page.
        if ($this->request->getMethod() !== 'HEAD' && !$this->settings['forceConfirmationButtonForEmailLinks']) {
            return $this->acceptAction($user, $hash);
        }

        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);
        }

        return $this->htmlResponse();
    }

    /**
     * Accept the registration process by admin after user confirmation
     */
    public function acceptAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);

            if (
                !$user->getDisable() || $this->frontenUserGroupService->isUserInUserGroups(
                    $user,
                    $this->frontenUserGroupService->getConfiguredUserGroups(
                        $this->settings,
                        (int)($this->settings['usergroupPostAccept'] ?? 0)
                    )
                )
            ) {
                $this->view->assign('userAlreadyAccepted', 1);
            } else {
                $user = $this->frontenUserGroupService->changeUsergroup(
                    $this->settings,
                    $user,
                    (int)($this->settings['usergroupPostAccept'] ?? 0)
                );
                $user->setDisable(false);

                if (!($this->settings['confirmEmailPostAccept'] ?? false)) {
                    $user->setActivatedOn(new DateTime('now'));
                }

                $event = new CreateAcceptEvent($user, $this->settings);
                $this->eventDispatcher->dispatch($event);
                $user = $event->getUser();

                try {
                    $this->userRepository->update($user);
                } catch (IllegalObjectTypeException | UnknownObjectException) {
                }

                $this->mailService->sendEmails(
                    $this->request,
                    $this->settings,
                    $user,
                    $this->getControllerName(),
                    __FUNCTION__
                );

                $this->view->assign('userAccepted', 1);
            }
        }

        return new HtmlResponse($this->view->render());
    }

    /**
     * @throws UnknownObjectException
     * @throws IllegalObjectTypeException
     */
    public function declineFormAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        // Microsoft Safelinks is said to call the page by HEAD command for verification.
        // So: if not HEAD, proceed normally. Otherwise, show an intermediate page.
        if ($this->request->getMethod() !== 'HEAD' && !$this->settings['forceConfirmationButtonForEmailLinks']) {
            return $this->declineAction($user, $hash);
        }

        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $this->view->assign('user', $user);
        }

        return $this->htmlResponse();
    }

    /**
     * Decline the registration process by admin with removing the user data
     *
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function declineAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userNotFound', 1);
        } else {
            $event = new CreateDeclineEvent($user, $this->settings);
            $this->eventDispatcher->dispatch($event);
            $user = $event->getUser();
            $this->view->assign('user', $user);

            if ($user->getImage()->count()) {
                $image = $user->getImage()->current();
                if ($image) {
                    $this->fileService->removeFile($image);
                    $this->removeImageFromUserAndRequest($user);
                }
            }

            $this->userRepository->remove($user);

            $this->mailService->sendEmails(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                __FUNCTION__
            );

            $this->view->assign('userDeclined', 1);
        }

        return new HtmlResponse($this->view->render());
    }

    protected function setupCheck(): ?ResponseInterface
    {
        $setupResponse = null;

        $setupChecks = $this->checkFactory->getCheckInstances();
        foreach ($setupChecks as $setupCheck) {
            if ($setupCheck instanceof CheckInterface && $setupResponse = $setupCheck->check($this->settings)) {
                break;
            }
        }

        return $setupResponse;
    }
}
