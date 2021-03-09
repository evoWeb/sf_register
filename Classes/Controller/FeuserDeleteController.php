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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use Evoweb\SfRegister\Controller\Event\DeleteFormEvent;
use Evoweb\SfRegister\Controller\Event\DeleteSaveEvent;
use Evoweb\SfRegister\Controller\Event\DeleteConfirmEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An frontend user create controller
 */
class FeuserDeleteController extends FeuserController
{
    protected string $controller = 'delete';

    protected array $ignoredActions = ['confirmAction', 'requestAction'];

    public function formAction(FrontendUser $user = null): ResponseInterface
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
                    ->get(PropertyMapper::class);
                $user = $propertyMapper->convert($userData, FrontendUser::class);
            }
        }

        if ($user == null) {
            /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
            $user = $this->userRepository->findByUid($userId);
        }

        $this->eventDispatcher->dispatch(new DeleteFormEvent($user, $this->settings));

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Save action
     *
     * @param FrontendUser $user
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="user")
     */
    public function saveAction(FrontendUser $user): ResponseInterface
    {
        $this->eventDispatcher->dispatch(new DeleteSaveEvent($user, $this->settings));

        if (!$user->getUsername()) {
            $user->setUsername($user->getEmail());
        }
        if (!$user->getUid()) {
            $user = $this->userRepository->findByEmail($user->getEmail());
        }

        $user = $this->sendEmails($user, __FUNCTION__);

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Confirm delete process by user
     *
     * @param ?FrontendUser $user
     * @param ?string $hash
     *
     * @return ResponseInterface
     */
    public function confirmAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->determineFrontendUser($user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userAlreadyDeleted', 1);
        } else {
            $this->view->assign('user', $user);

            $this->eventDispatcher->dispatch(new DeleteConfirmEvent($user, $this->settings));

            $this->sendEmails($user, __FUNCTION__);

            $this->userRepository->remove($user);
            $this->persistAll();

            $this->view->assign('userDeleted', 1);
        }

        return new HtmlResponse($this->view->render());
    }

    public function requestAction(?string $email): ResponseInterface
    {
        $this->view->assign('user', ['email' => $email]);

        return new HtmlResponse($this->view->render());
    }

    /**
     * @param FrontendUser $requestUser
     *
     * @return ResponseInterface
     *
     * @TYPO3\CMS\Extbase\Annotation\Validate("Evoweb\SfRegister\Validation\Validator\UserValidator", param="requestUser")
     */
    public function sendLinkAction(FrontendUser $requestUser): ResponseInterface
    {
        /** @var FrontendUser $user */
        $user = $this->userRepository->findByEmail($requestUser->getEmail());

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userUnknown', 1);
        } else {
            $this->view->assign('user', $user);
            $this->view->assign('requestUser', $requestUser);

            $this->sendEmails($user, __FUNCTION__);
        }

        return new HtmlResponse($this->view->render());
    }
}
