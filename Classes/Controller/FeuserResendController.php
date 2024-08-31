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

use Evoweb\SfRegister\Controller\Event\ResendFormEvent;
use Evoweb\SfRegister\Controller\Event\ResendMailEvent;
use Evoweb\SfRegister\Domain\Model\Email;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Annotation as Extbase;

/**
 * An frontend user resend controller
 */
class FeuserResendController extends FeuserController
{
    public const PLUGIN_ACTIONS = 'form, mail';

    protected string $controller = 'Resend';

    public function formAction(Email $email = null): ResponseInterface
    {
        if ($email === null) {
            try {
                /** @var UserAspect $userAspect */
                $userAspect = $this->context->getAspect('frontend.user');
                $userId = $userAspect->get('id');

                /** @var FrontendUser $user */
                $user = $this->userRepository->findByUid($userId);

                $email = new Email();
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

        /** @var FrontendUser $user */
        $user = $this->userRepository->findByEmail($email->getEmail());

        if ($user instanceof FrontendUser) {
            $this->sendEmails($user, __FUNCTION__);
        }

        return new HtmlResponse($this->view->render());
    }
}
