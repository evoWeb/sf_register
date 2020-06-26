<?php

namespace Evoweb\SfRegister\Services;

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

use Evoweb\SfRegister\Interfaces\FrontendUserInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to handle mail sending
 */
class Mail implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var \Psr\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $frameworkConfiguration = [];

    public function __construct(
        \Psr\EventDispatcher\EventDispatcherInterface $eventDispatcher,
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
        $this->frameworkConfiguration = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
    }


    public function sendNotifyAdmin(
        FrontendUserInterface $user,
        string $controller,
        string $action
    ): FrontendUserInterface {
        $method = 'NotifyAdmin' . $controller . $action;
        $controller = 'Feuser' . $controller;

        $this->sendEmail(
            $user,
            $this->getAdminRecipient(),
            'adminEmail',
            $this->getSubject($method, $user),
            $this->renderHtmlBody($controller, 'form', $method, $user),
            $this->renderTextBody($controller, 'form', $method, $user)
        );

        $user = $this->dispatchEvent($method, $user);

        return $user;
    }

    public function sendNotifyUser(
        FrontendUserInterface $user,
        string $controller,
        string $action
    ): FrontendUserInterface {
        $method = 'NotifyUser' . $controller . $action;
        $controller = 'Feuser' . $controller;

        $this->sendEmail(
            $user,
            $this->getUserRecipient($user),
            'userEmail',
            $this->getSubject($method, $user),
            $this->renderHtmlBody($controller, 'form', $method, $user),
            $this->renderTextBody($controller, 'form', $method, $user)
        );

        $user = $this->dispatchEvent($method, $user);

        return $user;
    }

    public function sendInvitation(FrontendUserInterface $user, string $type)
    {
        $method = __FUNCTION__ . $type;

        $this->sendEmail(
            $user,
            [$user->getInvitationEmail() => ''],
            'userEmail',
            $this->getSubject($method, $user),
            $this->renderHtmlBody('FeuserCreate', 'form', $method, $user),
            $this->renderTextBody('FeuserCreate', 'form', $method, $user)
        );

        $user = $this->dispatchEvent($method, $user);

        return $user;
    }


    protected function getSubject(string $method, FrontendUserInterface $user): string
    {
        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            'subject' . $method,
            'SfRegister',
            [$this->settings['sitename'], $user->getUsername()]
        );
    }

    protected function getAdminRecipient(): array
    {
        return [
            trim($this->settings['adminEmail']['toEmail']) => trim($this->settings['adminEmail']['toName'])
        ];
    }

    protected function getUserRecipient(FrontendUserInterface $user): array
    {
        if ($user->getFirstName() || $user->getLastName()) {
            $name = trim($user->getFirstName() . ' ' . $user->getLastName());
        } else {
            $name = trim($user->getUsername());
        }

        return [
            trim($user->getEmail()) => $name
        ];
    }


    protected function sendEmail(
        FrontendUserInterface $user,
        array $recipient,
        string $typeOfEmail,
        string $subject,
        string $bodyHtml,
        string $bodyPlain
    ): int {
        $settings =& $this->settings[$typeOfEmail];

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
        $mail = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailMessage::class);
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

        $mail = $this->dispatchEvent('PreSubmitMail', $mail, $user);

        return $mail->send();
    }

    protected function renderHtmlBody(
        string $controller,
        string $action,
        string $method,
        FrontendUserInterface $user
    ): string {
        $view = $this->getView($controller, $action);
        $view->setFormat('html');

        $context = $view->getRenderingContext();
        $context->setControllerName('Email');
        $context->setControllerAction($method);

        try {
            $view->assignMultiple([
                'user' => $user,
                'settings' => $this->settings
            ]);

            $body = $view->render();
        } catch (\TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException $e) {
            $body = '';
        }

        return $body;
    }

    protected function renderTextBody(
        string $controller,
        string $action,
        string $method,
        FrontendUserInterface $user
    ): string {
        $view = $this->getView($controller, $action);
        $view->setFormat('txt');

        $context = $view->getRenderingContext();
        $context->setControllerName('Email');
        $context->setControllerAction($method);

        try {
            $view->assignMultiple([
                'user' => $user,
                'settings' => $this->settings
            ]);

            $body = $view->render();
        } catch (\TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException $e) {
            $body = '';
        }

        return $body;
    }

    protected function getView(string $controller, string $action): \TYPO3\CMS\Fluid\View\StandaloneView
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = GeneralUtility::getContainer()->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setLayoutRootPaths($this->frameworkConfiguration['view']['layoutRootPaths']);
        $view->setPartialRootPaths($this->frameworkConfiguration['view']['partialRootPaths']);
        $view->setTemplateRootPaths($this->frameworkConfiguration['view']['templateRootPaths']);

        $request = $view->getRequest();
        $request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
        $request->setPluginName($this->frameworkConfiguration['pluginName']);
        $request->setControllerName($controller);
        $request->setControllerActionName($action);

        return $view;
    }

    /**
     * Dispatch event to listeners
     *
     * @param string $method
     * @param \TYPO3\CMS\Core\Mail\MailMessage|FrontendUserInterface $result
     *
     * @return \TYPO3\CMS\Core\Mail\MailMessage|FrontendUserInterface
     */
    protected function dispatchEvent($method, $result)
    {
        $event = 'Evoweb\\SfRegister\\Services\\Event\\' . $method . 'Event';
        $arguments = array_slice(func_get_args(), 2);

        $eventObject = new $event($result, $this->settings, $arguments);
        $this->eventDispatcher->dispatch($eventObject);
        return $eventObject->getResult();
    }
}
