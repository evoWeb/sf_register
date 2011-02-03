<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}



Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Form',
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm',
		'FeuserEdit' => 'form, preview, proxy, save',
		'FeuserPassword' => 'form, save',
	),
	array(
		'FeuserCreate' => 'form, preview, proxy, save, confirm',
		'FeuserEdit' => 'form, preview, proxy, save',
		'FeuserPassword' => 'form, save',
	)
);

?>