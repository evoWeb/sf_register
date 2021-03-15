<?php
namespace Evoweb\SfRegister\Services;

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

use Evoweb\SfRegister\Interfaces\FrontendUserInterface;

/**
 * Service to handle mail sending
 */
class Mail implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager;

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

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;


    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function injectSignalSlotDispatcher(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher)
    {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
    }

    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
    ) {
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


    public function sendAdminNotification(FrontendUserInterface $user, string $type): FrontendUserInterface
    {
        $method = __FUNCTION__ . $type;
        if (method_exists($this, $method)) {
            $user = $this->{$method}($user);
        } else {
            $this->sendEmail(
                $user,
                $this->getAdminRecipient(),
                'adminEmail',
                $this->getSubject($method, $user),
                $this->renderBody('FeuserCreate', 'form', $method, $user, 'html'),
                $this->renderBody('FeuserCreate', 'form', $method, $user, 'txt')
            );

            $user = $this->dispatchSlotSignal($method . 'PostSend', $user);
        }

        return $user;
    }

    public function sendUserNotification(FrontendUserInterface $user, string $type): FrontendUserInterface
    {
        $method = __FUNCTION__ . $type;
        if (method_exists($this, $method)) {
            $user = $this->{$method}($user);
        } else {
            $this->sendEmail(
                $user,
                $this->getUserRecipient($user),
                'userEmail',
                $this->getSubject($method, $user),
                $this->renderBody('FeuserCreate', 'form', $method, $user, 'html'),
                $this->renderBody('FeuserCreate', 'form', $method, $user, 'txt')
            );

            $user = $this->dispatchSlotSignal($method . 'PostSend', $user);
        }

        return $user;
    }


    public function sendAdminNotificationPostCreateSave(FrontendUserInterface $user): FrontendUserInterface
    {
        $this->sendEmail(
            $user,
            $this->getAdminRecipient(),
            'adminEmail',
            $this->getSubject(__FUNCTION__, $user),
            $this->renderBody('FeuserCreate', 'form', __FUNCTION__, $user, 'html'),
            $this->renderBody('FeuserCreate', 'form', __FUNCTION__, $user, 'txt')
        );

        return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
    }

    public function sendUserNotificationPostCreateSave(FrontendUserInterface $user): FrontendUserInterface
    {
        $this->sendEmail(
            $user,
            $this->getUserRecipient($user),
            'userEmail',
            $this->getSubject(__FUNCTION__, $user),
            $this->renderBody('FeuserCreate', 'form', __FUNCTION__, $user, 'html'),
            $this->renderBody('FeuserCreate', 'form', __FUNCTION__, $user, 'txt')
        );

        return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
    }

    public function sendAdminNotificationPostCreateConfirm(FrontendUserInterface $user): FrontendUserInterface
    {
        $this->sendEmail(
            $user,
            $this->getAdminRecipient(),
            'adminEmail',
            $this->getSubject(__FUNCTION__, $user),
            $this->renderBody('FeuserCreate', 'form', __FUNCTION__, $user, 'html'),
            $this->renderBody('FeuserCreate', 'form', __FUNCTION__, $user, 'txt')
        );

        return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
    }


    public function sendAdminNotificationPostEditSave(FrontendUserInterface $user): FrontendUserInterface
    {
        $this->sendEmail(
            $user,
            $this->getAdminRecipient(),
            'adminEmail',
            $this->getSubject(__FUNCTION__, $user),
            $this->renderBody('FeuserEdit', 'form', __FUNCTION__, $user, 'html'),
            $this->renderBody('FeuserEdit', 'form', __FUNCTION__, $user, 'txt')
        );

        return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
    }

    public function sendUserNotificationPostEditSave(FrontendUserInterface $user): FrontendUserInterface
    {
        $this->sendEmail(
            $user,
            $this->getUserRecipient($user),
            'userEmail',
            $this->getSubject(__FUNCTION__, $user),
            $this->renderBody('FeuserEdit', 'form', __FUNCTION__, $user, 'html'),
            $this->renderBody('FeuserEdit', 'form', __FUNCTION__, $user, 'txt')
        );

        return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
    }

    public function sendAdminNotificationPostEditConfirm(FrontendUserInterface $user): FrontendUserInterface
    {
        $this->sendEmail(
            $user,
            $this->getAdminRecipient(),
            'adminEmail',
            $this->getSubject(__FUNCTION__, $user),
            $this->renderBody('FeuserEdit', 'form', __FUNCTION__, $user, 'html'),
            $this->renderBody('FeuserEdit', 'form', __FUNCTION__, $user, 'txt')
        );

        return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
    }


    public function sendInvitation(FrontendUserInterface $user, string $type): FrontendUserInterface
    {
        $method = __FUNCTION__ . $type;

        $this->sendEmail(
            $user,
            [$user->getInvitationEmail() => ''],
            'userEmail',
            $this->getSubject($method, $user),
            $this->renderBody('FeuserCreate', 'form', $method, $user, 'html'),
            $this->renderBody('FeuserCreate', 'form', $method, $user, 'txt')
        );

        $user = $this->dispatchSlotSignal($method . 'PostSend', $user);

        return $user;
    }


    protected function getSubject(string $method, FrontendUserInterface $user): string
    {

        $labelIndex = 'subject' . str_replace('send', '', $method);
        $subject    = null;

        if (!empty($this->settings['translation']['subjects'])) {
            $subject = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                $this->settings['translation']['subjects'] . ':' . $labelIndex,
                null,
                [$this->settings['sitename'], $user->getUsername()]
            );
        }

        return $subject ?? \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            $labelIndex,
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
        $mail = $this->objectManager->get(\TYPO3\CMS\Core\Mail\MailMessage::class);
        $mail->setTo($recipient)
            ->setFrom([$settings['fromEmail'] => $settings['fromName']])
            ->setSubject($subject);

        if ($settings['replyEmail']) {
            $mail->setReplyTo([$settings['replyEmail'] => $settings['replyName']]);
        }

        if ($bodyHtml !== '') {
            if (method_exists($mail, 'addPart')) {
                // @todo remove once TYPO3 9.5 support gets dropped
                $mail->addPart($bodyHtml, 'text/html');
            } else {
                $mail->html($bodyHtml);
            }
        }
        if ($bodyPlain !== '') {
            if (method_exists($mail, 'addPart')) {
                // @todo remove once TYPO3 9.5 support gets dropped
                $mail->addPart($bodyPlain, 'text/plain');
            } else {
                $mail->text($bodyPlain);
            }
        }

        $mail = $this->dispatchSlotSignal('sendMailPreSend', $mail, $user);

        return $mail->send();
    }

    protected function renderBody(
        string $controller,
        string $action,
        string $method,
        FrontendUserInterface $user,
        string $fileExtension = 'html'
    ): string {
        $variables = [
            'user' => $user,
            'settings' => $this->settings
        ];

        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);
        $view->setLayoutRootPaths($this->frameworkConfiguration['view']['layoutRootPaths']);
        $view->setPartialRootPaths($this->frameworkConfiguration['view']['partialRootPaths']);
        $view->setTemplateRootPaths($this->frameworkConfiguration['view']['templateRootPaths']);

        $request = $view->getRequest();
        $request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
        $request->setPluginName($this->frameworkConfiguration['pluginName']);
        $request->setControllerName($controller);
        $request->setControllerActionName($action);
        $request->setFormat($fileExtension);

        $context = $view->getRenderingContext();
        $context->setControllerName('Email');
        $context->setControllerAction(str_replace('send', '', $method));
        try {
            $view->assignMultiple($variables);

            $body = $view->render();
        } catch (\TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException $e) {
            $body = '';
        }

        return $body;
    }

    /**
     * Dispatch signal to registered slots
     *
     * @param string $signalName
     * @param \TYPO3\CMS\Core\Mail\MailMessage|FrontendUserInterface $result
     *
     * @return \TYPO3\CMS\Core\Mail\MailMessage|FrontendUserInterface
     */
    protected function dispatchSlotSignal($signalName, $result)
    {
        $arguments = array_merge(array_slice(func_get_args(), 2), [$this->settings, $this->objectManager]);

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            $signalName,
            ['result' => &$result, 'arguments' => $arguments]
        );

        return $result;
    }
}
