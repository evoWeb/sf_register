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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An frontend user create controller
 */
class FeuserInviteController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'invite';

    public function formAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null)
    {
        if (is_null($user) && $this->userIsLoggedIn()) {
            $userId = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
        }

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'user' => &$user,
                'settings' => $this->settings,
            ]
        );

        $this->view->assign('user', $user);
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function inviteAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        $type = 'SendInvitation';
        $doNotSendInvitation = false;

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'user' => &$user,
                'settings' => $this->settings,
                'doNotSendInvitation' => &$doNotSendInvitation,
            ]
        );

        $user = $this->sendEmails($user, $type);

        if (!$doNotSendInvitation) {
            /** @var \Evoweb\SfRegister\Services\Mail $mailService */
            $mailService = GeneralUtility::getContainer()->get(\Evoweb\SfRegister\Services\Mail::class);
            $user = $mailService->sendInvitation($user, 'ToRegister');
        }

        /** @var \Evoweb\SfRegister\Services\Session $session */
        $session = GeneralUtility::makeInstance(\Evoweb\SfRegister\Services\Session::class);
        $session->remove('captchaWasValidPreviously');

        $this->view->assign('user', $user);
    }
}
