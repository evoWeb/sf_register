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

use Evoweb\SfRegister\Controller\Event\InviteFormEvent;
use Evoweb\SfRegister\Controller\Event\InviteInviteEvent;
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
use TYPO3\CMS\Extbase\Attribute;

/**
 * Invite frontend user controller
 */
class FeuserInviteController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, invite';

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

    public function formAction(?FrontendUser $user = null): ResponseInterface
    {
        if ($user === null) {
            if ($this->frontendUserService->userIsLoggedIn()) {
                // Behaviour-preserving: keep df53334 behaviour where getLoggedInUser() may return null
                // (uncaught TypeError into the non-nullable InviteFormEvent constructor). 30e771a's
                // `?? new FrontendUser()` changed behaviour and is deferred to a later fix step.
                $user = $this->frontendUserService->getLoggedInUser();
            } else {
                $user = GeneralUtility::makeInstance(FrontendUser::class);
            }
        }

        // @phpstan-ignore-next-line argument.type
        $event = new InviteFormEvent($user, $this->settings);
        $this->eventDispatcher->dispatch($event);
        $user = $event->getUser();
        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    public function inviteAction(
        #[Attribute\Validate(validator: UserValidator::class)]
        FrontendUser $user
    ): ResponseInterface {
        /** @var FrontendUser $user */
        $user = $this->mailService->sendEmails(
            $this->request,
            $this->settings,
            $user,
            $this->getControllerName(),
            __FUNCTION__
        );

        $event = new InviteInviteEvent($user, $this->settings, false);
        $this->eventDispatcher->dispatch($event);
        $doNotSendInvitation = $event->isDoNotSendInvitation();
        if (!$doNotSendInvitation) {
            $user = $this->mailService->sendInvitation(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                'ToRegister'
            );
        }

        $this->sessionService->remove('captchaWasValid');

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }
}
