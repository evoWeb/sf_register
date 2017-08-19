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

    $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sf_register']);

    if (TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')
        && (
            !isset($extensionConfiguration['setRealurlConfigByDefault'])
            || $extensionConfiguration['setRealurlConfigByDefault'] == 1
        )
    ) {
        /** @noinspection PhpIncludeInspection */
        require_once(
            TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath(
                'sf_register',
                'Configuration/Realurl/configuration.php'
            )
        );
    }

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Evoweb.sf_register',
        'Form',
        [
            'FeuserCreate' => 'inviteForm, invite, form, preview, proxy, save, confirm, accept, decline, refuse, removeImage',
            'FeuserEdit' => 'form, preview, proxy, save, confirm, accept, removeImage',
            'FeuserPassword' => 'form, save',
            'FeuserInvite' => 'inviteForm, invite',
        ],
        [
            'FeuserCreate' => 'inviteForm, invite, form, preview, proxy, save, confirm, accept, decline, refuse, removeImage',
            'FeuserEdit' => 'form, preview, proxy, save, confirm, accept, removeImage',
            'FeuserPassword' => 'form, save',
            'FeuserInvite' => 'inviteForm, invite',
        ]
    );

    $GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['sf_register'] =
        \Evoweb\SfRegister\Controller\AjaxController::class . '::processRequest';

    define('SFREGISTERCACHEIDENTIFIER', 'cache_sf_register_extending');

    // Register cache sf_register
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
        'options' => [],
    ];


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


    if (TYPO3_MODE === 'FE' && !(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
        /**
         * Signal slot dispatcher
         *
         * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
         */
        $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class
        );
        $signalSlotDispatcher->connect(
            \Evoweb\SfRegister\Controller\FeuserController::class,
            'initializeAction',
            \Evoweb\SfRegister\Signal\FeuserControllerSignal::class,
            'initializeAction'
        );
    }

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
