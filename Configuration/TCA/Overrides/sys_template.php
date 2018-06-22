<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    /** @noinspection PhpUndefinedVariableInspection */
    $extensionConfiguration = !is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sf_register']) ?
        unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sf_register']) :
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sf_register'];

    switch ($extensionConfiguration['typoscriptComplexity']) {
        case 'maximal':
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
                'sf_register',
                'Configuration/TypoScript/maximal/',
                'Frontend User Register [maximal]'
            );
            break;
        case 'minimal':
            // fall through intended
        default:
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
                'sf_register',
                'Configuration/TypoScript/minimal/',
                'Frontend User Register [minimal]'
            );
    }
});
