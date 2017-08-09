<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    /**
     * Page TypoScript for mod wizards
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sf_register/Configuration/TsConfig/ModWizards.typoscript">'
    );
    /**
     * User TypoScript for fields
     *
     * Needs to be added on top so others can extend regardless of load order
     */
    $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] = '
[GLOBAL]
<INCLUDE_TYPOSCRIPT: source="FILE:EXT:sf_register/Configuration/TypoScript/Fields/setup.typoscript">
' . $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'];
});
