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
 * An frontend user create controller
 */
class FeuserDeleteController extends FeuserController
{
    /**
     * @var string
     */
    protected $controller = 'delete';

    /**
     * @var array
     */
    protected $ignoredActions = ['confirmAction', 'requestAction'];

    public function formAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null)
    {
        $userId = $this->context->getAspect('frontend.user')->get('id');

        $originalRequest = $this->request->getOriginalRequest();
        if (
            (
                $this->request->hasArgument('user')
                || ($originalRequest !== null && $originalRequest->hasArgument('user'))
            )
            && $this->userIsLoggedIn()
        ) {
            /** @var array $userData */
            $userData = $this->request->hasArgument('user') ?
                $this->request->getArgument('user') :
                $originalRequest->getArgument('user');

            // only reconstitute user object if given user uid equals logged in user uid
            if ($userData['uid'] == $userId) {
                /** @var \TYPO3\CMS\Extbase\Property\PropertyMapper $propertyMapper */
                $propertyMapper = GeneralUtility::getContainer()
                    ->get(\TYPO3\CMS\Extbase\Property\PropertyMapper::class);
                $user = $propertyMapper->convert($userData, \Evoweb\SfRegister\Domain\Model\FrontendUser::class);
            }
        }

        if ($user == null) {
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
        }

        $this->eventDispatcher->dispatch(new Event\DeleteFormEvent($user, $this->settings));

        $this->view->assign('user', $user);
    }

    /**
     * Save action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function saveAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        $this->eventDispatcher->dispatch(new Event\DeleteSaveEvent($user, $this->settings));

        if (!$user->getUsername()) {
            $user->setUsername($user->getEmail());
        }
        if (!$user->getUid()) {
            $user = $this->userRepository->findByEmail($user->getEmail());
        }

        $user = $this->sendEmails($user, __FUNCTION__);

        $this->view->assign('user', $user);
    }

    /**
     * Confirm delete process by user
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser|null $user
     * @param string|null $hash
     */
    public function confirmAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userAlreadyDeleted', 1);
        } else {
            $this->view->assign('user', $user);

            $this->eventDispatcher->dispatch(new Event\DeleteConfirmEvent($user, $this->settings));

            $this->sendEmails($user, __FUNCTION__);

            $this->userRepository->remove($user);
            $this->persistAll();

            $this->view->assign('userDeleted', 1);
        }
    }

    public function requestAction(string $email = null)
    {
        $this->view->assign('user', ['email' => $email]);
    }

    /**
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $requestUser
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="requestUser")
     */
    public function sendLinkAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $requestUser)
    {
        /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
        $user = $this->userRepository->findByEmail($requestUser->getEmail());

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userUnknown', 1);
        } else {
            $this->view->assign('user', $user);
            $this->view->assign('requestUser', $requestUser);

            $this->sendEmails($user, __FUNCTION__);
        }
    }
}
