<?php
defined('TYPO3_MODE') || die();

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'sf_register',
        'Form',
        'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:tt_content.list_type_form'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_form'] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_form'] = 'pi_flexform';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'sfregister_form',
        'FILE:EXT:sf_register/Configuration/FlexForms/form.xml'
    );
});
