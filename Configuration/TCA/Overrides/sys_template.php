<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

(static function () {
    ExtensionManagementUtility::addStaticFile(
        'sf_register',
        'Configuration/TypoScript/minimal/',
        'Frontend User Register [minimal]'
    );

    ExtensionManagementUtility::addStaticFile(
        'sf_register',
        'Configuration/TypoScript/maximal/',
        'Frontend User Register [maximal]'
    );
})();
