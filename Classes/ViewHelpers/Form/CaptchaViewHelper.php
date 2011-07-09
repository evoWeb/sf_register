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
 * Viewhelper to output a captcha in a form
 *
 * <code title="Usage">
 * {namespace register=Tx_SfRegister_ViewHelpers}
 * <register:form.captcha type="jmrecaptcha"/>
 * </code>
 */
class Tx_SfRegister_ViewHelpers_Form_CaptchaViewHelper extends Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper {
	/**
	 * Factory to create a captcha that is used to render the output
	 *
	 * @var Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory
	 */
	protected $captchaAdapterFactory;

	/**
	 * Injection of factory by object manager
	 *
	 * @param Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory $captchaAdapterFactory
	 * @return void
	 */
	public function injectCaptchaAdapterFactory(Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory $captchaAdapterFactory) {
		$this->captchaAdapterFactory = $captchaAdapterFactory;
	}

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
	}

	/**
	 * Render the captcha block
	 *
	 * @param string $type Type of captcha to use (comes from hooked captchas in generell)
	 * @return string
	 */
	public function render($type) {
		return $this->captchaAdapterFactory->getCaptchaAdapter($type)->render();
	}
}

?>