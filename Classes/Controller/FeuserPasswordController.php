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

use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Controller\Event\PasswordFormEvent;
use Evoweb\SfRegister\Controller\Event\PasswordSaveEvent;
use Evoweb\SfRegister\Services\Session;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An frontend user password controller
 */
class FeuserPasswordController extends FeuserController
{
    protected string $controller = 'password';

    public function formAction(Password $password = null): ResponseInterface
    {
        $this->eventDispatcher->dispatch(new PasswordFormEvent($password, $this->settings));

        $this->view->assign('password', $password);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Save action
     *
     * @param Password $password
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="password")
     */
    public function saveAction(Password $password): ResponseInterface
    {
        if ($this->userIsLoggedIn()) {
            $userId = $this->getTypoScriptFrontendController()->fe_user->user['uid'];
            /** @var FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);

            $this->eventDispatcher->dispatch(new PasswordSaveEvent($user, $this->settings));

            $user->setPassword($this->encryptPassword($password->getPassword()));

            $this->userRepository->update($user);

            /** @var Session $session */
            $session = GeneralUtility::makeInstance(Session::class);
            $session->remove('captchaWasValidPreviously');
        }

        return new HtmlResponse($this->view->render());
    }
}
