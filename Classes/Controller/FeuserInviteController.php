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
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\Mail;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Services\Session;
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
        protected File $fileService,
        protected FrontendUserRepository $userRepository,
        protected FrontendUserService $frontendUserService,
    ) {
        parent::__construct($modifyValidator, $fileService, $userRepository);
    }

    public function formAction(FrontendUser $user = null): ResponseInterface
    {
        if ($user === null && $this->frontendUserService->userIsLoggedIn()) {
            $user = $this->frontendUserService->getLoggedInUser();
        }

        // user is logged
        if ($user instanceof FrontendUser) {
            $user = $this->eventDispatcher->dispatch(new InviteFormEvent($user, $this->settings))->getUser();

            $this->view->assign('user', $user);
        }

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function inviteAction(FrontendUser $user): ResponseInterface
    {
        $doNotSendInvitation = $this->eventDispatcher->dispatch(new InviteInviteEvent($user, $this->settings, false))
            ->isDoNotSendInvitation();

        $user = $this->sendEmails($user, __FUNCTION__);

        if (!$doNotSendInvitation) {
            /** @var Mail $mailService */
            $mailService = GeneralUtility::makeInstance(Mail::class);
            $mailService->overrideSettings($this->settings);
            $user = $mailService->sendInvitation($user, 'ToRegister');
        }

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class);
        $session->remove('captchaWasValid');

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }
}
