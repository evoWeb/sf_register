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
		$type = str_replace('send', '', __FUNCTION__);
		$variables = array('user' => $user);

		$mailResult = $this->sendEmail(
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject($user, 'subject' . $type),
			$this->renderFileTemplate('FeuserCreate', 'form', $type, $variables)
		);

		return $this->processHook('send' . $type . 'PostSend', $user, $this->settings, $this->objectManager);
	}

	/**
	 * Send an email notification post activation to the admin
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendAdminNotificationMailPostActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$type = str_replace('send', '', __FUNCTION__);
		$variables = array('user' => $user);

		$mailResult = $this->sendEmail(
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject($user, 'subject' . $type),
			$this->renderFileTemplate('FeuserEdit', 'form', $type, $variables)
		);

		return $this->processHook('send' . $type . 'PostSend', $user, $this->settings, $this->objectManager);
	}

	/**
	 * Send an email on registration request to activate the user by admin
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendAdminNotificationMailPreActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$type = str_replace('send', '', __FUNCTION__);
		$variables = array('user' => $user);

		$user->setMailhash($this->getMailHash($user));

		$mailResult = $this->sendEmail(
			$this->getAdminRecipient(),
			'adminEmail',
			$this->getSubject($user, 'subject' . $type),
			$this->renderFileTemplate('FeuserCreate', 'confirm', $type, $variables)
		);

		return $this->processHook('send' . $type . 'PostSend', $user, $this->settings, $this->objectManager);
	}

	/**
	 * Send an email notification pre activation to the user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendUserNotificationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$type = str_replace('send', '', __FUNCTION__);
		$variables = array('user' => $user);

		$mailResult = $this->sendEmail(
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject($user, 'subject' . $type),
			$this->renderFileTemplate('FeuserCreate', 'form', $type, $variables)
		);

		return $this->processHook('send' . $type . 'PostSend', $user, $this->settings, $this->objectManager);
	}

	/**
	 * Send an email notification post activation to the user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendUserNotificationMailPostActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$type = str_replace('send', '', __FUNCTION__);
		$variables = array('user' => $user);

		$mailResult = $this->sendEmail(
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject($user, 'subject' . $type),
			$this->renderFileTemplate('FeuserEdit', 'form', $type, $variables)
		);

		return $this->processHook('send' . $type . 'PostSend', $user, $this->settings, $this->objectManager);
	}

	/**
	 * Send an email on registration request to activate the user by user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function sendUserNotificationMailPreActivation(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$type = str_replace('send', '', __FUNCTION__);
		$variables = array('user' => $user);

		$user->setMailhash($this->getMailHash($user));

		$mailResult = $this->sendEmail(
			$this->getUserRecipient($user),
			'userEmail',
			$this->getSubject($user, 'subject' . $type),
			$this->renderFileTemplate('FeuserCreate', 'confirm', $type, $variables)
		);

		return $this->processHook('send' . $type . 'PostSend', $user, $this->settings, $this->objectManager);
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
		$name = '';
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
	 * @param string $bodyHTML
	 * @param string $bodyPlain
	 * @return void
	 */
	protected function sendEmail(array $recipient, $typeOfEmail, $subject, $bodyHTML, $bodyPlain = '') {
		$mail = t3lib_div::makeInstance('t3lib_mail_Message');
		$mail
			->setTo($recipient)
			->setFrom(array($this->settings[$typeOfEmail]['fromEmail'] => $this->settings[$typeOfEmail]['fromName']))
			->setReplyTo(array($this->settings[$typeOfEmail]['replyEmail'] => $this->settings[$typeOfEmail]['replyName']))
			->setSubject($subject);

		if ($bodyHTML !== '') {
			$mail->addPart($bodyHTML, 'text/html');
		}
		if ($bodyPlain !== '') {
			$mail->addPart($bodyPlain, 'text/plain');
		}

		$mail = $this->processHook('sendMailPreSend', $mail, $this->settings, $this->objectManager);

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
			$templateRootPath = t3lib_extMgm::extPath('sf_register') . 'Resources/Private/Templates/';
		}

		$templateRootPath = t3lib_div::getFileAbsFileName($templateRootPath);
		if (t3lib_div::isAllowedAbsPath($templateRootPath)) {
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
			$layoutRootPath = t3lib_extMgm::extPath('sf_register') . 'Resources/Private/Layout/';
		}

		$layoutRootPath = t3lib_div::getFileAbsFileName($layoutRootPath);
		if (t3lib_div::isAllowedAbsPath($layoutRootPath)) {
			$result = $layoutRootPath . (substr($layoutRootPath, -1) !== '/' ? '/' : '');
		}

		return $result;
	}

	/**
	 * renders the given Template file via fluid rendering engine.
	 *
	 * @param string $controller
	 * @param string $action
	 * @param string $type type of template
	 * @param array $vars array of all variables you want to assgin to the view
	 * @return string of the rendered View.
	 */
	protected function renderFileTemplate($controller, $action, $type, array $variables) {
		$view = $this->objectManager->get('Tx_Fluid_View_StandaloneView');
		$view->setTemplatePathAndFilename($this->getTemplatePathAndFilename($type));
		$view->setLayoutRootPath($this->getAbsoluteLayoutRootPath());
		$view->assignMultiple($variables);

		$request = $view->getRequest();
		$request->setControllerExtensionName($this->frameworkConfiguration['extensionName']);
		$request->setPluginName($this->frameworkConfiguration['pluginName']);
		$request->setControllerName($controller);
		$request->setControllerActionName($action);

		return $view->render();
	}

	/**
	 * @return mixed
	 */
	protected function processHook($hookName) {
		$result = func_get_arg(1);

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sf_register']['Tx_SfRegister_Services_Mail'][$hookName]) &&
				count($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sf_register']['Tx_SfRegister_Services_Mail'][$hookName])) {
			$userObjectReferences = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sf_register']['Tx_SfRegister_Services_Mail'][$hookName];
			$arguments = array_slice(func_get_args(), 2);

			foreach ($userObjectReferences as $userObjectReference) {
				$hookObj =& t3lib_div::getUserObj($userObjectReference);
				$result = $hookObj->{$hookName}($result, $arguments);
			}
		}

		return $result;
	}
}

?>