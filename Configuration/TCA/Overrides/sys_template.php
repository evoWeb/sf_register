<?php

defined('TYPO3') or die();

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
