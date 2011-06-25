<?php

########################################################################
# Extension Manager/Repository config file for ext "sf_register".
#
# Auto generated 25-06-2011 08:19
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
	'_md5_values_when_last_written' => 'a:97:{s:21:"ext_conf_template.txt";s:4:"1623";s:12:"ext_icon.gif";s:4:"c675";s:17:"ext_localconf.php";s:4:"ab1d";s:14:"ext_tables.php";s:4:"905f";s:14:"ext_tables.sql";s:4:"2100";s:20:"Classes/Api/Ajax.php";s:4:"c167";s:39:"Classes/Controller/FeuserController.php";s:4:"1b59";s:45:"Classes/Controller/FeuserCreateController.php";s:4:"f66c";s:43:"Classes/Controller/FeuserEditController.php";s:4:"384e";s:47:"Classes/Controller/FeuserPasswordController.php";s:4:"0aa8";s:37:"Classes/Domain/Model/FrontendUser.php";s:4:"f98a";s:33:"Classes/Domain/Model/Password.php";s:4:"9fbd";s:38:"Classes/Domain/Model/StaticCountry.php";s:4:"d9fa";s:42:"Classes/Domain/Model/StaticCountryZone.php";s:4:"187e";s:39:"Classes/Domain/Model/StaticLanguage.php";s:4:"ca26";s:52:"Classes/Domain/Repository/FrontendUserRepository.php";s:4:"76c3";s:53:"Classes/Domain/Repository/StaticCountryRepository.php";s:4:"806f";s:57:"Classes/Domain/Repository/StaticCountryZoneRepository.php";s:4:"a511";s:54:"Classes/Domain/Repository/StaticLanguageRepository.php";s:4:"31a1";s:45:"Classes/Domain/Validator/BadWordValidator.php";s:4:"59fa";s:45:"Classes/Domain/Validator/CaptchaValidator.php";s:4:"7654";s:43:"Classes/Domain/Validator/EmptyValidator.php";s:4:"2403";s:58:"Classes/Domain/Validator/EqualCurrentPasswordValidator.php";s:4:"5b96";s:54:"Classes/Domain/Validator/EqualCurrentUserValidator.php";s:4:"b488";s:49:"Classes/Domain/Validator/ImageUploadValidator.php";s:4:"5823";s:44:"Classes/Domain/Validator/IsTrueValidator.php";s:4:"b3dd";s:44:"Classes/Domain/Validator/RepeatValidator.php";s:4:"dfb6";s:46:"Classes/Domain/Validator/RequiredValidator.php";s:4:"ef10";s:44:"Classes/Domain/Validator/UniqueValidator.php";s:4:"8628";s:42:"Classes/Domain/Validator/UserValidator.php";s:4:"a707";s:30:"Classes/Interfaces/Captcha.php";s:4:"5b50";s:35:"Classes/Interfaces/FrontendUser.php";s:4:"fde2";s:25:"Classes/Services/File.php";s:4:"1479";s:26:"Classes/Services/Login.php";s:4:"c218";s:25:"Classes/Services/Mail.php";s:4:"321c";s:44:"Classes/Services/Captcha/AbstractAdapter.php";s:4:"4f95";s:50:"Classes/Services/Captcha/CaptchaAdapterFactory.php";s:4:"91fc";s:47:"Classes/Services/Captcha/JmRecaptchaAdapter.php";s:4:"6eb6";s:45:"Classes/Services/Captcha/SrFreecapAdapter.php";s:4:"18fd";s:40:"Classes/Validation/ValidatorResolver.php";s:4:"66b8";s:46:"Classes/ViewHelpers/Form/CaptchaViewHelper.php";s:4:"af43";s:47:"Classes/ViewHelpers/Form/RequiredViewHelper.php";s:4:"c025";s:60:"Classes/ViewHelpers/Form/SelectStaticCountriesViewHelper.php";s:4:"53cb";s:63:"Classes/ViewHelpers/Form/SelectStaticCountryZonesViewHelper.php";s:4:"b15f";s:59:"Classes/ViewHelpers/Form/SelectStaticLanguageViewHelper.php";s:4:"af37";s:51:"Classes/ViewHelpers/Form/SelectStaticViewHelper.php";s:4:"3fa7";s:32:"Configuration/FlexForms/form.xml";s:4:"68f3";s:38:"Configuration/Realurl/realurl_conf.php";s:4:"2705";s:46:"Configuration/TypoScript/maximal/constants.txt";s:4:"5127";s:42:"Configuration/TypoScript/maximal/setup.txt";s:4:"faa4";s:46:"Configuration/TypoScript/minimal/constants.txt";s:4:"1cb4";s:42:"Configuration/TypoScript/minimal/setup.txt";s:4:"32a7";s:40:"Resources/Private/Language/locallang.xml";s:4:"90c6";s:43:"Resources/Private/Language/locallang_be.xml";s:4:"69f6";s:43:"Resources/Private/Language/locallang_ts.xml";s:4:"f6f9";s:50:"Resources/Private/Partials/captchaJmRecaptcha.html";s:4:"5f82";s:48:"Resources/Private/Partials/captchaSrFreecap.html";s:4:"de11";s:42:"Resources/Private/Partials/fieldError.html";s:4:"3e25";s:42:"Resources/Private/Partials/formErrors.html";s:4:"c714";s:40:"Resources/Private/Partials/required.html";s:4:"73a8";s:46:"Resources/Private/Partials/selectTimezone.html";s:4:"80f7";s:60:"Resources/Private/Templates/Email/AdminNotificationMail.html";s:4:"8893";s:74:"Resources/Private/Templates/Email/AdminNotificationMailPostActivation.html";s:4:"d16b";s:73:"Resources/Private/Templates/Email/AdminNotificationMailPreActivation.html";s:4:"0fae";s:59:"Resources/Private/Templates/Email/UserNotificationMail.html";s:4:"0fc1";s:73:"Resources/Private/Templates/Email/UserNotificationMailPostActivation.html";s:4:"6adf";s:72:"Resources/Private/Templates/Email/UserNotificationMailPreActivation.html";s:4:"93f6";s:53:"Resources/Private/Templates/FeuserCreate/Confirm.html";s:4:"30dd";s:50:"Resources/Private/Templates/FeuserCreate/Form.html";s:4:"7971";s:53:"Resources/Private/Templates/FeuserCreate/Preview.html";s:4:"6663";s:50:"Resources/Private/Templates/FeuserCreate/Save.html";s:4:"ce5a";s:48:"Resources/Private/Templates/FeuserEdit/Form.html";s:4:"28c9";s:51:"Resources/Private/Templates/FeuserEdit/Preview.html";s:4:"cea0";s:48:"Resources/Private/Templates/FeuserEdit/Save.html";s:4:"c9f3";s:52:"Resources/Private/Templates/FeuserPassword/Form.html";s:4:"6e37";s:52:"Resources/Private/Templates/FeuserPassword/Save.html";s:4:"cfb8";s:39:"Resources/Public/Images/progressbar.png";s:4:"af24";s:44:"Resources/Public/JavaScript/passwordmeter.js";s:4:"3e60";s:42:"Resources/Public/JavaScript/sf_register.js";s:4:"71dd";s:39:"Resources/Public/Stylesheets/styles.css";s:4:"a65a";s:41:"Tests/Controller/FeuserControllerTest.php";s:4:"5079";s:47:"Tests/Controller/FeuserCreateControllerTest.php";s:4:"8e5e";s:45:"Tests/Controller/FeuserEditControllerTest.php";s:4:"a960";s:49:"Tests/Controller/FeuserPasswordControllerTest.php";s:4:"8c35";s:39:"Tests/Domain/Model/FrontendUserTest.php";s:4:"0fdc";s:35:"Tests/Domain/Model/PasswordTest.php";s:4:"a849";s:54:"Tests/Domain/Repository/FrontendUserRepositoryTest.php";s:4:"773a";s:47:"Tests/Domain/Validator/BadWordValidatorTest.php";s:4:"279b";s:60:"Tests/Domain/Validator/EqualCurrentPasswordValidatorTest.php";s:4:"28f0";s:51:"Tests/Domain/Validator/ImageUploadValidatorTest.php";s:4:"3f24";s:46:"Tests/Domain/Validator/IsTrueValidatorTest.php";s:4:"5072";s:54:"Tests/Domain/Validator/PasswordsEqualValidatorTest.php";s:4:"ac9a";s:46:"Tests/Domain/Validator/UniqueValidatorTest.php";s:4:"e90f";s:44:"Tests/Domain/Validator/UserValidatorTest.php";s:4:"cc4a";s:27:"Tests/Services/FileTest.php";s:4:"3965";s:27:"Tests/Services/MailTest.php";s:4:"4578";s:42:"Tests/Validation/ValidatorResolverTest.php";s:4:"992f";}',
);

?>