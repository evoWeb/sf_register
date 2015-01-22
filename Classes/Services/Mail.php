<?php
namespace Evoweb\SfRegister\Services;
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
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
 * Service to handle mail sending
 */
class Mail implements \TYPO3\CMS\Core\SingletonInterface {
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
	protected $settings = array();

	/**
	 * Framework configurations
	 *
	 * @var array
	 */
	protected $frameworkConfiguration = array();

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
	public function sendAdminNotification(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user, $type) {
		if (method_exists($this, 'sendAdminNotification' . $type)) {
			$user = $this->{'sendAdminNotification' . $type}($user);
		} else {
			$this->sendEmail(
				$user,
				$this->getAdminRecipient(),
				'adminEmail',
				$this->getSubject(__FUNCTION__ . $type, $user),
				$this->renderFileTemplate('FeuserCreate', 'form', __FUNCTION__ . $type, $user)
			);

			$user = $this->dispatchSlotSignal(__FUNCTION__ . $type . 'PostSend', $user);
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
	public function sendUserNotification(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user, $type) {
		if (method_exists($this, 'sendUserNotification' . $type)) {
			$user = $this->{'sendUserNotification' . $type}($user);
		} else {
			$this->sendEmail(
				$user,
				$this->getUserRecipient($user),
				'userEmail',
				$this->getSubject(__FUNCTION__ . $type, $user),
				$this->renderFileTemplate('FeuserCreate', 'form', __FUNCTION__ . $type, $user)
			);

			$user = $this->dispatchSlotSignal(__FUNCTION__ . $type . 'PostSend', $user);
		}

		return $user;
	}


	/**
	 * Send an email notification pre confirmation to the admin
	 *
	 * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
	 * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
	 */
	public function sendAdminNotificationPostCreateSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
		$this->sendEmail(
			$user,
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject(__FUNCTION__, $user),
			$this->renderFileTemplate('FeuserCreate', 'form', __FUNCTION__, $user)
		);

		return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
	}

	/**
	 * Send an email notification pre confirmation to the user
	 *
	 * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
	 * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
	 */
	public function sendUserNotificationPostCreateSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
		$this->sendEmail(
			$user,
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject(__FUNCTION__, $user),
			$this->renderFileTemplate('FeuserCreate', 'form', __FUNCTION__, $user)
		);

		return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
	}

	/**
	 * Send an email notification post confirmation to the admin
	 *
	 * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
	 * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
	 */
	public function sendAdminNotificationPostCreateConfirm(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
		$this->sendEmail(
			$user,
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject(__FUNCTION__, $user),
			$this->renderFileTemplate('FeuserCreate', 'form', __FUNCTION__, $user)
		);

		return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
	}


	/**
	 * Send an email notification post edit to the admin
	 *
	 * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
	 * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
	 */
	public function sendAdminNotificationPostEditSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
		$this->sendEmail(
			$user,
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject(__FUNCTION__, $user),
			$this->renderFileTemplate('FeuserEdit', 'form', __FUNCTION__, $user)
		);

