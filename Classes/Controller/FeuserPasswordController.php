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
 * An frontend user password controller
 */
class FeuserPasswordController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'password';

    public function formAction()
    {
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'settings' => $this->settings,
            ]
        );
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\Password $password
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb.SfRegister:User", param="password")
     */
    public function saveAction(\Evoweb\SfRegister\Domain\Model\Password $password)
    {
        if ($this->userIsLoggedIn()) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $userId = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);

            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__,
                [
                    'user' => &$user,
                    'settings' => $this->settings
                ]
            );

            $user->setPassword($this->encryptPassword($password->getPassword(), $this->settings));

            $this->userRepository->update($user);

            $this->objectManager->get(\Evoweb\SfRegister\Services\Session::class)->remove('captchaWasValidPreviously');
        }
    }
}
