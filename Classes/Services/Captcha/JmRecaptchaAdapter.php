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

class Tx_SfRegister_Services_Captcha_JmRecaptchaAdapter extends Tx_SfRegister_Services_Captcha_AbstractAdapter {
	/**
	 * Captcha object
	 *
	 * @var tx_jmrecaptcha
	 */
	protected $captcha = NULL;

	/**
	 * The constructor of the class
	 */
	public function __construct() {
		if (t3lib_extMgm::isLoaded('jm_recaptcha')) {
			require_once(t3lib_extMgm::extPath('jm_recaptcha') . 'class.tx_jmrecaptcha.php');
			$this->captcha = t3lib_div::makeInstance('tx_jmrecaptcha');
		}
	}

	/**
	 * Rendering the output of the captcha
	 *
	 * @return string
	 */
	public function render() {
		t3lib_div::makeInstance('Tx_SfRegister_Services_Session')
			->remove('captchaWasValidPreviously');

		if ($this->captcha !== null) {
			$output = $this->captcha->getReCaptcha($this->settings['error']);
		} else {
			$output = Tx_Extbase_Utility_Localization::translate('error.captcha.notinstalled', 'sf_register', array('jm_recaptcha'));
		}

		return $output;
	}

	/**
	 * Validate the captcha value from the request and output an error if not valid
	 *
	 * @param string $value
	 * @return bool
	 */
	public function isValid($value) {
		$validCaptcha = TRUE;

		if ($this->captcha !== null) {
			$_POST['recaptcha_response_field'] = $value;
			$status = $this->captcha->validateReCaptcha();

			if ($status == FALSE || $status['error'] !== NULL) {
				$validCaptcha = FALSE;
				$this->errors[] = new Tx_Extbase_Validation_Error(
					Tx_Extbase_Utility_Localization::translate('error.jmrecaptcha.' . $status['error'], 'SfRegister'),
					1307421960
				);
			}
		}

		return $validCaptcha;
	}
}

?>