		return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
	}

	/**
	 * Send an email notification post edit to the user
	 *
	 * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
	 * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
	 */
	public function sendUserNotificationPostEditSave(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
		$this->sendEmail(
			$user,
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject(__FUNCTION__, $user),
			$this->renderFileTemplate('FeuserEdit', 'form', __FUNCTION__, $user)
		);

		return $this->dispatchSlotSignal(__FUNCTION__ . 'PostSend', $user);
	}

	/**
	 * Send an email notification post confirmation to the admin
	 *
	 * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
	 * @return \Evoweb\SfRegister\Interfaces\FrontendUserInterface
	 */
	public function sendAdminNotificationPostEditConfirm(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
		$this->sendEmail(
			$user,
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject(__FUNCTION__, $user),
			$this->renderFileTemplate('FeuserCreate', 'form', __FUNCTION__, $user)
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
	protected function getSubject($method, \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
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
	protected function getMailHash(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
		return md5($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] .
			$user->getUsername() . $GLOBALS['EXEC_TIME'] . $user->getEmail());
	}

	/**
	 * Get admin recipient
	 *
	 * @return string
	 */
	protected function getAdminRecipient() {
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
	protected function getUserRecipient(\Evoweb\SfRegister\Interfaces\FrontendUserInterface $user) {
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
	protected function sendEmail($user, array $recipient, $typeOfEmail, $subject, $bodyHtml, $bodyPlain = '') {
		/** @var $mail \TYPO3\CMS\Core\Mail\MailMessage */
		$mail = $this->objectManager->get('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$mail
			->setTo($recipient)
			->setFrom(array($this->settings[$typeOfEmail]['fromEmail'] => $this->settings[$typeOfEmail]['fromName']))
			->setSubject($subject);

		if ($this->settings[$typeOfEmail]['replyEmail']) {
			$mail->setReplyTo(array($this->settings[$typeOfEmail]['replyEmail'] => $this->settings[$typeOfEmail]['replyName']));
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
	 * Get template path and filename
	 *
	 * @param string $templateName
	 * @return string
	 */
	protected function getTemplatePathAndFilename($templateName) {
		return $this->getAbsoluteTemplateRootPath() . 'Email/' . $templateName . '.html';
	}

	/**
	 * Get absolute template root path
	 *
	 * @return string
	 */
	protected function getAbsoluteTemplateRootPath() {
		$result = '';
		$templateRootPath = trim($this->settings['templateRootPath']);

		if ($templateRootPath === '') {
			$templateRootPath = trim($this->frameworkConfiguration['view']['templateRootPath']);
		}

		if ($templateRootPath === '') {
			$templateRootPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sf_register') .
				'Resources/Private/Templates/';
		}

		$templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($templateRootPath);
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath($templateRootPath)) {
			$result = $templateRootPath . (substr($templateRootPath, -1) !== '/' ? '/' : '');
		}

		return $result;
	}

	/**
	 * Get absolute layout root path
	 *
	 * @return string
	 */
	protected function getAbsoluteLayoutRootPath() {
		$result = '';
		$layoutRootPath = trim($this->settings['layoutRootPath']);

		if ($layoutRootPath === '') {
			$layoutRootPath = trim($this->frameworkConfiguration['view']['layoutRootPath']);
		}

		if ($layoutRootPath === '') {
			$layoutRootPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sf_register') . 'Resources/Private/Layouts/';
		}

		$layoutRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($layoutRootPath);
		if (\TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath($layoutRootPath)) {
			$result = rtrim($layoutRootPath, '/') . '/';
		}

		return $result;
	}

	/**
	 * renders the given Template file via fluid rendering engine.
	 *
	 * @param string $controller
	 * @param string $action
	 * @param string $method method calling this function
	 * @param \Evoweb\SfRegister\Interfaces\FrontendUserInterface $user
	 * @return string of the rendered View.
	 */
	protected function renderFileTemplate($controller, $action, $method, $user) {
		$type = str_replace('send', '', $method);
		$variables = array('user' => $user, 'settings' => $this->settings);

		/** @var $view \TYPO3\CMS\Fluid\View\StandaloneView */
		$view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
		$view->setTemplatePathAndFilename($this->getTemplatePathAndFilename($type));
		$view->setLayoutRootPaths(array($this->getAbsoluteLayoutRootPath()));
		$view->assignMultiple($variables);

		$request = $view->getRequest();
		$request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
		$request->setPluginName($this->frameworkConfiguration['pluginName']);
		$request->setControllerName($controller);
		$request->setControllerActionName($action);

		return $view->render();
	}

	/**
	 * Dispatch signal to registered slots
	 *
	 * @param string $signalName
	 * @param object $result
	 * @return mixed
	 */
	protected function dispatchSlotSignal($signalName, $result) {
		$arguments = array_merge(
			array_slice(func_get_args(), 2),
			array($this->settings, $this->objectManager)
		);

		$this->signalSlotDispatcher->dispatch(
			__CLASS__,
			$signalName,
			array(
				'result' => &$result,
				'arguments' => $arguments,
			)
		);

		return $result;
	}
}