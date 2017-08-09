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

    try {
        /**
         * Cache manager
         *
         * @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager
         */
        $cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Cache\CacheManager::class);
        // Reflection cache
        if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 8000000
            && !$cacheManager->hasCache(SFREGISTERCACHEIDENTIFIER)
        ) {
            /**
             * TYPO3 cache factory
             *
             * @var \TYPO3\CMS\Core\Cache\CacheFactory $typo3CacheFactory
             */
            /** @noinspection PhpDeprecationInspection */
            $typo3CacheFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Cache\CacheFactory::class,
                'production',
                $cacheManager
            );
            $cacheConfiguration = $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
            $typo3CacheFactory->create(
                SFREGISTERCACHEIDENTIFIER,
                $cacheConfiguration[SFREGISTERCACHEIDENTIFIER]['frontend'],
                $cacheConfiguration[SFREGISTERCACHEIDENTIFIER]['backend'],
                $cacheConfiguration[SFREGISTERCACHEIDENTIFIER]['options']
            );
        }
    } catch (Exception $exeption) {
        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog(
            'Can not create cache ' . SFREGISTERCACHEIDENTIFIER,
            'sf_register',
            2
        );
    }
}, 'sf_register');
