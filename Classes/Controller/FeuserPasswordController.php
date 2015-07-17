<?php
namespace Evoweb\SfRegister\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
        $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__, array(
                'settings' => $this->settings,
            ));
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\Password $password
     *
     * @return void
     * @validate $password Evoweb.SfRegister:User
     */
    public function saveAction(\Evoweb\SfRegister\Domain\Model\Password $password)
    {
        if (\Evoweb\SfRegister\Services\Login::isLoggedIn()) {
            $user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

            $this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__, array(
                    'user' => &$user,
                    'settings' => $this->settings,
                ));

            $user->setPassword($this->encryptPassword($password->getPassword(), $this->settings));

            $this->userRepository->update($user);

            $this->objectManager->get('Evoweb\\SfRegister\\Services\\Session')
                ->remove('captchaWasValidPreviously');
        }
    }
}
