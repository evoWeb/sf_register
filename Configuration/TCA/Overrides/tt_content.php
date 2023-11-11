<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

(static function () {
    $languageFile = 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:';

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Create',
        $languageFile . 'tt_content.list_type_create'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_create'] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_create'] = 'pi_flexform';

    ExtensionManagementUtility::addPiFlexFormValue(
        'sfregister_create',
        'FILE:EXT:sf_register/Configuration/FlexForms/create.xml'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Edit',
        $languageFile . 'tt_content.list_type_edit'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_edit'] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_edit'] = 'pi_flexform';

    ExtensionManagementUtility::addPiFlexFormValue(
        'sfregister_edit',
        'FILE:EXT:sf_register/Configuration/FlexForms/edit.xml'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Delete',
        $languageFile . 'tt_content.list_type_delete'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_delete'] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_delete'] = 'pi_flexform';

    ExtensionUtility::registerPlugin(
        'sf_register',
        'RequestLink',
        $languageFile . 'tt_content.list_type_requestlink'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_requestlink'] =
        'layout, select_key';

    ExtensionManagementUtility::addPiFlexFormValue(
        'sfregister_delete',
        'FILE:EXT:sf_register/Configuration/FlexForms/delete.xml'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Password',
        $languageFile . 'tt_content.list_type_password'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_password'] =
        'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_password'] = 'pi_flexform';

    ExtensionManagementUtility::addPiFlexFormValue(
        'sfregister_password',
        'FILE:EXT:sf_register/Configuration/FlexForms/password.xml'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Invite',
        $languageFile . 'tt_content.list_type_invite'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_invite'] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_invite'] = 'pi_flexform';

    ExtensionManagementUtility::addPiFlexFormValue(
        'sfregister_invite',
        'FILE:EXT:sf_register/Configuration/FlexForms/invite.xml'
    );

    ExtensionUtility::registerPlugin(
        'sf_register',
        'Resend',
        $languageFile . 'tt_content.list_type_resend'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_resend'] = 'layout, select_key';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_resend'] = 'pi_flexform';

    ExtensionManagementUtility::addPiFlexFormValue(
        'sfregister_resend',
        'FILE:EXT:sf_register/Configuration/FlexForms/resend.xml'
    );
})();
