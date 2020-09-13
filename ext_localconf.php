<?php

defined('TYPO3_MODE') || die();

call_user_func(function () {
    /**
     * Page TypoScript for mod wizards
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '@import \'EXT:sf_register/Configuration/TSconfig/Wizards/NewContentElement.typoscript\''
    );

    // Needs to be added on top so others can extend regardless of load order
    $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] = '
[GLOBAL]
@import \'EXT:sf_register/Configuration/TypoScript/Fields/setup.typoscript\'
' . $GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'];

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'sf-register-extension',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:sf_register/Resources/Public/Icons/Extension.svg']
    );

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['flexFormSegment'][
        \Evoweb\SfRegister\Form\FormDataProvider\FormFields::class
    ] = [
        'before' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
        ],
    ];

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SfRegister',
        'Create',
        [
            \Evoweb\SfRegister\Controller\FeuserCreateController::class =>
                'form, preview, proxy, save, confirm, refuse, accept, decline, removeImage',
        ],
        [
            \Evoweb\SfRegister\Controller\FeuserCreateController::class =>
                'form, preview, proxy, save, confirm, refuse, accept, decline, removeImage',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SfRegister',
        'Edit',
        [
            \Evoweb\SfRegister\Controller\FeuserEditController::class =>
                'form, preview, proxy, save, confirm, accept, removeImage',
        ],
        [
            \Evoweb\SfRegister\Controller\FeuserEditController::class =>
                'form, preview, proxy, save, confirm, accept, removeImage',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SfRegister',
        'Delete',
        [
            \Evoweb\SfRegister\Controller\FeuserDeleteController::class => 'form, save, confirm',
        ],
        [
            \Evoweb\SfRegister\Controller\FeuserDeleteController::class => 'form, save, confirm',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SfRegister',
        'RequestLink',
        [
            \Evoweb\SfRegister\Controller\FeuserDeleteController::class =>
                'request, sendLink',
        ],
        [
            \Evoweb\SfRegister\Controller\FeuserDeleteController::class =>
                'request, sendLink',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SfRegister',
        'Password',
        [
            \Evoweb\SfRegister\Controller\FeuserPasswordController::class => 'form, save',
        ],
        [
            \Evoweb\SfRegister\Controller\FeuserPasswordController::class => 'form, save',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SfRegister',
        'Invite',
        [
            \Evoweb\SfRegister\Controller\FeuserInviteController::class => 'form, invite',
        ],
        [
            \Evoweb\SfRegister\Controller\FeuserInviteController::class => 'form, invite',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'SfRegister',
        'Resend',
        [
            \Evoweb\SfRegister\Controller\FeuserResendController::class => 'form, mail',
        ],
        [
            \Evoweb\SfRegister\Controller\FeuserResendController::class => 'form, mail',
        ]
    );

    /** @var \TYPO3\CMS\Extbase\Object\Container\Container $container */
    $container = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Extbase\Object\Container\Container::class
    );
    $container->registerImplementation(
        \Evoweb\SfRegister\Interfaces\FrontendUserInterface::class,
        \Evoweb\SfRegister\Domain\Model\FrontendUser::class
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
        \Evoweb\SfRegister\Property\TypeConverter\FrontendUserConverter::class
    );
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
        \Evoweb\SfRegister\Property\TypeConverter\DateTimeConverter::class
    );
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
        \Evoweb\SfRegister\Property\TypeConverter\UploadedFileReferenceConverter::class
    );
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
        \Evoweb\SfRegister\Property\TypeConverter\ObjectStorageConverter::class
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
        'sf_register',
        'auth',
        \Evoweb\SfRegister\Services\AutoLogin::class,
        [
            'title' => 'Auto login for users of sf_register',
            'description' => 'Authenticates user with given session value',
            'subtype' => 'getUserFE,authUserFE',
            'available' => true,
            'priority' => 75,
            'quality' => 75,
            'os' => '',
            'exec' => '',
            'className' => \Evoweb\SfRegister\Services\AutoLogin::class,
        ]
    );
});
