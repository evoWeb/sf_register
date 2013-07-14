<?php
namespace Evoweb\SfRegister\Utility;
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class to render the wizard icon for new content elements
 *
 * @author	Jonas Duebi <jd@cabag.ch>
 * @package	TYPO3
 * @subpackage	tx_sfregister
 */
class WizardIcon {
	/**
	 * Process the wizard items array
	 *
	 * @param array $wizardItems
	 * @return array
	 */
	public function proc($wizardItems) {
		/** @var $language \TYPO3\CMS\Lang\LanguageService */
		$language = & $GLOBALS['LANG'];
		$ll = $this->includeLocalLang();

		$wizardItems['plugins_tx_sfregister_pi1'] = array(
			'icon' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('sf_register') . 'ext_icon.gif',
			'title' => $language->getLLL('pi1_title', $ll),
			'description' => $language->getLLL('pi1_plus_wiz_description', $ll),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=sfregister_form'
		);

		return $wizardItems;
	}

	/**
	 * Reads the locallang_be.xml and returns the $localLang values found in that file.
	 *
	 * @return array The array with language labels
	 */
	protected function includeLocalLang() {
		$localLang = \TYPO3\CMS\Core\Utility\GeneralUtility::readLLfile(
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sf_register') .
				'Resources/Private/Language/locallang_be.xml',
			$GLOBALS['LANG']->lang
		);

		return $localLang;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/sf_register/Classes/Utility/WizardIcon.php']) {
	/** @noinspection PhpIncludeInspection */
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/sf_register/Classes/Utility/WizardIcon.php']);
}

?>