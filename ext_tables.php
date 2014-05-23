<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}


/** @noinspection PhpUndefinedVariableInspection */
$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

switch ($extensionConfiguration['typoscriptComplexity']) {
	case 'maximal':
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/maximal/', 'Feuser Register [maximal]');
		break;
	case 'minimal':
		// fall through intended
	default:
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/minimal/', 'Feuser Register [minimal]');
}


/**
 * Page TypoScript for mod wizards
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
	'<INCLUDE_TYPOSCRIPT: source="FILE:EXT:' . $_EXTKEY . '/Configuration/PageTypoScript/ModWizards.ts">'
);


$tempColumns = array(
	'mailhash' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.mailhash',
		'config' => array(
			'type' => 'input',
			'readOnly' => TRUE,
		)
	),

	'activated_on' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.activated_on',
		'config' => array(
			'type' => 'input',
			'readOnly' => TRUE,
			'eval' => 'datetime',
		)
	),

	'pseudonym' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.pseudonym',
		'config' => array(
			'type' => 'input',
			'size' => '20',
			'max' => '50',
			'eval' => 'trim',
		)
	),
	'gender' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gender',
		'config' => array(
			'type' => 'radio',
			'items' => array(
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gender.I.1', '1'),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gender.I.2', '2')
			),
		)
	),
	'date_of_birth' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.date_of_birth',
		'config' => array(
			'type' => 'input',
			'size' => '10',
			'max' => '20',
			'eval' => 'date',
			'checkbox' => '0',
			'default' => ''
		)
	),
	'language' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.language',
		'config' => array(
			'type' => 'input',
			'size' => '4',
			'max' => '2',
			'eval' => '',
			'default' => ''
		)
	),
	'zone' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.zone',
		'config' => array(
			'type' => 'input',
			'size' => '20',
			'max' => '40',
			'eval' => 'trim',
			'default' => ''
		)
	),
	'timezone' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone',
		'config' => array(
			'type' => 'select',
			'items' => array(
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-12', -12),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-11', -11),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-10', -10),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-9.5', -9.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-9', -9),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-8', -8),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-7', -7),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-6', -6),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-5', -5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-4.5', -4.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-4', -4),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-3.5', -3.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-3', -3),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-2', -2),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.-1', -1),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.0', 0),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.1', 1),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.2', 2),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.3', 3),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.3.5', 3.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.4', 4),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.4.5', 4.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.5', 5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.5.5', 5.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.5.75', 5.75),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.6', 6),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.6.5', 6.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.7', 7),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.8', 8),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.9', 9),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.9.5', 9.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.10', 10),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.11', 11),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.11.5', 11.5),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.12', 12),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.12.75', 12.75),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.13', 13),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.timezone.I.14', 14),
			),
			'size' => 1,
			'maxitems' => 1,
			'default' => 0,
		)
	),
	'daylight' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.daylight',
		'config'  => array(
			'type' => 'check'
		)
	),
	'mobilephone' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.mobilephone',
		'config' => array(
			'type' => 'input',
			'size' => 20,
		)
	),
	'gtc' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.gtc',
		'config'  => array(
			'type' => 'check'
		)
	),
	'privacy' => array(
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.privacy',
		'config'  => array(
			'type' => 'check'
		)
	),
	'status' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status',
		'config' => array(
			'type' => 'select',
			'items' => array(
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.0', '0'),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.1', '1'),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.2', '2'),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.3', '3'),
				Array('LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.status.I.4', '4'),
			),
			'size' => 1,
			'maxitems' => 1,
		)
	),
	'by_invitation' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.by_invitation',
		'config' => array(
			'type' => 'check',
			'default' => '0'
		)
	),
	'comments' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.comments',
		'config' => array(
			'type' => 'text',
			'rows' => '5',
			'cols' => '48'
		)
	),
	'email_new' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.email_new',
		'config' => array(
			'type' => 'input',
			'size' => '20',
			'max' => '80',
			'eval' => 'trim',
		)
	),
	'module_sys_dmail_newsletter' => array(
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.module_sys_dmail_newsletter',
		'exclude' => '1',
		'config' => array(
			'type' => 'check'
		)
	),
	'module_sys_dmail_html' => array(
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.module_sys_dmail_html',
		'exclude' => '1',
		'config' => array(
			'type' => 'check'
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gender', '', 'before:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'pseudonym', '', 'after:username');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'date_of_birth, language, status', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'zone, timezone, daylight', '', 'after:city');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'mobilephone', '', 'after:telephone');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'email_new', '', 'after:email');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.div.registration, mailhash, activated_on, gtc, privacy');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'by_invitation, comments, module_sys_dmail_html');

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
	$tempColumns = array(
		'static_info_country' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.static_info_country',
			'config' => array(
				'type' => 'input',
				'size' => '5',
				'max' => '3',
				'eval' => '',
				'default' => ''
			)
		),
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumns);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'static_info_country', '', 'after:zone');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['sfregister_form'] = 'layout, select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['sfregister_form'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('sfregister_form', 'FILE:EXT:sf_register/Configuration/FlexForms/form.xml');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Form',
	'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tt_content.list_type_form'
);


try {
	\TYPO3\CMS\Core\Cache\Cache::initializeCachingFramework();

	/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
	$cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
		// Reflection cache
	if (!$cacheManager->hasCache(SFREGISTERCACHEIDENTIFIER)) {
		/** @var \TYPO3\CMS\Core\Cache\CacheFactory $typo3CacheFactory */
		$typo3CacheFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheFactory');
		$typo3CacheFactory->create(
			SFREGISTERCACHEIDENTIFIER,
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER]['frontend'],
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER]['backend'],
			$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][SFREGISTERCACHEIDENTIFIER]['options']
		);
	}
} catch (Exception $exeption) {
	\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Can not create cache ' . SFREGISTERCACHEIDENTIFIER, $_EXTKEY, 2);
}