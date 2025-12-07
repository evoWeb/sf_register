<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

call_user_func(static function () {
    ExtensionManagementUtility::addTcaSelectItemGroup(
        'tt_content',
        'CType',
        'sf_register',
        'sf_register.be:tt_content.list_type_group'
    );

    $GLOBALS['TCA']['tt_content']['palettes']['sfregister-frames'] = [
        'label' => 'frontend.ttc:palette.frames',
        'showitem' => '
            frame_class;frontend.ttc:frame_class_formlabel,
            space_before_class;frontend.ttc:space_before_class_formlabel,
            space_after_class;frontend.ttc:space_after_class_formlabel
        ',
    ];

    $showItems = '
            --palette--;;general,
            --palette--;;headers,
        --div--;core.form.tabs:plugin,
            pi_flexform,
            pages;frontend.ttc:pages.ALT.list_formlabel,
            recursive,
        --div--;core.form.tabs:appearance,
            --palette--;;sfregister-frames,
            --palette--;;appearanceLinks,
        --div--;core.form.tabs:categories,
            categories,
    ';

    $pluginSignature = ExtensionUtility::registerPlugin(
        'sf_register',
        'Create',
        'sf_register.be:tt_content.list_type_create',
        'sf-register-plugin',
        'sf_register',
        'sf_register.be:tt_content.list_type_create_description',
        'FILE:EXT:sf_register/Configuration/FlexForms/create.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;

    $pluginSignature = ExtensionUtility::registerPlugin(
        'sf_register',
        'Edit',
        'sf_register.be:tt_content.list_type_edit',
        'sf-register-plugin',
        'sf_register',
        'sf_register.be:tt_content.list_type_edit_description',
        'FILE:EXT:sf_register/Configuration/FlexForms/edit.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;

    $pluginSignature = ExtensionUtility::registerPlugin(
        'sf_register',
        'Delete',
        'sf_register.be:tt_content.list_type_delete',
        'sf-register-plugin',
        'sf_register',
        'sf_register.be:tt_content.list_type_delete_description',
        'FILE:EXT:sf_register/Configuration/FlexForms/delete.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;

    $pluginSignature = ExtensionUtility::registerPlugin(
        'sf_register',
        'RequestLink',
        'sf_register.be:tt_content.list_type_requestlink',
        'sf-register-plugin',
        'sf_register',
        'sf_register.be:tt_content.list_type_requestlink_description',
        'FILE:EXT:sf_register/Configuration/FlexForms/request-delete.xml',
    );

    $pluginSignature = ExtensionUtility::registerPlugin(
        'sf_register',
        'Password',
        'sf_register.be:tt_content.list_type_password',
        'sf-register-plugin',
        'sf_register',
        'sf_register.be:tt_content.list_type_password_description',
        'FILE:EXT:sf_register/Configuration/FlexForms/password.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;

    $pluginSignature = ExtensionUtility::registerPlugin(
        'sf_register',
        'Invite',
        'sf_register.be:tt_content.list_type_invite',
        'sf-register-plugin',
        'sf_register',
        'sf_register.be:tt_content.list_type_invite_description',
        'FILE:EXT:sf_register/Configuration/FlexForms/invite.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;

    $pluginSignature = ExtensionUtility::registerPlugin(
        'sf_register',
        'Resend',
        'sf_register.be:tt_content.list_type_resend',
        'sf-register-plugin',
        'sf_register',
        'sf_register.be:tt_content.list_type_resend_description',
        'FILE:EXT:sf_register/Configuration/FlexForms/resend.xml',
    );
    $GLOBALS['TCA']['tt_content']['types'][$pluginSignature]['showitem'] = $showItems;
});
