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

/**
 * An frontend user resend controller
 */
class FeuserResendController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'resend';

    public function formAction(\Evoweb\SfRegister\Domain\Model\Email $email = null)
    {
        $email = $this->eventDispatcher->dispatch(new Event\ResendFormEvent($email, $this->settings))->getEmail();

        $userId = $this->context->getAspect('frontend.user')->get('id');
        $email = $email ?? $this->userRepository->findByUid($userId);

        if ($email) {
            $this->view->assign('email', ['email' => $email->getEmail()]);
        }
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\Email $email
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="email")
     */
    public function mailAction(\Evoweb\SfRegister\Domain\Model\Email $email)
    {
        $email = $this->eventDispatcher->dispatch(new Event\ResendMailEvent($email, $this->settings))->getEmail();

        /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
        $user = $this->userRepository->findByEmail($email->getEmail());

        if ($user) {
            $this->sendEmails($user, __FUNCTION__);
        }
    }
}
