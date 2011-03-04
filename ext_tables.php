<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}



t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/', 'Feuser Register');



t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addPageTSConfig('
TCAdefaults.fe_users.tx_extbase_type = Tx_Extbase_Domain_Model_FrontendUser
');

$tempColumns = array (
	'mailhash' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.mailhash',
		'config' => array (
			'type' => 'input',
			'readOnly' => true,
		)
	),
	'mobilephone' => array (
		'exclude' => 1,
		'label' => 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xml:fe_users.mobilephone',
		'config' => array (
			'type' => 'input',
			'size' => 20,
		)
	),
);
t3lib_extMgm::addTCAcolumns('fe_users', $tempColumns, 1);
t3lib_extMgm::addToAllTCAtypes('fe_users', 'mobilephone', '', 'after:telephone');
t3lib_extMgm::addToAllTCAtypes('fe_users', 'mailhash');



t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist']['sfregister_form'] =
	'layout, select_key, pages, recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist']['sfregister_form'] =
	'pi_flexform';
t3lib_extMgm::addPiFlexFormValue('sfregister_form', 'FILE:EXT:sf_register/Configuration/FlexForms/form.xml');

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Form',
	'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xml:tt_content.list_type_form'
);

?>