<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    $typoScriptComplexity = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get('sf_register', 'typoscriptComplexity');

    switch ($typoScriptComplexity) {
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
