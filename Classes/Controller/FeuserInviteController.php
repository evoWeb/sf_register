<?php
namespace Evoweb\SfRegister\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
            /** @noinspection PhpInternalEntityUsedInspection */
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
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb.SfRegister:User", param="user")
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
            $mailService = $this->objectManager->get(\Evoweb\SfRegister\Services\Mail::class);
            $user = $mailService->sendInvitation($user, 'ToRegister');
        }

        $this->objectManager->get(\Evoweb\SfRegister\Services\Session::class)->remove('captchaWasValidPreviously');

        $this->view->assign('user', $user);
    }
}
