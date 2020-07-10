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

    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) < 10000000) {
        // @todo remove once TYPO3 9.5.x support is dropped
        $extensionName = 'Evoweb.sf_register';
        $createController = 'FeuserCreate';
        $editController = 'FeuserEdit';
        $passwordController = 'FeuserPassword';
        $inviteController = 'FeuserInvite';
        $deleteController = 'FeuserDelete';
        $resendController = 'FeuserResend';
    } else {
        $extensionName = 'SfRegister';
        $createController = \Evoweb\SfRegister\Controller\FeuserCreateController::class;
        $editController = \Evoweb\SfRegister\Controller\FeuserEditController::class;
        $passwordController = \Evoweb\SfRegister\Controller\FeuserPasswordController::class;
        $inviteController = \Evoweb\SfRegister\Controller\FeuserInviteController::class;
        $deleteController = \Evoweb\SfRegister\Controller\FeuserDeleteController::class;
        $resendController = \Evoweb\SfRegister\Controller\FeuserResendController::class;
    }
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        $extensionName,
        'Form',
        [
            $createController => 'form, preview, proxy, save, confirm, accept, decline, refuse, removeImage',
            $editController => 'form, preview, proxy, save, confirm, accept, removeImage',
            $passwordController => 'form, save',
            $inviteController => 'inviteForm, invite',
            $deleteController => 'form, save, confirm',
            $resendController => 'form, mail',
        ],
        [
            $createController => 'form, preview, proxy, save, confirm, accept, decline, refuse, removeImage',
            $editController => 'form, preview, proxy, save, confirm, accept, removeImage',
            $passwordController => 'form, save',
            $inviteController => 'inviteForm, invite',
            $deleteController => 'form, save, confirm',
            $resendController => 'form, mail',
        ]
    );

    \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class)
        ->registerImplementation(
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
