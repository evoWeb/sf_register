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
	 * Send an email on registration request to activate the user
	 * 
	 * @param Tx_Rsmysherpasusers_Domain_Model_AbstractUser $user
	 */
	protected static function sendConfirmationMail(Tx_SfRegister_Domain_Model_FrontendUser $user) {

			//Sendmail
		$transport = Swift_SendmailTransport::newInstance(ini_get('sendmail_path'));
			//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		$hash = md5($user->getUsername().time().$user->getEmail());
		$user->setMailhash($hash);

			//Create a message
		$message = Swift_Message::newInstance(Tx_Extbase_Utility_Localization::translate('emails.registrationSubject','rsmysherpasusers'))
			->setFrom(array('registration@mysherpas.com' => Tx_Extbase_Utility_Localization::translate('emails.registrationSenderName','rsmysherpasusers')))
			->setTo(array($user->getEmail() => $user->getName()))
			->setBody(self::renderFileTemplate(
				t3lib_extMgm::extPath('rsmysherpasusers', 'Resources/Private/Templates/eMails/registration.html'),
				array(
					'user' => $user,
					'hash' => $hash
				)
			), 'text/html')
			->addPart(self::renderFileTemplate(
				t3lib_extMgm::extPath('rsmysherpasusers', 'Resources/Private/Templates/eMails/registration.txt'),
				array(
					'user' => $user,
					'hash' => $hash
				)
			), 'text/plain')
		;

			//Send the message
		$result = $mailer->send($message);
	}
}

?>