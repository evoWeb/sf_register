<?php

declare(strict_types=1);

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

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['flexFormSegment'][ FormFields::class ] = [
    'depends' => [ TcaCheckboxItems::class ],
    'before' => [ TcaSelectItems::class ],
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][ FrontendUserInterface::class ]['className'] = FrontendUser::class;

ExtensionUtility::configurePlugin(
    'SfRegister',
    'Create',
    [ FeuserCreateController::class => FeuserCreateController::PLUGIN_ACTIONS ],
    [ FeuserCreateController::class => FeuserCreateController::PLUGIN_ACTIONS ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'SfRegister',
    'Edit',
    [ FeuserEditController::class => FeuserEditController::PLUGIN_ACTIONS ],
    [ FeuserEditController::class => FeuserEditController::PLUGIN_ACTIONS ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'SfRegister',
    'Delete',
    [ FeuserDeleteController::class => FeuserDeleteController::DELETE_PLUGIN_ACTIONS ],
    [ FeuserDeleteController::class => FeuserDeleteController::DELETE_PLUGIN_ACTIONS ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'SfRegister',
    'RequestLink',
    [ FeuserDeleteController::class => FeuserDeleteController::REQUEST_PLUGIN_ACTIONS ],
    [ FeuserDeleteController::class => FeuserDeleteController::REQUEST_PLUGIN_ACTIONS ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'SfRegister',
    'Password',
    [ FeuserPasswordController::class => FeuserPasswordController::PLUGIN_ACTIONS ],
    [ FeuserPasswordController::class => FeuserPasswordController::PLUGIN_ACTIONS ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'SfRegister',
    'Invite',
    [ FeuserInviteController::class => FeuserInviteController::PLUGIN_ACTIONS ],
    [ FeuserInviteController::class => FeuserInviteController::PLUGIN_ACTIONS ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'SfRegister',
    'Resend',
    [ FeuserResendController::class => FeuserResendController::PLUGIN_ACTIONS ],
    [ FeuserResendController::class => FeuserResendController::PLUGIN_ACTIONS ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionManagementUtility::addService(
    'sf_register',
    'auth',
    AutoLogin::class,
    [
        'title' => 'Auto login after registration with sf_register',
        'description' => 'After a user registers with the help of sf_register this service authenticates the user with
            a given session value',
        'subtype' => 'getUserFE,authUserFE',
        'available' => true,
        'priority' => 75,
        'quality' => 75,
        'os' => '',
        'exec' => '',
        'className' => AutoLogin::class,
    ]
);
