<?php
namespace Evoweb\SfRegister\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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
    protected $ignoredActions = ['confirmAction'];

    public function formAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null)
    {
        $userId = $this->context->getAspect('frontend.user')->get('id');

        $originalRequest = $this->request->getOriginalRequest();
        if ((
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
                $propertyMapper = $this->objectManager->get(\TYPO3\CMS\Extbase\Property\PropertyMapper::class);
                $user = $propertyMapper->convert($userData, \Evoweb\SfRegister\Domain\Model\FrontendUser::class);
            }
        }

        if ($user == null) {
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
        }

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'user' => &$user,
                'settings' => $this->settings
            ]
        );

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
        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            __FUNCTION__,
            [
                'user' => &$user,
                'settings' => $this->settings
            ]
        );

        if (!$user->getUsername()) {
            $user->setUsername($user->getEmail());
        }
        if (!$user->getUid()) {
            $user = $this->userRepository->findByEmail($user->getEmail());
        }

        $user = $this->sendEmails($user, 'PostDeleteSave');

        $this->view->assign('user', $user);
    }

    /**
     * Confirm delete process by user
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $hash
     */
    public function confirmAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, string $hash = null)
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof \Evoweb\SfRegister\Domain\Model\FrontendUser)) {
            $this->view->assign('userAlreadyDeleted', 1);
        } else {
            $this->view->assign('user', $user);

            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__,
                [
                    'user' => &$user,
                    'settings' => $this->settings
                ]
            );

            $this->sendEmails($user, 'PostDeleteConfirm');

            $this->userRepository->remove($user);
            $this->persistAll();

            $this->view->assign('userDeleted', 1);
        }
    }
}
