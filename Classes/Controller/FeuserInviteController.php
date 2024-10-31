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
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * An frontend user invite controller
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
    ) {
        parent::__construct($modifyValidator, $fileService, $userRepository);
    }

    public function formAction(FrontendUser $user = null): ResponseInterface
    {
        if ($user === null) {
            if ($this->frontendUserService->userIsLoggedIn()) {
                $user = $this->frontendUserService->getLoggedInUser();
            } else {
                $user = GeneralUtility::makeInstance(FrontendUser::class);
            }
        }

        $user = $this->eventDispatcher->dispatch(new InviteFormEvent($user, $this->settings))->getUser();
        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function inviteAction(FrontendUser $user): ResponseInterface
    {
        /** @var FrontendUser $user */
        $user = $this->mailService->sendEmails(
            $this->request,
            $this->settings,
            $user,
            $this->getControllerName(),
            __FUNCTION__
        );

        $event = new InviteInviteEvent($user, $this->settings, false);
        $doNotSendInvitation = $this->eventDispatcher->dispatch($event)->isDoNotSendInvitation();
        if (!$doNotSendInvitation) {
            $user = $this->mailService->sendInvitation(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                'ToRegister'
            );
        }

        /** @var SessionService $session */
        $session = GeneralUtility::makeInstance(SessionService::class);
        $session->remove('captchaWasValid');

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }
}
