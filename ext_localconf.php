<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


/** @noinspection PhpUndefinedVariableInspection */
$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (
	t3lib_extMgm::isLoaded('realurl')
	&& (
		!isset($extensionConfiguration['setRealurlConfigByDefault'])
		|| $extensionConfiguration['setRealurlConfigByDefault'] == 1
	)
) {
	/** @noinspection PhpIncludeInspection */
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


	// Register cache sf_register
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cache_' . $_EXTKEY . '_extending'] = array(
	'frontend' => 't3lib_cache_frontend_PhpFrontend',
	'backend' => 't3lib_cache_backend_FileBackend',
	'options' => array(),
);

	// Configure clear cache post processing for extended domain model
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] =
	'EXT:' . $_EXTKEY . '/Classes/Utility/ClassCacheManager.php:Tx_SfRegister_Utility_ClassCacheManager->reBuild';

define('SFREGISTERCACHEIDENTIFIER', 'cache_' . $_EXTKEY . '_extending');

/** @noinspection PhpIncludeInspection */
require_once(t3lib_extMgm::extPath($_EXTKEY, 'Classes/Utility/ClassLoader.php'));
Tx_SfRegister_Utility_ClassLoader::registerAutoloader();

?>