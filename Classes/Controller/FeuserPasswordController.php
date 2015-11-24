<?php
namespace Evoweb\SfRegister\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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
     * Form action
     *
     * @return void
     */
    public function formAction()
    {
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            array(
                'settings' => $this->settings,
            )
        );
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\Password $password
     * @return void
     * @validate $password Evoweb.SfRegister:User
     */
    public function saveAction(\Evoweb\SfRegister\Domain\Model\Password $password)
    {
        if (\Evoweb\SfRegister\Services\Login::isLoggedIn()) {
            $user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__,
                array('user' => &$user, 'settings' => $this->settings)
            );

            $user->setPassword($this->encryptPassword($password->getPassword(), $this->settings));

            $this->userRepository->update($user);

            $this->objectManager->get(\Evoweb\SfRegister\Services\Session::class)->remove('captchaWasValidPreviously');
        }
    }
}
