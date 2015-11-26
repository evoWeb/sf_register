<?php
namespace Evoweb\SfRegister\Services;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Service to handle mail sending
 */
class Mail implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * Configuration manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * Settings of the create controller
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Framework configurations
     *
     * @var array
     */
    protected $frameworkConfiguration = [];

    /**
     * Signal slot dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;


    /**
     * Inject configuration manager
     *
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
        $this->frameworkConfiguration = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
    }


    /**
     * Send an email notification for type to the admin
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @param string $type
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendAdminNotification(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user, $type)
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

    /**
     * Send an email notification for type to the user
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @param string $type
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendUserNotification(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user, $type)
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


    /**
     * Send an email notification pre confirmation to the admin
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendAdminNotificationPostCreateSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
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

    /**
     * Send an email notification pre confirmation to the user
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendUserNotificationPostCreateSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
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

    /**
     * Send an email notification post confirmation to the admin
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendAdminNotificationPostCreateConfirm(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
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


    /**
     * Send an email notification post edit to the admin
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendAdminNotificationPostEditSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
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

    /**
     * Send an email notification post edit to the user
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendUserNotificationPostEditSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
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

    /**
     * Send an email notification post confirmation to the admin
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function sendAdminNotificationPostEditConfirm(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
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


    /**
     * Get translated version of the subject with replaced username and sitename
     *
     * @param string $method
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return string
     */
    protected function getSubject($method, \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
    {
        $labelIndex = 'subject' . str_replace('send', '', $method);

        return \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
            $labelIndex,
            'SfRegister',
            array($this->settings['sitename'], $user->getUsername())
        );
    }

    /**
     * Get the mailhash for the activation link based on time,
     * username and email address
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return string
     */
    protected function getMailHash(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
    {
        return md5(
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] . $user->getUsername() .
            $GLOBALS['EXEC_TIME'] . $user->getEmail()
        );
    }

    /**
     * Get admin recipient
     *
     * @return string
     */
    protected function getAdminRecipient()
    {
        return array(
            trim($this->settings['adminEmail']['toEmail']) => trim($this->settings['adminEmail']['toName'])
        );
    }

    /**
     * Get user recipient
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @return string
     */
    protected function getUserRecipient(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user)
    {
        if ($user->getFirstName() || $user->getLastName()) {
            $name = trim($user->getFirstName() . ' ' . $user->getLastName());
        } else {
            $name = trim($user->getUsername());
        }

        return array(
            trim($user->getEmail()) => $name
        );
    }


    /**
     * Send email
     *
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @param array $recipient
     * @param string $typeOfEmail
     * @param string $subject
     * @param string $bodyHtml
     * @param string $bodyPlain
     * @return integer the number of recipients who were accepted for delivery
     */
    protected function sendEmail($user, array $recipient, $typeOfEmail, $subject, $bodyHtml, $bodyPlain)
    {
        /** @var $mail \TYPO3\CMS\Core\Mail\MailMessage */
        $mail = $this->objectManager->get(\TYPO3\CMS\Core\Mail\MailMessage::class);
        $mail->setTo($recipient)
            ->setFrom(array($this->settings[$typeOfEmail]['fromEmail'] => $this->settings[$typeOfEmail]['fromName']))
            ->setSubject($subject);

        if ($this->settings[$typeOfEmail]['replyEmail']) {
            $mail->setReplyTo(
                array($this->settings[$typeOfEmail]['replyEmail'] => $this->settings[$typeOfEmail]['replyName'])
            );
        }

        if ($bodyHtml !== '') {
            $mail->addPart($bodyHtml, 'text/html');
        }
        if ($bodyPlain !== '') {
            $mail->addPart($bodyPlain, 'text/plain');
        }

        $mail = $this->dispatchSlotSignal('sendMailPreSend', $mail, $user);

        return $mail->send();
    }


    /**
     * renders the given Template file via fluid rendering engine.
     *
     * @param string $controller
     * @param string $action
     * @param string $method method calling this function
     * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
     * @param string $fileExtension
     * @return string of the rendered View.
     */
    protected function renderBody($controller, $action, $method, $user, $fileExtension = 'html')
    {
        $templateName = 'Email/' . str_replace('send', '', $method);
        $variables = array('user' => $user, 'settings' => $this->settings);

        /** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
        $view = $this->objectManager->get(\TYPO3\CMS\Fluid\View\StandaloneView::class);

        $request = $view->getRequest();
        $request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
        $request->setPluginName($this->frameworkConfiguration['pluginName']);
        $request->setControllerName($controller);
        $request->setControllerActionName($action);
        $request->setFormat($fileExtension);

        $view->setLayoutRootPaths($this->getAbsoluteLayoutRootPath());
        $view->setTemplateRootPaths($this->getAbsoluteTemplateRootPaths());
        try {
            $view->setTemplate($templateName);
            $view->assignMultiple($variables);

            $body = $view->render();
        } catch (\TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException $e) {
            $body = '';
        }

        return $body;
    }

    /**
     * Get absolute template root paths
     *
     * @return array
     */
    protected function getAbsoluteTemplateRootPaths()
    {
        $templateRootPaths = [];
        if ($this->settings['templateRootPath']) {
            $templateRootPaths[] = trim($this->settings['templateRootPath']);
        }

        if (isset($this->frameworkConfiguration['view'])) {
            if (isset($this->frameworkConfiguration['view']['templateRootPath'])) {
                $templateRootPaths[] = $this->frameworkConfiguration['view']['templateRootPath'];
            }

            if (isset($this->frameworkConfiguration['view']['templateRootPaths'])) {
                $templateRootPaths = array_merge(
                    $templateRootPaths,
                    $this->frameworkConfiguration['view']['templateRootPaths']
                );
            }
        }

        if (empty($templateRootPaths)) {
            $templateRootPaths[] = ExtensionManagementUtility::extPath('sf_register') . 'Resources/Private/Templates/';
        }

        $result = [];
        foreach ($templateRootPaths as $key => $value) {
            $value = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(trim($value));
            if (\TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath($value)) {
                $result[] = rtrim(trim($value), '/') . '/';
            }
        }

        return $result;
    }

    /**
     * Get absolute layout root path
     *
     * @return string
     */
    protected function getAbsoluteLayoutRootPath()
    {
        $layoutRootPaths = [];
        if ($this->settings['layoutRootPath']) {
            $layoutRootPaths = trim($this->settings['layoutRootPath']);
        }

        if (isset($this->frameworkConfiguration['view'])) {
            if (isset($this->frameworkConfiguration['view']['layoutRootPath'])) {
                $layoutRootPaths[] = $this->frameworkConfiguration['view']['layoutRootPath'];
            }

            if (isset($this->frameworkConfiguration['view']['layoutRootPaths'])) {
                $layoutRootPaths = array_merge(
                    $layoutRootPaths,
                    $this->frameworkConfiguration['view']['layoutRootPaths']
                );
            }
        }

        if (empty($layoutRootPaths)) {
            $layoutRootPaths[] = ExtensionManagementUtility::extPath('sf_register') . 'Resources/Private/Layouts/';
        }

        $result = [];
        foreach ($layoutRootPaths as $key => $value) {
            $value = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(trim($value));
            if (\TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath($value)) {
                $result[] = rtrim(trim($value), '/') . '/';
            }
        }

        return $result;
    }


    /**
     * Dispatch signal to registered slots
     *
     * @param string $signalName
     * @param object $result
     * @return mixed
     */
    protected function dispatchSlotSignal($signalName, $result)
    {
        $arguments = array_merge(array_slice(func_get_args(), 2), array($this->settings, $this->objectManager));

        $this->signalSlotDispatcher->dispatch(
            __CLASS__,
            $signalName,
            array('result' => &$result, 'arguments' => $arguments)
        );

        return $result;
    }
}
