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

use Evoweb\SfRegister\Controller\Event\InviteFormEvent;
use Evoweb\SfRegister\Controller\Event\InviteInviteEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Services\Mail;
use Evoweb\SfRegister\Services\Session;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An frontend user invite controller
 */
class FeuserInviteController extends FeuserController
{
    protected string $controller = 'invite';

    public function formAction(FrontendUser $user = null): ResponseInterface
    {
        if (is_null($user) && $this->userIsLoggedIn()) {
            $userId = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
            /** @var FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
        }

        $this->eventDispatcher->dispatch(new InviteFormEvent($user, $this->settings));

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Invite action
     *
     * @param FrontendUser $user
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function inviteAction(FrontendUser $user): ResponseInterface
    {
        $event = new InviteInviteEvent($user, $this->settings, false);
        $this->eventDispatcher->dispatch($event);
        $doNotSendInvitation = $event->isDoNotSendInvitation();

        $user = $this->sendEmails($user, __FUNCTION__);

        if (!$doNotSendInvitation) {
            /** @var Mail $mailService */
            $mailService = GeneralUtility::getContainer()->get(Mail::class);
            $user = $mailService->sendInvitation($user, 'ToRegister');
        }

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class);
        $session->remove('captchaWasValidPreviously');

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }
}
