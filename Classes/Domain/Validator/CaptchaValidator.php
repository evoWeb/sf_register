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
 * A captcha validator
 *
 * @scope singleton
 */
class Tx_SfRegister_Domain_Validator_CaptchaValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * Captcha adapter factory
	 *
	 * @var Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory
	 */
	protected $captchaAdapterFactory;

	/**
	 * Inject of captcha adapter factory
	 *
	 * @param Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory $captchaAdapterFactory
	 * @return void
	 */
	public function injectCaptchaAdapterFactory(Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory $captchaAdapterFactory) {
		$this->captchaAdapterFactory = $captchaAdapterFactory;
	}

	/**
	 * If the given captcha is valid
	 *
	 * @param object $object
	 * @return boolean
	 */
	public function isValid($value) {
		$result = TRUE;

		$captchaAdapter = $this->captchaAdapterFactory->getCaptchaAdapter($this->options['type']);
		if (!$captchaAdapter->isValid($value)) {
			$result = FALSE;
			$this->errors = $captchaAdapter->getErrors();
		}

		return $result;
	}
}

?>