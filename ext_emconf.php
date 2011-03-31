<?php

########################################################################
# Extension Manager/Repository config file for ext "sf_register".
#
# Auto generated 24-03-2011 09:35
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
	'version' => '1.1.3',
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
	'_md5_values_when_last_written' => 'a:39:{s:12:"ext_icon.gif";s:4:"c675";s:17:"ext_localconf.php";s:4:"79f0";s:14:"ext_tables.php";s:4:"f261";s:14:"ext_tables.sql";s:4:"536e";s:39:"Classes/Controller/FeuserController.php";s:4:"4966";s:45:"Classes/Controller/FeuserCreateController.php";s:4:"8da1";s:43:"Classes/Controller/FeuserEditController.php";s:4:"4aa0";s:47:"Classes/Controller/FeuserPasswordController.php";s:4:"d9f3";s:37:"Classes/Domain/Model/FrontendUser.php";s:4:"9d9b";s:52:"Classes/Domain/Repository/FrontendUserRepository.php";s:4:"7947";s:49:"Classes/Domain/Validator/ImageUploadValidator.php";s:4:"7dd4";s:44:"Classes/Domain/Validator/IsTrueValidator.php";s:4:"e10f";s:51:"Classes/Domain/Validator/PasswordsEqualValidator.php";s:4:"6a8d";s:47:"Classes/Domain/Validator/PasswordsValidator.php";s:4:"7302";s:44:"Classes/Domain/Validator/UniqueValidator.php";s:4:"31d9";s:42:"Classes/Domain/Validator/UserValidator.php";s:4:"e952";s:25:"Classes/Services/File.php";s:4:"d130";s:25:"Classes/Services/Mail.php";s:4:"6c8b";s:40:"Classes/Validation/ValidatorResolver.php";s:4:"a5c3";s:32:"Configuration/FlexForms/form.xml";s:4:"33dc";s:34:"Configuration/TypoScript/setup.txt";s:4:"5cba";s:40:"Resources/Private/Language/locallang.xml";s:4:"42b6";s:43:"Resources/Private/Language/locallang_be.xml";s:4:"6055";s:43:"Resources/Private/Partials/actionlinks.html";s:4:"1969";s:42:"Resources/Private/Partials/fieldError.html";s:4:"f7b1";s:42:"Resources/Private/Partials/formErrors.html";s:4:"c714";s:58:"Resources/Private/Templates/Email/AdminActivationMail.html";s:4:"e867";s:60:"Resources/Private/Templates/Email/AdminNotificationMail.html";s:4:"a4fc";s:57:"Resources/Private/Templates/Email/UserActivationMail.html";s:4:"e867";s:59:"Resources/Private/Templates/Email/UserNotificationMail.html";s:4:"a4fc";s:53:"Resources/Private/Templates/FeuserCreate/Confirm.html";s:4:"30dd";s:50:"Resources/Private/Templates/FeuserCreate/Form.html";s:4:"14f2";s:53:"Resources/Private/Templates/FeuserCreate/Preview.html";s:4:"6c5f";s:50:"Resources/Private/Templates/FeuserCreate/Save.html";s:4:"ce5a";s:48:"Resources/Private/Templates/FeuserEdit/Form.html";s:4:"999e";s:51:"Resources/Private/Templates/FeuserEdit/Preview.html";s:4:"f0ea";s:48:"Resources/Private/Templates/FeuserEdit/Save.html";s:4:"c9f3";s:52:"Resources/Private/Templates/FeuserPassword/Form.html";s:4:"c6ca";s:52:"Resources/Private/Templates/FeuserPassword/Save.html";s:4:"cfb8";}',
);

?>