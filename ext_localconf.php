<?php
defined('TYPO3_MODE') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;


/** @noinspection PhpUndefinedVariableInspection */
$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (
	ExtensionManagementUtility::isLoaded('realurl')
	&& (
		!isset($extensionConfiguration['setRealurlConfigByDefault'])
		|| $extensionConfiguration['setRealurlConfigByDefault'] == 1
	)
) {
	/** @noinspection PhpIncludeInspection */
	require_once(ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/Realurl/realurl_conf.php');
}


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Evoweb.' . $_EXTKEY,
	'Form',
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserEdit' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserPassword' => 'form, save',
	),
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserEdit' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserPassword' => 'form, save',
	)
);


$TYPO3_CONF_VARS['FE']['eID_include']['sf_register'] = 'EXT:sf_register/Classes/Api/Ajax.php';


define('SFREGISTERCACHEIDENTIFIER', 'cache_' . $_EXTKEY . '_extending');

	// Register cache sf_register
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER] = array(
	'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\PhpFrontend',
	'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\FileBackend',
	'options' => array(),
);

	// Configure clear cache post processing for extended domain model
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] =
	'EXT:' . $_EXTKEY . '/Classes/Utility/ClassCacheManager.php:\Evoweb\SfRegister\Utility\ClassCacheManager->reBuild';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter('Evoweb\\SfRegister\\Property\\TypeConverter\\FrontendUserConverter');

\Evoweb\SfRegister\Utility\ClassLoader::registerAutoloader();
