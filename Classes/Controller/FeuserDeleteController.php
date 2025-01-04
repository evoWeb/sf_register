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

use Evoweb\SfRegister\Controller\Event\DeleteConfirmEvent;
use Evoweb\SfRegister\Controller\Event\DeleteFormEvent;
use Evoweb\SfRegister\Controller\Event\DeleteSaveEvent;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * A frontend user create controller
 */
class FeuserDeleteController extends FeuserController
{
    public const DELETE_PLUGIN_ACTIONS = 'form, save, confirm';
    public const REQUEST_PLUGIN_ACTIONS = 'request, sendLink';

    /**
     * @var string[]
     */
    protected array $ignoredActions = ['confirmAction', 'requestAction'];

    public function __construct(
        protected ModifyValidator $modifyValidator,
        protected FileService $fileService,
        protected FrontendUserRepository $userRepository,
        protected MailService $mailService,
        protected FrontendUserService $frontendUserService,
    ) {
        parent::__construct($modifyValidator, $fileService, $userRepository);
    }

    public function formAction(?FrontendUser $user = null): ResponseInterface
    {
        if ($user === null) {
            $user = $this->frontendUserService->getLoggedInRequestUser($this->request);
        }

        if ($user instanceof FrontendUser) {
            $user = $this->eventDispatcher->dispatch(new DeleteFormEvent($user, $this->settings))->getUser();
        }

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'user'])]
    public function saveAction(?FrontendUser $user = null): ResponseInterface
    {
        if ($user === null) {
            return $this->redirect('form');
        }
        $user = $this->eventDispatcher->dispatch(new DeleteSaveEvent($user, $this->settings))->getUser();

        if (!$user->getUsername()) {
            $user->setUsername($user->getEmail());
        }
        if (!$user->getUid()) {
            $user = $this->userRepository->findByEmail($user->getEmail());
        }

        if ($user instanceof FrontendUser && $user->getUid()) {
            $user = $this->mailService->sendEmails(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                __FUNCTION__
            );
        } else {
            $this->view->assign('userNotFound', true);
        }

        $this->view->assign('user', $user);

        return new HtmlResponse($this->view->render());
    }

    /**
     * Confirm delete process by user
     *
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function confirmAction(?FrontendUser $user, ?string $hash): ResponseInterface
    {
        $user = $this->frontendUserService->determineFrontendUser($this->request, $user, $hash);

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userAlreadyDeleted', 1);
        } else {
            $user = $this->eventDispatcher->dispatch(new DeleteConfirmEvent($user, $this->settings))->getUser();
            $this->view->assign('user', $user);

            $this->mailService->sendEmails(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                __FUNCTION__
            );

            if ($user->getImage()->count()) {
                $image = $user->getImage()->current();
                $this->fileService->removeFile($image);
                $this->removeImageFromUserAndRequest($user);
            }

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

    #[Extbase\Validate(['validator' => UserValidator::class, 'param' => 'requestUser'])]
    public function sendLinkAction(FrontendUser $requestUser): ResponseInterface
    {
        /** @var FrontendUser $user */
        $user = $this->userRepository->findByEmail($requestUser->getEmail());

        if (!($user instanceof FrontendUser)) {
            $this->view->assign('userUnknown', 1);
        } else {
            $this->view->assign('user', $user);
            $this->view->assign('requestUser', $requestUser);

            $this->mailService->sendEmails(
                $this->request,
                $this->settings,
                $user,
                $this->getControllerName(),
                __FUNCTION__
            );
        }

        return new HtmlResponse($this->view->render());
    }
}
