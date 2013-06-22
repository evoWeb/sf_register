<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


/** @noinspection PhpUndefinedVariableInspection */
$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl') &&
		(!isset($extensionConfiguration['setRealurlConfigByDefault']) ||
		$extensionConfiguration['setRealurlConfigByDefault'] == 1)) {
	/** @noinspection PhpIncludeInspection */
	require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/Realurl/realurl_conf.php');
}



\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Evoweb.' . $_EXTKEY,
	'Form',
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserEdit' => 'form, preview, proxy, save, removeImage',
		'FeuserPassword' => 'form, save',
	),
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserEdit' => 'form, preview, proxy, save, removeImage',
		'FeuserPassword' => 'form, save',
	)
);

$TYPO3_CONF_VARS['FE']['eID_include']['sf_register'] = 'EXT:sf_register/Classes/Api/Ajax.php';

?>