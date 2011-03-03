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
 * An frontend user controller
 */
class Tx_SfRegister_Services_Mail implements t3lib_Singleton {
	/**
	 * @var array
	 */
	protected $settings = array();
	
	/**
	 * @var Tx_Extbase_Object_ManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param array $settings
	 * @return Tx_SfRegister_Services_Mail
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;

		return $this;
	}
	
	/**
	 * @param unknown_type $objectManager
	 * @return Tx_SfRegister_Services_Mail
	 */
	public function injectObjectManager($objectManager) {
		$this->objectManager = $objectManager;

		return $this;
	}

	/**
	 * Send an email on registration request to activate the user
	 * 
	 * @param Tx_Rsmysherpasusers_Domain_Model_AbstractUser $user
	 */
	public function sendConfirmationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$mailer = t3lib_div::makeInstance('t3lib_htmlmail');

		$user->setMailhash(md5($user->getUsername() . time() . $user->getEmail()));

		$subject = Tx_Extbase_Utility_Localization::translate('emails.confirmationSubject', 'subject of confirmation email not set');
		$subject = vsprintf($subject, array('sitename'));

		$variables = array(
			'user' => $user
		);

		$this->sendTemplateEmail(
			$user->getEmail(),
			$this->settings['confirmationmail']['fromEmail'],
			$this->settings['confirmationmail']['fromName'],
			$subject,
			'ConfirmationMail',
			$variables
		);

		return $user;
	}

	/**
	 * @param string $recipient
	 * @param string $senderEmail
	 * @param string $senderName
	 * @param string $subject
	 * @param string $templateName
	 * @param array $variables
	 * @return boolean
	 */
	protected function sendTemplateEmail($recipient, $senderEmail, $senderName, $subject, $templateName, array $variables = array()) {
		$emailView = $this->objectManager->create('Tx_Fluid_View_StandaloneView');
		$emailView->setFormat('html');

		$templatePathAndFilename = $this->getTemplatePathAndFilename($templateName);

		$emailView->setTemplatePathAndFilename($templatePathAndFilename);
		$emailView->assignMultiple($variables);
		$emailBody = $emailView->render();

			// TODO replace by t3lib_mail_Mailer as t3lib_htmlmail is deprecated
		$htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$htmlMail->start();
		$htmlMail->recipient = $recipient;
		$htmlMail->subject = $subject;
		$htmlMail->from_email = $senderEmail;
		$htmlMail->from_name = $senderName;
		$htmlMail->returnPath = $senderEmail;
		$htmlMail->addPlain($emailBody);
		$htmlMail->setHTML($htmlMail->encodeMsg($emailBody));

		return $htmlMail->send($recipient);
	}

	/**
	 * @param string $templateName
	 * @return string
	 */
	protected function getTemplatePathAndFilename($templateName) {
		$extbaseFrameworkConfiguration = $this->getFrameworkConfiguration();
		$templateRootPath = t3lib_div::getFileAbsFileName($extbaseFrameworkConfiguration['view']['templateRootPath']);
		$templatePathAndFilename = $templateRootPath . 'Email/' . $templateName . '.html';

		return $templatePathAndFilename;
	}
	
	/**
	 * @return array
	 */
	protected function getFrameworkConfiguration() {
		//$this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		//$this->concreteConfigurationManager->getConfiguration($extensionName, $pluginName);
		$configuration = Tx_Extbase_Dispatcher::$extbaseFrameworkConfiguration;

		debug($configuration);

		return $configuration;
	}
}

?>