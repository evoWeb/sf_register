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

namespace Evoweb\SfRegister\Services;

use Evoweb\SfRegister\Domain\Model\FrontendUserInterface;
use Evoweb\SfRegister\Services\Event\AbstractEventWithUser;
use Evoweb\SfRegister\Services\Event\PreSubmitMailEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException;

/**
 * Service to handle mail sending
 */
class Mail implements SingletonInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $frameworkConfiguration = [];

    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected ConfigurationManagerInterface $configurationManager
    ) {
        $this->frameworkConfiguration = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function sendEmails(
        RequestInterface $request,
        array $settings,
        FrontendUserInterface $user,
        string $controllerName,
        string $action
    ): FrontendUserInterface {
        $action = ucfirst(str_replace('Action', '', $action));
        $type = $controllerName . $action;

        if ($this->isNotifyAdmin($settings, $type)) {
            $user = $this->sendNotifyAdmin($request, $settings, $user, $controllerName, $action);
        }

        if ($this->isNotifyUser($settings, $type)) {
            $user = $this->sendNotifyUser($request, $settings, $user, $controllerName, $action);
        }

        return $user;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function isNotifyAdmin(array $settings, string $type): bool
    {
        $type = lcfirst($type);
        $notifySettings = $settings['notifyAdmin'] ?? [];
        return !empty($notifySettings[$type]);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function isNotifyUser(array $settings, string $type): bool
    {
        $type = lcfirst($type);
        $notifySettings = $settings['notifyUser'] ?? [];
        return !empty($notifySettings[$type]);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function sendNotifyAdmin(
        RequestInterface $request,
        array $settings,
        FrontendUserInterface $user,
        string $controller,
        string $action
    ): FrontendUserInterface {
        $method = 'NotifyAdmin' . $controller . $action;
        $controller = 'Feuser' . $controller;

        $this->sendEmail(
            $settings,
            $user,
            $this->getAdminRecipient($settings),
            'adminEmail',
            $this->getSubject($settings, $method, $user),
            $this->renderHtmlBody($request, $settings, $controller, 'form', $method, $user),
            $this->renderTextBody($request, $settings, $controller, 'form', $method, $user)
        );

        return $this->dispatchUserEvent($settings, $method, $user);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function sendNotifyUser(
        RequestInterface $request,
        array $settings,
        FrontendUserInterface $user,
        string $controller,
        string $action
    ): FrontendUserInterface {
        $method = 'NotifyUser' . $controller . $action;
        $controller = 'Feuser' . $controller;

        $this->sendEmail(
            $settings,
            $user,
            $this->getUserRecipient($user),
            'userEmail',
            $this->getSubject($settings, $method, $user),
            $this->renderHtmlBody($request, $settings, $controller, 'form', $method, $user),
            $this->renderTextBody($request, $settings, $controller, 'form', $method, $user)
        );

        return $this->dispatchUserEvent($settings, $method, $user);
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function sendInvitation(
        RequestInterface $request,
        array $settings,
        FrontendUserInterface $user,
        string $controller,
        string $action
    ): FrontendUserInterface {
        $method = $controller . $action;

        $this->sendEmail(
            $settings,
            $user,
            [$user->getInvitationEmail() => ''],
            'userEmail',
            $this->getSubject($settings, $method, $user),
            $this->renderHtmlBody($request, $settings, 'FeuserCreate', 'form', $method, $user),
            $this->renderTextBody($request, $settings, 'FeuserCreate', 'form', $method, $user)
        );

        return $this->dispatchUserEvent($settings, $method, $user);
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function getSubject(array $settings, string $method, FrontendUserInterface $user): string
    {
        return (string)LocalizationUtility::translate(
            'subject' . $method,
            'SfRegister',
            [$settings['sitename'] ?? '', $user->getUsername()]
        );
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, string>
     */
    protected function getAdminRecipient(array $settings): array
    {
        return [
            trim($settings['adminEmail']['toEmail'] ?? '') => trim($settings['adminEmail']['toName'] ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getUserRecipient(FrontendUserInterface $user): array
    {
        if ($user->getFirstName() || $user->getLastName()) {
            $name = trim($user->getFirstName() . ' ' . $user->getLastName());
        } else {
            $name = trim($user->getUsername());
        }

        return [
            trim($user->getEmail()) => $name,
        ];
    }

    /**
     * @param array<string, mixed> $settings
     * @param array<string, string> $recipient
     */
    protected function sendEmail(
        array $settings,
        FrontendUserInterface $user,
        array $recipient,
        string $typeOfEmail,
        string $subject,
        string $bodyHtml,
        string $bodyPlain
    ): bool {
        $settings = & $settings[$typeOfEmail];

        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail->setTo($recipient)
            ->setFrom([$settings['fromEmail'] => $settings['fromName']])
            ->setSubject($subject);

        if ($settings['replyEmail']) {
            $mail->setReplyTo([$settings['replyEmail'] => $settings['replyName']]);
        }

        if ($bodyHtml !== '') {
            $mail->html($bodyHtml);
        }
        if ($bodyPlain !== '') {
            $mail->text($bodyPlain);
        }

        $mail = $this->dispatchMailEvent($settings, $mail, $user);

        return $mail->send();
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function renderHtmlBody(
        RequestInterface $request,
        array $settings,
        string $controller,
        string $action,
        string $method,
        FrontendUserInterface $user
    ): string {
        $view = $this->getView($request, $controller, $action);
        $view->setFormat('html');

        $context = $view->getRenderingContext();
        $context->setControllerName('Email');
        $context->setControllerAction($method);

        try {
            $view->assignMultiple([
                'user' => $user,
                'settings' => $settings,
            ]);

            $body = $view->render();
        } catch (InvalidTemplateResourceException) {
            $body = '';
        }

        return $body;
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function renderTextBody(
        RequestInterface $request,
        array $settings,
        string $controller,
        string $action,
        string $method,
        FrontendUserInterface $user
    ): string {
        $view = $this->getView($request, $controller, $action);
        $view->setFormat('txt');

        $context = $view->getRenderingContext();
        $context->setControllerName('Email');
        $context->setControllerAction($method);

        try {
            $view->assignMultiple([
                'user' => $user,
                'settings' => $settings,
            ]);

            $body = $view->render();
        } catch (InvalidTemplateResourceException) {
            $body = '';
        }

        return $body;
    }

    protected function getView(
        RequestInterface $request,
        string $controller,
        string $action
    ): StandaloneView {
        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setLayoutRootPaths($this->frameworkConfiguration['view']['layoutRootPaths']);
        $view->setPartialRootPaths($this->frameworkConfiguration['view']['partialRootPaths']);
        $view->setTemplateRootPaths($this->frameworkConfiguration['view']['templateRootPaths']);

        $request = $request->withControllerExtensionName($this->frameworkConfiguration['extensionName']);
        $request = $request->withPluginName($this->frameworkConfiguration['pluginName']);
        $request = $request->withControllerName($controller);
        $request = $request->withControllerActionName($action);
        $view->setRequest($request);

        return $view;
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function dispatchMailEvent(
        array $settings,
        MailMessage $mail,
        FrontendUserInterface $user
    ): MailMessage {
        $eventObject = new PreSubmitMailEvent($mail, $settings, ['user' => $user]);
        return $this->eventDispatcher->dispatch($eventObject)->getMail();
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function dispatchUserEvent(
        array $settings,
        string $method,
        FrontendUserInterface $user
    ): FrontendUserInterface {
        $event = 'Evoweb\\SfRegister\\Services\\Event\\' . $method . 'Event';
        /** @var AbstractEventWithUser $eventObject */
        $eventObject = new $event($user, $settings);
        return $this->eventDispatcher->dispatch($eventObject)->getUser();
    }
}
