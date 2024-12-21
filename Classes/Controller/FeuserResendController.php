<?php

declare(strict_types=1);

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

use Evoweb\SfRegister\Controller\Event\ResendFormEvent;
use Evoweb\SfRegister\Controller\Event\ResendMailEvent;
use Evoweb\SfRegister\Domain\Model\Email;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Services\Session as SessionService;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * An frontend user resend controller
 */
class FeuserResendController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, mail';

    public function __construct(
        protected ModifyValidator $modifyValidator,
        protected FileService $fileService,
        protected FrontendUserRepository $userRepository,
        protected MailService $mailService,
        protected FrontendUserService $frontendUserService,
        protected SessionService $sessionService,
    ) {
        parent::__construct($modifyValidator, $fileService, $userRepository);
    }

    public function formAction(Email $email = null): ResponseInterface
    {
        if ($email === null) {
            $email = new Email();
            try {
                $user = $this->frontendUserService->getLoggedInUser();
                $email->setEmail($user->getEmail());
            } catch (\Exception) {
            }
        }

        $email = $this->eventDispatcher->dispatch(new ResendFormEvent($email, $this->settings))->getEmail();
        $this->view->assign('email', $email);

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'email'])]
    public function mailAction(Email $email): ResponseInterface
    {
        $email = $this->eventDispatcher->dispatch(new ResendMailEvent($email, $this->settings))->getEmail();
        $user = $this->userRepository->findByEmail($email->getEmail());

        if ($user instanceof FrontendUser) {
            $this->mailService->sendEmails(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                __FUNCTION__
            );
        }

        $this->sessionService->remove('captchaWasValid');

        return new HtmlResponse($this->view->render());
    }
}
