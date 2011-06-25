<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}



$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

if (!isset($extensionConfiguration['setRealurlConfigByDefault']) || $extensionConfiguration['setRealurlConfigByDefault'] == 1) {
	require_once(t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/Realurl/realurl_conf.php');
}

switch($extensionConfiguration['typoscriptComplexity']) {
	case 'maximal':
		t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/maximal/', 'Feuser Register [maximal]');
		break;
	case 'minimal':
	default:
		t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/minimal/', 'Feuser Register [minimal]');
		break;
}



t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addPageTSConfig('
TCAdefaults.fe_users.tx_extbase_type = Tx_Extbase_Domain_Model_FrontendUser
TCAdefaults.fe_groups.tx_extbase_type = Tx_Extbase_Domain_Model_FrontendUserGroup
');

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
	'module_sys_dmail_html' => array(
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.module_sys_dmail_html',
		'exclude' => '1',
		'config' => array(
			'type'=>'check'
		)
	),
);

t3lib_extMgm::addTCAcolumns('fe_users', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('fe_users', 'gender', '', 'before:name');
t3lib_extMgm::addToAllTCAtypes('fe_users', 'pseudonym', '', 'after:username');
t3lib_extMgm::addToAllTCAtypes('fe_users', 'date_of_birth, language, status', '', 'after:name');
t3lib_extMgm::addToAllTCAtypes('fe_users', 'zone, timezone, daylight', '', 'after:city');
t3lib_extMgm::addToAllTCAtypes('fe_users', 'mobilephone', '', 'after:telephone');

t3lib_extMgm::addToAllTCAtypes('fe_users', '--div--;LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.div.registration, mailhash, activated_on, gtc, privacy');
t3lib_extMgm::addToAllTCAtypes('fe_users', 'by_invitation, comments, module_sys_dmail_html');

if (t3lib_extMgm::isLoaded('static_info_tables')) {
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
	t3lib_extMgm::addTCAcolumns('fe_users', $tempColumns, 1);
	t3lib_extMgm::addToAllTCAtypes('fe_users', 'static_info_country', '', 'after:zone');
}

if (t3lib_extMgm::isLoaded('direct_mail')) {
	$tempColumns = array(
		'module_sys_dmail_category' => array(
			'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.module_sys_dmail_group.category',
			'exclude' => '1',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_dmail_category',
				'foreign_table_where' => 'AND sys_dmail_category.l18n_parent=0 AND sys_dmail_category.pid IN (###PAGE_TSCONFIG_IDLIST###) ORDER BY sys_dmail_category.uid',
				'itemsProcFunc' => 'tx_directmail_select_categories->get_localized_categories',
				'itemsProcFunc_config' => array(
					'table' => 'sys_dmail_category',
					'indexField' => 'uid',
				),
				'size' => 5,
				'minitems' => 0,
				'maxitems' => 60,
				'renderMode' => 'checkbox',
				'MM' => 'sys_dmail_feuser_category_mm',
			)
		)
	);
	t3lib_extMgm::addTCAcolumns('fe_users', $tempColumns, 1);
	t3lib_extMgm::addToAllTCAtypes('fe_users', '--div--;Direct mail, module_sys_dmail_html, module_sys_dmail_category');
}



t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist']['sfregister_form'] =
	'layout, select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist']['sfregister_form'] =
	'pi_flexform';
t3lib_extMgm::addPiFlexFormValue('sfregister_form', 'FILE:EXT:sf_register/Configuration/FlexForms/form.xml');

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Form',
	'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tt_content.list_type_form'
);

?>