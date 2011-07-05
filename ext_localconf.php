<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}



$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (t3lib_extMgm::isLoaded('realurl') && (!isset($extensionConfiguration['setRealurlConfigByDefault']) ||
		$extensionConfiguration['setRealurlConfigByDefault'] == 1)) {
	require_once(t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/Realurl/realurl_conf.php');
}



Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
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