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

use Evoweb\SfRegister\Controller\Event;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An frontend user password controller
 */
class FeuserPasswordController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'password';

    public function formAction(\Evoweb\SfRegister\Domain\Model\Password $password = null)
    {
        $this->eventDispatcher->dispatch(new Event\PasswordFormEvent($password, $this->settings));

        $this->view->assign('password', $password);
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\Password $password
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="password")
     */
    public function saveAction(\Evoweb\SfRegister\Domain\Model\Password $password)
    {
        if ($this->userIsLoggedIn()) {
            $userId = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);

            $this->eventDispatcher->dispatch(new Event\PasswordSaveEvent($user, $this->settings));

            $user->setPassword($this->encryptPassword($password->getPassword()));

            $this->userRepository->update($user);

            /** @var \Evoweb\SfRegister\Services\Session $session */
            $session = GeneralUtility::makeInstance(\Evoweb\SfRegister\Services\Session::class);
            $session->remove('captchaWasValidPreviously');
        }
    }
}
