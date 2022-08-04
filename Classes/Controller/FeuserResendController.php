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

use Evoweb\SfRegister\Controller\Event\ResendFormEvent;
use Evoweb\SfRegister\Controller\Event\ResendMailEvent;
use Evoweb\SfRegister\Domain\Model\Email;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

/**
 * An frontend user resend controller
 */
class FeuserResendController extends FeuserController
{
    protected string $controller = 'Resend';

    public function formAction(Email $email = null): ResponseInterface
    {
        if ($email === null) {
            $email = new Email();
        }

        $email = $this->eventDispatcher->dispatch(new ResendFormEvent($email, $this->settings))->getEmail();

        $userId = $this->context->getAspect('frontend.user')->get('id');
        $email = $email ?? $this->userRepository->findByUid($userId);

        if ($email) {
            $this->view->assign('email', ['email' => $email->getEmail()]);
        }

        return new HtmlResponse($this->view->render());
    }

    /**
     * Save action
     *
     * @param Email $email
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="email")
     */
    public function mailAction(Email $email): ResponseInterface
    {
        $email = $this->eventDispatcher->dispatch(new ResendMailEvent($email, $this->settings))->getEmail();

        /** @var FrontendUser $user */
        $user = $this->userRepository->findByEmail($email->getEmail());

        if ($user instanceof FrontendUser) {
            $this->sendEmails($user, __FUNCTION__);
        }

        return new HtmlResponse($this->view->render());
    }
}
