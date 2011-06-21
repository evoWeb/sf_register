<?php

########################################################################
# Extension Manager/Repository config file for ext "sf_register".
#
# Auto generated 30-04-2011 15:55
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
	'version' => '1.3.0',
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'priority' => 'bottom',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => 'typo3temp/sf_register',
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
			'typo3' => '4.5.0-0.0.0',
			'extbase' => '1.3.0-1.3.1',
			'fluid' => '1.3.0-1.3.1',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:66:{s:12:"ext_icon.gif";s:4:"c675";s:17:"ext_localconf.php";s:4:"14c5";s:14:"ext_tables.php";s:4:"8060";s:14:"ext_tables.sql";s:4:"d6e6";s:39:"Classes/Controller/FeuserController.php";s:4:"6c81";s:45:"Classes/Controller/FeuserCreateController.php";s:4:"613a";s:43:"Classes/Controller/FeuserEditController.php";s:4:"c171";s:47:"Classes/Controller/FeuserPasswordController.php";s:4:"0aa8";s:37:"Classes/Domain/Model/FrontendUser.php";s:4:"70eb";s:33:"Classes/Domain/Model/Password.php";s:4:"aea1";s:52:"Classes/Domain/Repository/FrontendUserRepository.php";s:4:"76c3";s:45:"Classes/Domain/Validator/BadWordValidator.php";s:4:"59fa";s:58:"Classes/Domain/Validator/EqualCurrentPasswordValidator.php";s:4:"5b96";s:49:"Classes/Domain/Validator/ImageUploadValidator.php";s:4:"5823";s:44:"Classes/Domain/Validator/IsTrueValidator.php";s:4:"b3dd";s:52:"Classes/Domain/Validator/PasswordsEqualValidator.php";s:4:"eaa0";s:44:"Classes/Domain/Validator/UniqueValidator.php";s:4:"8628";s:42:"Classes/Domain/Validator/UserValidator.php";s:4:"9213";s:25:"Classes/Services/File.php";s:4:"1479";s:25:"Classes/Services/Mail.php";s:4:"58b9";s:40:"Classes/Validation/ValidatorResolver.php";s:4:"a5c3";s:32:"Configuration/FlexForms/form.xml";s:4:"5d37";s:38:"Configuration/TypoScript/constants.txt";s:4:"f0ae";s:34:"Configuration/TypoScript/setup.txt";s:4:"e03e";s:40:"Resources/Private/Language/locallang.xml";s:4:"c91a";s:43:"Resources/Private/Language/locallang_be.xml";s:4:"c955";s:43:"Resources/Private/Language/locallang_ts.xml";s:4:"dcb1";s:43:"Resources/Private/Partials/actionlinks.html";s:4:"1969";s:42:"Resources/Private/Partials/fieldError.html";s:4:"f7b1";s:42:"Resources/Private/Partials/formErrors.html";s:4:"c714";s:60:"Resources/Private/Templates/Email/AdminNotificationMail.html";s:4:"8893";s:74:"Resources/Private/Templates/Email/AdminNotificationMailPostActivation.html";s:4:"d16b";s:73:"Resources/Private/Templates/Email/AdminNotificationMailPreActivation.html";s:4:"0fae";s:59:"Resources/Private/Templates/Email/UserNotificationMail.html";s:4:"0fc1";s:73:"Resources/Private/Templates/Email/UserNotificationMailPostActivation.html";s:4:"6adf";s:72:"Resources/Private/Templates/Email/UserNotificationMailPreActivation.html";s:4:"93f6";s:53:"Resources/Private/Templates/FeuserCreate/Confirm.html";s:4:"30dd";s:50:"Resources/Private/Templates/FeuserCreate/Form.html";s:4:"c1fd";s:53:"Resources/Private/Templates/FeuserCreate/Preview.html";s:4:"d15d";s:50:"Resources/Private/Templates/FeuserCreate/Save.html";s:4:"ce5a";s:48:"Resources/Private/Templates/FeuserEdit/Form.html";s:4:"0fd8";s:51:"Resources/Private/Templates/FeuserEdit/Preview.html";s:4:"cea0";s:48:"Resources/Private/Templates/FeuserEdit/Save.html";s:4:"c9f3";s:52:"Resources/Private/Templates/FeuserPassword/Form.html";s:4:"94cd";s:52:"Resources/Private/Templates/FeuserPassword/Save.html";s:4:"cfb8";s:39:"Resources/Public/Images/progressbar.png";s:4:"af24";s:44:"Resources/Public/JavaScript/passwordmeter.js";s:4:"3e60";s:42:"Resources/Public/JavaScript/sf_register.js";s:4:"4985";s:39:"Resources/Public/Stylesheets/styles.css";s:4:"f7ad";s:41:"Tests/Controller/FeuserControllerTest.php";s:4:"5079";s:47:"Tests/Controller/FeuserCreateControllerTest.php";s:4:"8e5e";s:45:"Tests/Controller/FeuserEditControllerTest.php";s:4:"a960";s:49:"Tests/Controller/FeuserPasswordControllerTest.php";s:4:"8c35";s:39:"Tests/Domain/Model/FrontendUserTest.php";s:4:"0fdc";s:35:"Tests/Domain/Model/PasswordTest.php";s:4:"a849";s:54:"Tests/Domain/Repository/FrontendUserRepositoryTest.php";s:4:"773a";s:47:"Tests/Domain/Validator/BadWordValidatorTest.php";s:4:"279b";s:60:"Tests/Domain/Validator/EqualCurrentPasswordValidatorTest.php";s:4:"28f0";s:51:"Tests/Domain/Validator/ImageUploadValidatorTest.php";s:4:"3f24";s:46:"Tests/Domain/Validator/IsTrueValidatorTest.php";s:4:"5072";s:54:"Tests/Domain/Validator/PasswordsEqualValidatorTest.php";s:4:"ac9a";s:46:"Tests/Domain/Validator/UniqueValidatorTest.php";s:4:"e90f";s:44:"Tests/Domain/Validator/UserValidatorTest.php";s:4:"cc4a";s:27:"Tests/Services/FileTest.php";s:4:"3965";s:27:"Tests/Services/MailTest.php";s:4:"4578";s:42:"Tests/Validation/ValidatorResolverTest.php";s:4:"992f";}',
);

?>