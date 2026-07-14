<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Controller;

use Evoweb\SfRegister\Controller\Event\PasswordFormEvent;
use Evoweb\SfRegister\Controller\Event\PasswordSaveEvent;
use Evoweb\SfRegister\Domain\Model\Password;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Attribute;

/**
 * Change password of frontend user controller
 */
class FeuserPasswordController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, save';

    public function __construct(
        protected ModifyValidator $modifyValidator,
        protected FileService $fileService,
        protected FrontendUserRepository $userRepository,
        protected FrontendUserService $frontendUserService,
    ) {
        parent::__construct($modifyValidator, $fileService, $userRepository);
    }

    public function formAction(?Password $password = null): ResponseInterface
    {
        if (!$this->frontendUserService->userIsLoggedIn()) {
            $this->view->assign('notLoggedIn', true);
        }

        if ($password === null) {
            $password = new Password();
        }

        $event = new PasswordFormEvent($password, $this->settings);
        $this->eventDispatcher->dispatch($event);
        $password = $event->getPassword();
        $this->view->assign('password', $password);

        return new HtmlResponse($this->view->render());
    }

    public function saveAction(
        #[Attribute\Validate(validator: UserValidator::class)]
        Password $password
    ): ResponseInterface {
        $statusCode = 200;
        if ($this->frontendUserService->userIsLoggedIn()) {
            // Behaviour-preserving: keep df53334 behaviour where getLoggedInUser() may return null
            // (uncaught TypeError into the non-nullable PasswordSaveEvent constructor). 30e771a's
            // `?? new FrontendUser()` changed behaviour and is deferred to a later fix step.
            $user = $this->frontendUserService->getLoggedInUser();
            // @phpstan-ignore-next-line argument.type
            $event = new PasswordSaveEvent($user, $this->settings);
            $this->eventDispatcher->dispatch($event);
            $user = $event->getUser();

            $user->setPassword($this->encryptPassword($password->getPassword()));

            try {
                $this->userRepository->update($user);
            } catch (Exception) {
                $statusCode = 500;
            }
        }

        return new HtmlResponse($this->view->render(), $statusCode);
    }
}
