<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Form',
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserEdit' => 'form, preview, proxy, save, removeImage',
		'FeuserPassword' => 'form, save',
	),
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm, removeImage',
		'FeuserEdit' => 'form, preview, proxy, save, removeImage',
		'FeuserPassword' => 'form, save',
	)
);

?>