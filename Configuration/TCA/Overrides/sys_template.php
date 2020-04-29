<?php

defined('TYPO3_MODE') || die();

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'sf_register',
        'Configuration/TypoScript/minimal/',
        'Frontend User Register [minimal]'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'sf_register',
        'Configuration/TypoScript/maximal/',
        'Frontend User Register [maximal]'
    );
});
