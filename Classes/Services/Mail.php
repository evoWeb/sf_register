<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Service to handle mail sending
 */
class Tx_SfRegister_Services_Mail implements t3lib_Singleton {
	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var array
	 */
	protected $frameworkConfiguration = array();

	/**
	 * Inject object manager
	 *
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return Tx_SfRegister_Services_Mail
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;

		return $this;
	}

	/**
	 * Inject configuration manager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return Tx_SfRegister_Services_Mail
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		$this->frameworkConfiguration = $configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);

		return $this;
	}


	/**
	 * Send an email notification pre activation to the admin
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendAdminNotificationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('AdminNotificationMail');
		$message = $this->renderFileTemplate('FeuserCreate', 'form', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject($user, 'subjectAdminNotificationMail'),
			$message
		);

		return $user;
	}

	/**
	 * Send an email notification post activation to the admin
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendAdminNotificationMailPostActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('AdminNotificationMailPostActivation');
		$message = $this->renderFileTemplate('FeuserEdit', 'form', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject($user, 'subjectAdminNotificationMailPostActivation'),
			$message
		);

		return $user;
	}

	/**
	 * Send an email on registration request to activate the user by admin
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendAdminNotificationMailPreActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$user->setMailhash($this->getMailHash($user));

		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('AdminNotificationMailPreActivation');
		$message = $this->renderFileTemplate('FeuserCreate', 'confirm', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject($user, 'subjectAdminNotificationMailPreActivation'),
			$message
		);

		return $user;
	}

	/**
	 * Send an email notification pre activation to the user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendUserNotificationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('UserNotificationMail');
		$message = $this->renderFileTemplate('FeuserCreate', 'form', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject($user, 'subjectUserNotificationMail'),
			$message
		);

		return $user;
	}

	/**
	 * Send an email notification post activation to the user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendUserNotificationMailPostActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('UserNotificationMailPostActivation');
		$message = $this->renderFileTemplate('FeuserEdit', 'form', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject($user, 'subjectUserNotificationMailPostActivation'),
			$message
		);

		return $user;
	}

	/**
	 * Send an email on registration request to activate the user by user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendUserNotificationMailPreActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$user->setMailhash($this->getMailHash($user));

		$variables = array(
			'user' => $user
		);

		$templatePathAndFilename = $this->getTemplatePathAndFilename('UserNotificationMailPreActivation');
		$message = $this->renderFileTemplate('FeuserCreate', 'confirm', $templatePathAndFilename, $variables);

		$this->sendEmail(
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject($user, 'subjectUserNotificationMailPreActivation'),
			$message
		);

		return $user;
	}


	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $labelIndex
	 * @return string
	 */
	protected function getSubject(Tx_SfRegister_Domain_Model_FrontendUser $user, $labelIndex) {
		return Tx_Extbase_Utility_Localization::translate(
			$labelIndex,
			'sf_register',
			array($this->settings['sitename'], $user->getUsername())
		);
	}

	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return string
	 */
	protected function getMailHash(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		return md5($user->getUsername() . time() . $user->getEmail());
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
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return string
	 */
	protected function getUserRecipient(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if ($user->getFirstName() || $user->getLastName()) {
			$name = trim($user->getFirstName() . ' ' . $user->getLastName());
		}

		return array(
			trim($user->getEmail()) => $name
		);
	}


	/**
	 * Send email
	 *
	 * @param array $recipient
	 * @param string $typeOfEmail
	 * @param string $subject
	 * @param string $body
	 * @return boolean
	 */
	protected function sendEmail(array $recipient, $typeOfEmail, $subject, $body) {
		$mail = t3lib_div::makeInstance('t3lib_mail_Message');
		$mail
			->setTo($recipient)
			->setFrom(array($this->settings[$typeOfEmail]['fromEmail'] => $this->settings[$typeOfEmail]['fromName']))
			->setReplyTo(array($this->settings[$typeOfEmail]['replyEmail'] => $this->settings[$typeOfEmail]['replyName']))
			->setSubject($subject)
			->setBody($body);

		//$mail->attach(Swift_Attachment::fromPath($theFile)->setFilename($theName));

		$mail->send();
		return $mail->isSent();
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
		$templateRootPath = $this->frameworkConfiguration['view']['templateRootPath'];

		if ($templateRootPath === '') {
			$templateRootPath = t3lib_extMgm::extPath('sf_register') . 'Resources/Private/Templates/';
		}

		$templateRootPath = t3lib_div::getFileAbsFileName($templateRootPath);
		if (t3lib_div::isAllowedAbsPath($templateRootPath)) {
			return $templateRootPath;
		}
	}

	/**
	 * renders the given Template file via fluid rendering engine.
	 *
	 * @param string $controller
	 * @param string $action
	 * @param string $templateFile absolute path to the template File
	 * @param array $vars array of all variables you want to assgin to the view
	 * @return string of the rendered View.
	 */
	protected function renderFileTemplate($controller, $action, $templateFile, array $vars) {
		$view = $this->objectManager->get('Tx_Fluid_View_StandaloneView');
		$view->setTemplatePathAndFilename($templateFile);
		$view->assignMultiple($vars);

		$request = $view->getRequest();
		$request->setPluginName($this->frameworkConfiguration['pluginName']);
		$request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
		$request->setControllerName($controller);
		$request->setControllerActionName($action);

		$data = $view->render();

		return $data;
	}
}

?>