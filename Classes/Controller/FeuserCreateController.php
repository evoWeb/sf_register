<?php

declare(strict_types=1);

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
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * A frontend user create controller
 */
class FeuserCreateController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, preview, proxy, save, confirm, refuse, accept, decline, removeImage';

    /**
     * @var string[]
     */
    protected array $ignoredActions = ['confirmAction', 'refuseAction', 'acceptAction', 'declineAction'];

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
            $user = $this->eventDispatcher->dispatch(new CreateFormEvent($user, $this->settings))->getUser();
            $this->view->assign('user', $user);
        }

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function previewAction(FrontendUser $user): ResponseInterface
    {
        if ($this->request->hasArgument('temporaryImage')) {
            $this->view->assign('temporaryImage', $this->request->getArgument('temporaryImage'));
        }

        $user = $this->eventDispatcher->dispatch(new CreatePreviewEvent($user, $this->settings))->getUser();
        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function saveAction(FrontendUser $user): ResponseInterface
    {
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

        $user = $this->eventDispatcher->dispatch(new CreateSaveEvent($user, $this->settings))->getUser();

        try {
            // Persist user to get valid uid
            $plainPassword = $user->getPassword();
            // Avoid plain password being persisted
            $user->setPassword('');
            $this->userRepository->add($user);
            $this->persistAll();

            // Write back plain password
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

        $redirectResponse = null;
        $redirectPageId = (int)($this->settings['redirectPostRegistrationPageId'] ?? 0);
        if ($this->settings['autologinPostRegistration'] ?? false) {
            $this->frontendUserService->autoLogin($this->request, $user, $redirectPageId);
        }

        if ($redirectResponse === null && $redirectPageId > 0) {
            $redirectResponse = $this->frontendUserService->redirectToPage($this->request, $redirectPageId);
        }

        return $redirectResponse ?: new HtmlResponse($this->view->render());
    }

    /**
     * Confirm registration process by user. Can be followed by acceptance of admin
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
                $user->setActivatedOn(new \DateTime('now'));

                if (!($this->settings['acceptEmailPostConfirm'] ?? false)) {
                    $user->setDisable(false);
                }

                $user = $this->eventDispatcher->dispatch(new CreateConfirmEvent($user, $this->settings))->getUser();
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

                if ($redirectResponse === null && $redirectPageId > 0) {
                    $redirectResponse = $this->frontendUserService->redirectToPage($this->request, $redirectPageId);
                }
            }
        }

        return $redirectResponse ?: new HtmlResponse($this->view->render());
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
            $user = $this->eventDispatcher->dispatch(new CreateRefuseEvent($user, $this->settings))->getUser();
            $this->view->assign('user', $user);

            if ($user->getImage()->count()) {
                $image = $user->getImage()->current();
                $this->fileService->removeFile($image);
                $this->removeImageFromUserAndRequest($user);
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

    /**
     * Accept registration process by admin after user confirmation
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
                    $user->setActivatedOn(new \DateTime('now'));
                }

                $user = $this->eventDispatcher->dispatch(new CreateAcceptEvent($user, $this->settings))->getUser();

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
     * Decline registration process by admin with removing the user data
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
            $user = $this->eventDispatcher->dispatch(new CreateDeclineEvent($user, $this->settings))->getUser();
            $this->view->assign('user', $user);

            if ($user->getImage()->count()) {
                $image = $user->getImage()->current();
                $this->fileService->removeFile($image);
                $this->removeImageFromUserAndRequest($user);
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
            if ($setupResponse = $setupCheck->check($this->settings)) {
                break;
            }
        }

        return $setupResponse;
    }
}
