<?php

declare(strict_types=1);

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::registerPageTSConfigFile(
    'sf_register',
    'Configuration/TSconfig/NewContentElementWizard.tsconfig',
    '[Frontend user registration] New content element wizards',
);
