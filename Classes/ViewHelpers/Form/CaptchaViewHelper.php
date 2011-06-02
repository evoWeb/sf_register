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

class Tx_SfRegister_ViewHelpers_Form_CaptchaViewHelper extends Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper {
	/**
	 * @var Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory
	 */
	protected $captchaAdapterFactory;

	/**
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
		$this->registerTagAttribute('autocomplete', 'string', 'if set, the autocomplete of the browser will be turned of');
		$this->registerTagAttribute('size', 'string', 'Size of input field');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('name', 'string', 'Name of input tag');
		$this->registerArgument('value', 'mixed', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. If used in conjunction with <f:form object="...">, "name" and "value" properties will be ignored.');
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
	}

	/**
	 * Render the captcha block
	 *
	 * @param string $type Type of captcha to use (comes from hooked captchas in generell)
	 * @return void
	 */
	public function render($type) {
		return $this->captchaAdapterFactory->getCaptchaAdapter($type)->render();
	}
}

?>