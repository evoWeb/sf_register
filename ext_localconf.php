<?php
defined('TYPO3_MODE') or die();

/** @noinspection PhpUndefinedVariableInspection */
$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('realurl')
    && (
        !isset($extensionConfiguration['setRealurlConfigByDefault'])
        || $extensionConfiguration['setRealurlConfigByDefault'] == 1
    )
) {
    /** @noinspection PhpIncludeInspection */
    require_once(
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) .
        'Configuration/Realurl/realurl_conf.php'
    );
}


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Evoweb.' . $_EXTKEY,
    'Form',
    array(
        'FeuserCreate' => 'form, preview, proxy, save, confirm, accept, decline, refuse, removeImage',
        'FeuserEdit' => 'form, preview, proxy, save, confirm, accept, removeImage',
        'FeuserPassword' => 'form, save',
    ),
    array(
        'FeuserCreate' => 'form, preview, proxy, save, confirm, accept, decline, refuse, removeImage',
        'FeuserEdit' => 'form, preview, proxy, save, confirm, accept, removeImage',
        'FeuserPassword' => 'form, save',
    )
);


$TYPO3_CONF_VARS['FE']['eID_include']['sf_register'] = 'EXT:sf_register/Classes/Api/Ajax.php';


define('SFREGISTERCACHEIDENTIFIER', 'cache_' . $_EXTKEY . '_extending');

// Register cache sf_register
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER] = array(
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend::class,
    'backend' => \TYPO3\CMS\Core\Cache\Backend\FileBackend::class,
    'options' => array(),
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerTypeConverter(
    \Evoweb\SfRegister\Property\TypeConverter\FrontendUserConverter::class
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
