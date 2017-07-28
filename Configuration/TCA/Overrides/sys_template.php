<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    /** @noinspection PhpUndefinedVariableInspection */
    $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sf_register']);

    switch ($extensionConfiguration['typoscriptComplexity']) {
        case 'maximal':
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
                'sf_register',
                'Configuration/TypoScript/maximal/',
                'Feuser Register [maximal]'
            );
            break;
        case 'minimal':
            // fall through intended
        default:
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
                'sf_register',
                'Configuration/TypoScript/minimal/',
                'Feuser Register [minimal]'
            );
    }
});