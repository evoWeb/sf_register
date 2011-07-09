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
 * Viewhelper to render a selectbox with values in given steps from start to end value
 *
 * <code title="Usage">
 * {namespace register=Tx_SfRegister_ViewHelpers}
 * <register:form.required fieldName="'username"/>
 * </code>
 */
class Tx_SfRegister_ViewHelpers_Form_RequiredViewHelper extends Tx_Fluid_ViewHelpers_Form_AbstractFormFieldViewHelper {
	/**
	 * Configuration manager to fetch settings from
	 *
	 * @var Tx_Extbase_Configuration_ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * Settings of the plugin
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Configuration of the framework
	 *
	 * @var array
	 */
	protected $frameworkConfiguration = array();

	/**
	 * Injection of configuration manager
	 *
	 * @param	Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
		$this->frameworkConfiguration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
	}

	/**
	 * Render a special sign if the field is required 
	 *
	 * @param	string $fieldName Name of the field to render the requird marker to
	 * @return	string
	 */
	public function render($fieldName) {
		$mode = str_replace('feuser', '', strtolower(key($this->frameworkConfiguration['controllerConfiguration'])));
		$modeSettings = $this->settings['validation'][$mode];

		$result = '';
		if (array_key_exists($fieldName, $modeSettings) &&
				($modeSettings[$fieldName] != '' || is_array($modeSettings[$fieldName])) &&
				($modeSettings[$fieldName] == 'Tx_SfRegister_Domain_Validator_RequiredValidator' ||
				array_search('Tx_SfRegister_Domain_Validator_RequiredValidator', $modeSettings[$fieldName]))) {
			$result = $this->renderChildren();
		}

		return $result;
	}
}

?>