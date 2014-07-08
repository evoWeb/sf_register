<?php
defined('TYPO3_MODE') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;


/** @noinspection PhpUndefinedVariableInspection */
$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

switch ($extensionConfiguration['typoscriptComplexity']) {
	case 'maximal':
		ExtensionManagementUtility::addStaticFile(
			$_EXTKEY, 'Configuration/TypoScript/maximal/', 'Feuser Register [maximal]'
		);
		break;
	case 'minimal':
		// fall through intended
	default:
		ExtensionManagementUtility::addStaticFile(
			$_EXTKEY, 'Configuration/TypoScript/minimal/', 'Feuser Register [minimal]'
		);
}


/**
 * Page TypoScript for mod wizards
 */
ExtensionManagementUtility::addPageTSConfig(
	'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/PageTypoScript/ModWizards.ts">'
);


try {
	\TYPO3\CMS\Core\Cache\Cache::initializeCachingFramework();

	/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
	$cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		// Reflection cache
	if (!$cacheManager->hasCache(SFREGISTERCACHEIDENTIFIER)) {
		/** @var \TYPO3\CMS\Core\Cache\CacheFactory $typo3CacheFactory */
		$typo3CacheFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheFactory');
		$typo3CacheFactory->create(
			SFREGISTERCACHEIDENTIFIER,
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER]['frontend'],
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER]['backend'],
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER]['options']
		);
	}
} catch (Exception $exeption) {
	\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Can not create cache ' . SFREGISTERCACHEIDENTIFIER, $_EXTKEY, 2);
}