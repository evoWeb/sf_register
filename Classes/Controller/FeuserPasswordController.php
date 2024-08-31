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

use Evoweb\SfRegister\Controller\Event\PasswordFormEvent;
use Evoweb\SfRegister\Controller\Event\PasswordSaveEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Services\FrontenUserGroup;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Services\Session;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * A frontend user password controller
 */
class FeuserPasswordController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, save';

    public function __construct(
        protected ModifyValidator $modifyValidator,
        protected Context $context,
        protected File $fileService,
        protected FrontendUserRepository $userRepository,
        protected Session $session
    ) {
        parent::__construct($modifyValidator, $context, $fileService, $userRepository);
    }

    public function formAction(Password $password = null): ResponseInterface
    {
        if ($password === null) {
            $password = new Password();
        }
        $password = $this->eventDispatcher->dispatch(new PasswordFormEvent($password, $this->settings))->getPassword();

        $this->view->assign('password', $password);

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'password'])]
    public function saveAction(Password $password): ResponseInterface
    {
        if ($this->userIsLoggedIn()) {
            /** @var FrontendUserAuthentication $frontendUser */
            $frontendUser = $this->request->getAttribute('frontend.user');
            $userId = $frontendUser->user['uid'];

            /** @var FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
            $user = $this->eventDispatcher->dispatch(new PasswordSaveEvent($user, $this->settings))->getUser();

            $user->setPassword($this->encryptPassword($password->getPassword()));

            $this->userRepository->update($user);
        }

        return new HtmlResponse($this->view->render());
    }
}
