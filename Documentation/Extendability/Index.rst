.. include:: ../Includes.txt


.. _extendability:

Extendability
=============

.. contents::
   :local:
   :depth: 1


.. _CreateCustomFields:
Use your own flavor of fields
-----------------------------

Since version 8.8.0 its easier then ever to user your own fields. Just add your partial folder via typoscript and
register your own field configuration. After that you need to tell the user ts config that the new type is available to
be selected in the plugin too.

**Fields.typoscript**::

   plugin.tx_sfregister.view.partialRootPaths.50 = EXT:your_extension/Resources/Private/Partials/
   plugin.tx_sfregister.settings.fields.configuration {
      your_field_key {
         partial = YourFieldKey
         backendLabel = LLL:EXT:your_extension/Resources/Private/Language/locallang_be.xlf:your_field_key
      }
   }


**ext_localconf.php**::

   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
   '@import \'EXT:sf_register/Configuration/TypoScript/Fields.typoscript\''
   );


**TypoScript Setup**::

   @import 'EXT:sf_register/Configuration/TypoScript/Fields.typoscript'


By using the same fields file both in typoscript as well as in user ts config. No additional configuration is needed.

In your partials there are the following information available

* {user} the user object with previous entered values
* {fieldName} the name of the field in the user object
* {options} every value that is inside of the field config {partial, backendLabel, etc}
* {settings} the general plugin settings


.. _AddCustomProperties:
Adding custom properties
------------------------

Since late the frontend user domain model can be extended. This can be done the extension 'extender_'
which sole purpose is to extend extbase domain models. There is an example_ on how to use the extender.

If you run into problems extending please be aware that the only solution supported is by the use of 'extender'.

In this example its noteworthy that the last array key is not required but adviced.

The path to the file matches extension key and extbase compatible path to the domain model.

For highlighting purpose its adviced to let the domain model extend from \\Evoweb\\SfRegister\\Domain\\Model\\FrontendUser

**ext_localconf.php**::

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sf_register']['extender']['FrontendUser']['sfregister_extended'] =
	'EXT:sfregister_extended/Classes/Domain/Model/FrontendUser.php';


Beside extending the domain model with property and get-/set-method a field needs to be
created for sql and registered in TCA.

**ext_tables.sql**::

	#
	# Table structure for table 'fe_users'
	#
	CREATE TABLE fe_users (
		extending varchar(60) DEFAULT '',
	);


**TCA/Overrides/fe_users.php**::

	$temporaryColumns = array(
		'extending' => array(
			'exclude' => 1,
			'label' => 'extending',
			'config' => array(
				'type' => 'input',
				'readOnly' => TRUE,
			)
		),
	);

	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns, 1);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'extending');


.. _AddCustomCaptcha:
Bring in your own captcha
-------------------------

By implementing a adapter which is extending the \\Evoweb\\SfRegister\\Services\\Captcha\\AbstractAdapter you are able
to add an own captcha. The adapter now then has to be configured to be usable by adding typoscript settings like the
following taken from recaptcha_:

**Captcha.typoscript**::

   plugin.tx_sfregister.settings {
      # register recaptcha as captcha possibility
      captcha.recaptcha = Evoweb\Recaptcha\Adapter\SfRegisterAdapter

      fields {
         configuration {
            # change captcha field type to recaptcha
            captcha.type = Recaptcha
         }
      }

      validation.create {
         # tell validation to use recaptcha adapter
         captcha = Evoweb\SfRegister\Validation\Validator\CaptchaValidator(type = recaptcha)
      }
   }


.. _Psr14Event:

PSR-14 Events
-------------

This kind of event is superseding Hooks and Signal-Slots in TYPO3 and are the
way to go. That's why all signals are replaced with their event counterparts.


How to implement a slot
-----------------------

An overview_ on how to configure and interact with events was given on the
Developer Days in 2019. The detailed example shows how to configure them in the
Services.yaml:

