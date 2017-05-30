<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    array(
        'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:tt_content.list_type_form',
        'sfregister_form'
    ),
    'list_type',
    'sf_register'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_form'] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_form'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'sfregister_form',
    'FILE:EXT:sf_register/Configuration/FlexForms/form.xml'
);
