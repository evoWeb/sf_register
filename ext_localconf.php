<?php

use Evoweb\SfRegister\Controller\FeuserCreateController;
use Evoweb\SfRegister\Controller\FeuserDeleteController;
use Evoweb\SfRegister\Controller\FeuserEditController;
use Evoweb\SfRegister\Controller\FeuserInviteController;
use Evoweb\SfRegister\Controller\FeuserPasswordController;
use Evoweb\SfRegister\Controller\FeuserResendController;
use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\FrontendUserInterface;
use Evoweb\SfRegister\Form\FormDataProvider\FormFields;
use Evoweb\SfRegister\Services\AutoLogin;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaCheckboxItems;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();

call_user_func(function () {
    /**
     * Page TypoScript for mod wizards
     */
    ExtensionManagementUtility::addPageTSConfig(
        '@import \'EXT:sf_register/Configuration/TSconfig/Wizards/NewContentElement.tsconfig\''
    );

    // Needs to be added on top so others can extend regardless of load order
    $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] = '
[GLOBAL]
@import \'EXT:sf_register/Configuration/TypoScript/Fields/setup.typoscript\'
' . $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'];

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['flexFormSegment'][FormFields::class] = [
        'depends' => [ TcaCheckboxItems::class ],
        'before' => [ TcaSelectItems::class ],
    ];

    ExtensionUtility::configurePlugin(
        'SfRegister',
        'Create',
        [
            FeuserCreateController::class =>
                'form, preview, proxy, save, confirm, refuse, accept, decline, removeImage',
        ],
        [
            FeuserCreateController::class =>
                'form, preview, proxy, save, confirm, refuse, accept, decline, removeImage',
        ]
    );

    ExtensionUtility::configurePlugin(
        'SfRegister',
        'Edit',
        [ FeuserEditController::class => 'form, preview, proxy, save, confirm, accept, removeImage' ],
        [ FeuserEditController::class => 'form, preview, proxy, save, confirm, accept, removeImage' ]
    );

    ExtensionUtility::configurePlugin(
        'SfRegister',
        'Delete',
        [ FeuserDeleteController::class => 'form, save, confirm' ],
        [ FeuserDeleteController::class => 'form, save, confirm' ]
    );

    ExtensionUtility::configurePlugin(
        'SfRegister',
        'RequestLink',
        [ FeuserDeleteController::class => 'request, sendLink' ],
        [ FeuserDeleteController::class => 'request, sendLink' ]
    );

    ExtensionUtility::configurePlugin(
        'SfRegister',
        'Password',
        [ FeuserPasswordController::class => 'form, save' ],
        [ FeuserPasswordController::class => 'form, save' ]
    );

    ExtensionUtility::configurePlugin(
        'SfRegister',
        'Invite',
        [ FeuserInviteController::class => 'form, invite' ],
        [ FeuserInviteController::class => 'form, invite' ]
    );

    ExtensionUtility::configurePlugin(
        'SfRegister',
        'Resend',
        [ FeuserResendController::class => 'form, mail' ],
        [ FeuserResendController::class => 'form, mail' ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUserInterface::class]['className'] = FrontendUser::class;

    ExtensionManagementUtility::addService(
        'sf_register',
        'auth',
        AutoLogin::class,
        [
            'title' => 'Auto login for users of sf_register',
            'description' => 'Authenticates user with given session value',
            'subtype' => 'getUserFE,authUserFE',
            'available' => true,
            'priority' => 75,
            'quality' => 75,
            'os' => '',
            'exec' => '',
            'className' => AutoLogin::class,
        ]
    );
});