**Services.yaml**::

  Evoweb\SfRegister\EventListener\FeuserControllerListener:
    tags:
      - name: event.listener
        identifier: 'sfregister_feusercontroller_processinitializeaction'
        method: 'onProcessInitializeActionEvent'
        event: Evoweb\SfRegister\Controller\Event\ProcessInitializeActionEvent


The code above shows how to get an event listener is registered to an event.


Available events
----------------

+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| Event                                                                 | Parameter                                                            |
+=======================================================================+======================================================================+
| :php:`Evoweb\SfRegister\Controller\Event\InitializeActionEvent`       | :php:`FeuserController`, `array`                                     |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| FeuserCreateController                                                |                                                                      |
| :php:`Evoweb\SfRegister\Controller\Event\CreateFormEvent`             | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\CreatePreviewEvent`          | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\CreateSaveEvent`             | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\CreateConfirmEvent`          | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\CreateRefuseEvent`           | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\CreateAcceptEvent`           | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\CreateDeclineEvent`          | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| FeuserDeleteController                                                |                                                                      |
| :php:`Evoweb\SfRegister\Controller\Event\DeleteFormEvent`             | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\DeleteSaveEvent`             | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\DeleteConfirmEvent`          | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| FeuserEditController                                                  |                                                                      |
| :php:`Evoweb\SfRegister\Controller\Event\EditFormEvent`               | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\EditPreviewEvent`            | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\EditSaveEvent`               | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\EditConfirmEvent`            | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\EditAcceptEvent`             | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| FeuserInviteController                                                |                                                                      |
| :php:`Evoweb\SfRegister\Controller\Event\InviteFormEvent`             | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
| :php:`Evoweb\SfRegister\Controller\Event\InviteInviteEvent`           | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `bool`  |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| FeuserPasswordController                                              |                                                                      |
| :php:`Evoweb\SfRegister\Controller\Event\PasswordFormEvent`           | :php:`array`                                                         |
| :php:`Evoweb\SfRegister\Controller\Event\PasswordSaveEvent`           | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`          |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| FeuserResendController                                                |                                                                      |
| :php:`Evoweb\SfRegister\Controller\Event\ResendFormEvent`             | :php:`Evoweb\SfRegister\Domain\Model\Email`, `array`                 |
| :php:`Evoweb\SfRegister\Controller\Event\ResendMailEvent`             | :php:`Evoweb\SfRegister\Domain\Model\Email`, `array`                 |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminCreateAcceptEvent`  | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminCreateConfirmEvent` | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminCreateDeclineEvent` | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminCreateRefuseEvent`  | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminCreateSaveEvent`    | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminDeleteConfirmEvent` | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminDeleteSaveEvent`    | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminEditAcceptEvent`    | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminEditConfirmEvent`   | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminEditSaveEvent`      | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminInviteInviteEvent`  | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyAdminResendMailEvent`    | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserCreateAcceptEvent`   | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserCreateConfirmEvent`  | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserCreateDeclineEvent`  | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserCreateRefuseEvent`   | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserCreateSaveEvent`     | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserDeleteConfirmEvent`  | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserDeleteSaveEvent`     | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserEditAcceptEvent`     | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserEditConfirmEvent`    | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserEditSaveEvent`       | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserInviteInviteEvent`   | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\NotifyUserResendMailEvent`     | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\InvitationToRegisterEvent`     | :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`, `array`, `array` |
| :php:`Evoweb\SfRegister\Services\Event\PreSubmitMailEvent`            | :php:`TYPO3\CMS\Core\Mail\MailMessage`, `array`, `array`             |
+-----------------------------------------------------------------------+----------------------------------------------------------------------+


.. _extender: https://github.com/evoWeb/extender
.. _recaptcha: https://github.com/evoWeb/recaptcha
.. _example: https://github.com/evoWeb/ew_sfregister_extended
.. _overview: https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Hooks/EventDispatcher/Index.html
