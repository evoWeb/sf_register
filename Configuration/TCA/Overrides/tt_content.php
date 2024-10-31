<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function () {
    $languageFile = 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:';
    $GLOBALS['TCA']['tt_content']['palettes']['sfregister-frames'] = [
        'label' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.frames',
        'showitem' => '
            frame_class;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:frame_class_formlabel,
            space_before_class;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:space_before_class_formlabel,
            space_after_class;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:space_after_class_formlabel
        '
    ];

    $showItems = '
            --palette--;;general,
            --palette--;;headers,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,
            pi_flexform,
            pages;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:pages.ALT.list_formlabel,
            recursive,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
            --palette--;;sfregister-frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
            categories,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended
    ';

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Create',
        $languageFile . 'tt_content.list_type_create'
    );
    $GLOBALS['TCA']['tt_content']['types']['sfregister_create']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:sf_register/Configuration/FlexForms/create.xml',
        'sfregister_create'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Edit',
        $languageFile . 'tt_content.list_type_edit'
    );
    $GLOBALS['TCA']['tt_content']['types']['sfregister_edit']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:sf_register/Configuration/FlexForms/edit.xml',
        'sfregister_edit'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Delete',
        $languageFile . 'tt_content.list_type_delete'
    );
    $GLOBALS['TCA']['tt_content']['types']['sfregister_delete']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:sf_register/Configuration/FlexForms/delete.xml',
        'sfregister_delete'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'RequestLink',
        $languageFile . 'tt_content.list_type_requestlink'
    );
    $GLOBALS['TCA']['tt_content']['types']['sfregister_requestlink']['showitem'] =
        str_replace('pi_flexform,', '', $showItems);

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Password',
        $languageFile . 'tt_content.list_type_password'
    );
    $GLOBALS['TCA']['tt_content']['types']['sfregister_password']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:sf_register/Configuration/FlexForms/password.xml',
        'sfregister_password'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Invite',
        $languageFile . 'tt_content.list_type_invite'
    );
    $GLOBALS['TCA']['tt_content']['types']['sfregister_invite']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:sf_register/Configuration/FlexForms/invite.xml',
        'sfregister_invite'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Resend',
        $languageFile . 'tt_content.list_type_resend'
    );
    $GLOBALS['TCA']['tt_content']['types']['sfregister_resend']['showitem'] = $showItems;

    ExtensionManagementUtility::addPiFlexFormValue(
        '*',
        'FILE:EXT:sf_register/Configuration/FlexForms/resend.xml',
        'sfregister_resend'
    );
})();
