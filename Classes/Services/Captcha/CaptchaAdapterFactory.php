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
 * Factory to build a captcha
 */
class Tx_SfRegister_Services_Captcha_CaptchaAdapterFactory {
	/**
	 * Object manager
	 *
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * Configuration manager
	 *
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * Settings
	 *
	 * @var array
	 */
	protected $settings = array();

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
	 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}

	/**
	 * Get an adapter for an captcha of given type
	 * @param string $type
	 * @return Tx_SfRegister_Interfaces_Captcha
	 */
	public function getCaptchaAdapter($type) {
		$settings = array();

		if (array_key_exists($type, $this->settings['captcha'])) {
			$settings = is_array($this->settings['captcha'][$type]) ?
				$this->settings['captcha'][$type] :
				array();

			$type = is_array($this->settings['captcha'][$type]) ?
				$this->settings['captcha'][$type]['_typoScriptNodeValue'] :
				$this->settings['captcha'][$type];
		} elseif (strpos($type, '_') === FALSE) {
			$type = 'Tx_SfRegister_Services_Captcha_' . ucfirst(strtolower($type)) . 'Adapter';
		}

		$captchaAdapter = $this->objectManager->get($type);
		$captchaAdapter->setSettings($settings);

		return $captchaAdapter;
	}
}