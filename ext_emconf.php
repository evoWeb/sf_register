<?php

########################################################################
# Extension Manager/Repository config file for ext "sf_register".
#
# Auto generated 19-07-2011 09:32
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
	'version' => '1.4.0',
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
			'php' => '5.3.0-0.0.0',
			'typo3' => '4.5.0-0.0.0',
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:104:{s:21:"ext_conf_template.txt";s:4:"1623";s:12:"ext_icon.gif";s:4:"c675";s:17:"ext_localconf.php";s:4:"65dc";s:14:"ext_tables.php";s:4:"45f8";s:14:"ext_tables.sql";s:4:"2100";s:20:"Classes/Api/Ajax.php";s:4:"6a61";s:39:"Classes/Controller/FeuserController.php";s:4:"6ae9";s:45:"Classes/Controller/FeuserCreateController.php";s:4:"628c";s:43:"Classes/Controller/FeuserEditController.php";s:4:"6bfe";s:47:"Classes/Controller/FeuserPasswordController.php";s:4:"04ec";s:37:"Classes/Domain/Model/FrontendUser.php";s:4:"0251";s:33:"Classes/Domain/Model/Password.php";s:4:"9e15";s:38:"Classes/Domain/Model/StaticCountry.php";s:4:"bf58";s:42:"Classes/Domain/Model/StaticCountryZone.php";s:4:"62f4";s:39:"Classes/Domain/Model/StaticLanguage.php";s:4:"adc8";s:52:"Classes/Domain/Repository/FrontendUserRepository.php";s:4:"7e71";s:53:"Classes/Domain/Repository/StaticCountryRepository.php";s:4:"86bf";s:57:"Classes/Domain/Repository/StaticCountryZoneRepository.php";s:4:"86e6";s:54:"Classes/Domain/Repository/StaticLanguageRepository.php";s:4:"d38b";s:45:"Classes/Domain/Validator/BadWordValidator.php";s:4:"f617";s:45:"Classes/Domain/Validator/CaptchaValidator.php";s:4:"8662";s:43:"Classes/Domain/Validator/EmptyValidator.php";s:4:"2403";s:58:"Classes/Domain/Validator/EqualCurrentPasswordValidator.php";s:4:"17f9";s:54:"Classes/Domain/Validator/EqualCurrentUserValidator.php";s:4:"898d";s:49:"Classes/Domain/Validator/ImageUploadValidator.php";s:4:"8706";s:44:"Classes/Domain/Validator/IsTrueValidator.php";s:4:"4939";s:44:"Classes/Domain/Validator/RepeatValidator.php";s:4:"7fba";s:46:"Classes/Domain/Validator/RequiredValidator.php";s:4:"ef10";s:44:"Classes/Domain/Validator/UniqueValidator.php";s:4:"19f2";s:42:"Classes/Domain/Validator/UserValidator.php";s:4:"ad78";s:30:"Classes/Interfaces/Captcha.php";s:4:"91db";s:35:"Classes/Interfaces/FrontendUser.php";s:4:"6cd3";s:25:"Classes/Services/File.php";s:4:"6f12";s:25:"Classes/Services/Hook.php";s:4:"7fb9";s:26:"Classes/Services/Login.php";s:4:"57f9";s:25:"Classes/Services/Mail.php";s:4:"32cd";s:28:"Classes/Services/Session.php";s:4:"4190";s:44:"Classes/Services/Captcha/AbstractAdapter.php";s:4:"8dbe";s:50:"Classes/Services/Captcha/CaptchaAdapterFactory.php";s:4:"4344";s:47:"Classes/Services/Captcha/JmRecaptchaAdapter.php";s:4:"98a9";s:45:"Classes/Services/Captcha/SrFreecapAdapter.php";s:4:"f181";s:30:"Classes/Utility/WizardIcon.php";s:4:"56c3";s:40:"Classes/Validation/ValidatorResolver.php";s:4:"f3a0";s:46:"Classes/ViewHelpers/Form/CaptchaViewHelper.php";s:4:"6c51";s:50:"Classes/ViewHelpers/Form/RangeSelectViewHelper.php";s:4:"27ad";s:47:"Classes/ViewHelpers/Form/RequiredViewHelper.php";s:4:"22d5";s:60:"Classes/ViewHelpers/Form/SelectStaticCountriesViewHelper.php";s:4:"74de";s:63:"Classes/ViewHelpers/Form/SelectStaticCountryZonesViewHelper.php";s:4:"dc1a";s:59:"Classes/ViewHelpers/Form/SelectStaticLanguageViewHelper.php";s:4:"a145";s:51:"Classes/ViewHelpers/Form/SelectStaticViewHelper.php";s:4:"21c9";s:32:"Configuration/FlexForms/form.xml";s:4:"68f3";s:38:"Configuration/Realurl/realurl_conf.php";s:4:"ebe7";s:46:"Configuration/TypoScript/maximal/constants.txt";s:4:"111f";s:42:"Configuration/TypoScript/maximal/setup.txt";s:4:"7816";s:46:"Configuration/TypoScript/minimal/constants.txt";s:4:"1cb4";s:42:"Configuration/TypoScript/minimal/setup.txt";s:4:"c1ee";s:40:"Resources/Private/Language/locallang.xml";s:4:"7b40";s:43:"Resources/Private/Language/locallang_be.xml";s:4:"0dd6";s:43:"Resources/Private/Language/locallang_ts.xml";s:4:"8e3c";s:50:"Resources/Private/Partials/captchaJmRecaptcha.html";s:4:"f60e";s:48:"Resources/Private/Partials/captchaSrFreecap.html";s:4:"33d2";s:42:"Resources/Private/Partials/fieldError.html";s:4:"3e25";s:42:"Resources/Private/Partials/formErrors.html";s:4:"c714";s:40:"Resources/Private/Partials/required.html";s:4:"73a8";s:46:"Resources/Private/Partials/selectTimezone.html";s:4:"80f7";s:67:"Resources/Private/Templates/Email/AdminActivationMailAfterEdit.html";s:4:"0fae";s:60:"Resources/Private/Templates/Email/AdminNotificationMail.html";s:4:"8893";s:69:"Resources/Private/Templates/Email/AdminNotificationMailAfterEdit.html";s:4:"8893";s:74:"Resources/Private/Templates/Email/AdminNotificationMailPostActivation.html";s:4:"d16b";s:73:"Resources/Private/Templates/Email/AdminNotificationMailPreActivation.html";s:4:"0fae";s:66:"Resources/Private/Templates/Email/UserActivationMailAfterEdit.html";s:4:"93f6";s:59:"Resources/Private/Templates/Email/UserNotificationMail.html";s:4:"0fc1";s:68:"Resources/Private/Templates/Email/UserNotificationMailAfterEdit.html";s:4:"0fc1";s:73:"Resources/Private/Templates/Email/UserNotificationMailPostActivation.html";s:4:"6adf";s:72:"Resources/Private/Templates/Email/UserNotificationMailPreActivation.html";s:4:"93f6";s:53:"Resources/Private/Templates/FeuserCreate/Confirm.html";s:4:"8ba0";s:50:"Resources/Private/Templates/FeuserCreate/Form.html";s:4:"f742";s:53:"Resources/Private/Templates/FeuserCreate/Preview.html";s:4:"eeef";s:50:"Resources/Private/Templates/FeuserCreate/Save.html";s:4:"ce5a";s:48:"Resources/Private/Templates/FeuserEdit/Form.html";s:4:"d6e2";s:51:"Resources/Private/Templates/FeuserEdit/Preview.html";s:4:"cea0";s:48:"Resources/Private/Templates/FeuserEdit/Save.html";s:4:"c9f3";s:52:"Resources/Private/Templates/FeuserPassword/Form.html";s:4:"6e37";s:52:"Resources/Private/Templates/FeuserPassword/Save.html";s:4:"cfb8";s:39:"Resources/Public/Images/progressbar.png";s:4:"af24";s:44:"Resources/Public/JavaScript/passwordmeter.js";s:4:"9635";s:42:"Resources/Public/JavaScript/sf_register.js";s:4:"da42";s:39:"Resources/Public/Stylesheets/styles.css";s:4:"a65a";s:41:"Tests/Controller/FeuserControllerTest.php";s:4:"5079";s:47:"Tests/Controller/FeuserCreateControllerTest.php";s:4:"8e5e";s:45:"Tests/Controller/FeuserEditControllerTest.php";s:4:"a960";s:49:"Tests/Controller/FeuserPasswordControllerTest.php";s:4:"8c35";s:39:"Tests/Domain/Model/FrontendUserTest.php";s:4:"0fdc";s:35:"Tests/Domain/Model/PasswordTest.php";s:4:"6d5b";s:54:"Tests/Domain/Repository/FrontendUserRepositoryTest.php";s:4:"98fb";s:47:"Tests/Domain/Validator/BadWordValidatorTest.php";s:4:"d008";s:60:"Tests/Domain/Validator/EqualCurrentPasswordValidatorTest.php";s:4:"5eb6";s:51:"Tests/Domain/Validator/ImageUploadValidatorTest.php";s:4:"3f24";s:46:"Tests/Domain/Validator/IsTrueValidatorTest.php";s:4:"5072";s:46:"Tests/Domain/Validator/UniqueValidatorTest.php";s:4:"85b7";s:44:"Tests/Domain/Validator/UserValidatorTest.php";s:4:"cc4a";s:27:"Tests/Services/FileTest.php";s:4:"3965";s:27:"Tests/Services/MailTest.php";s:4:"4578";s:42:"Tests/Validation/ValidatorResolverTest.php";s:4:"992f";}',
);

?>