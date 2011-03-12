<?php

########################################################################
# Extension Manager/Repository config file for ext "sf_register".
#
# Auto generated 12-03-2011 22:11
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'FeUser Register',
	'description' => 'Offers the possibility to maintain the fe_user data in frontend by the user self.',
	'category' => 'Sebastian Fischer',
	'shy' => 0,
	'version' => '1.1.2',
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'fe_users',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Sebastian Fischer',
	'author_email' => 'typo3@evoweb.de',
	'author_company' => 'evoweb',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.3.0-0.0.0',
			'extbase' => '1.2.0-1.2.1',
			'fluid' => '1.2.0-1.2.1',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:34:{s:12:"ext_icon.gif";s:4:"c675";s:17:"ext_localconf.php";s:4:"79f0";s:14:"ext_tables.php";s:4:"dd17";s:14:"ext_tables.sql";s:4:"69b6";s:39:"Classes/Controller/FeuserController.php";s:4:"736f";s:45:"Classes/Controller/FeuserCreateController.php";s:4:"e941";s:43:"Classes/Controller/FeuserEditController.php";s:4:"9b5f";s:47:"Classes/Controller/FeuserPasswordController.php";s:4:"d9f3";s:37:"Classes/Domain/Model/FrontendUser.php";s:4:"943b";s:52:"Classes/Domain/Repository/FrontendUserRepository.php";s:4:"4991";s:51:"Classes/Domain/Validator/PasswordAgainValidator.php";s:4:"6a8d";s:47:"Classes/Domain/Validator/PasswordsValidator.php";s:4:"7302";s:42:"Classes/Domain/Validator/UserValidator.php";s:4:"a22b";s:25:"Classes/Services/Mail.php";s:4:"10f6";s:40:"Classes/Validation/ValidatorResolver.php";s:4:"a5c3";s:32:"Configuration/FlexForms/form.xml";s:4:"33dc";s:34:"Configuration/TypoScript/setup.txt";s:4:"8ca3";s:40:"Resources/Private/Language/locallang.xml";s:4:"9746";s:43:"Resources/Private/Language/locallang_be.xml";s:4:"d75d";s:43:"Resources/Private/Partials/actionlinks.html";s:4:"1969";s:42:"Resources/Private/Partials/formErrors.html";s:4:"cc71";s:58:"Resources/Private/Templates/Email/AdminActivationMail.html";s:4:"e867";s:60:"Resources/Private/Templates/Email/AdminNotificationMail.html";s:4:"a4fc";s:57:"Resources/Private/Templates/Email/UserActivationMail.html";s:4:"e867";s:59:"Resources/Private/Templates/Email/UserNotificationMail.html";s:4:"a4fc";s:53:"Resources/Private/Templates/FeuserCreate/Confirm.html";s:4:"30dd";s:50:"Resources/Private/Templates/FeuserCreate/Form.html";s:4:"7939";s:53:"Resources/Private/Templates/FeuserCreate/Preview.html";s:4:"7cab";s:50:"Resources/Private/Templates/FeuserCreate/Save.html";s:4:"ce5a";s:48:"Resources/Private/Templates/FeuserEdit/Form.html";s:4:"e6a2";s:51:"Resources/Private/Templates/FeuserEdit/Preview.html";s:4:"f0ea";s:48:"Resources/Private/Templates/FeuserEdit/Save.html";s:4:"c9f3";s:52:"Resources/Private/Templates/FeuserPassword/Form.html";s:4:"c6ca";s:52:"Resources/Private/Templates/FeuserPassword/Save.html";s:4:"cfb8";}',
);

?>