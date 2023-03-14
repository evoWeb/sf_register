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

use Evoweb\SfRegister\Controller\Event\PasswordFormEvent;
use Evoweb\SfRegister\Controller\Event\PasswordSaveEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Services\Session;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\HtmlResponse;

/**
 * A frontend user password controller
 */
class FeuserPasswordController extends FeuserController
{
    protected string $controller = 'Password';

    protected Session $session;

    public function __construct(
        Context $context,
        File $fileService,
        FrontendUserRepository $userRepository,
        FrontendUserGroupRepository $userGroupRepository,
        Session $session
    ) {
        $this->session = $session;
        parent::__construct($context, $fileService, $userRepository, $userGroupRepository);
    }

    public function formAction(Password $password = null): ResponseInterface
    {
        if ($password === null) {
            $password = new Password();
        }
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
        }

        return new HtmlResponse($this->view->render());
    }
}
