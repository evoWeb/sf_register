.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _extendability:

Extendability
=============

.. contents::
   :local:
   :depth: 1


Adding custom fields
--------------------

Since late the frontend user domain model can be extended. This can be done the extension 'extender'
which sole purpose is to extend extbase domain models.

If you run into problems extending please be aware that the only solution supported is by the use of 'extender'.

In this example its noteworthy that the last array key is not required but adviced.

The path to the file matches extension key and extbase compatible path to the domain model.

For highlighting purpose its adviced to let the domain model extend from \\Evoweb\\SfRegister\\Domain\\Model\\FrontendUser

**ext_localconf.php**::

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sf_register']['extender']['FrontendUser']['sfregister_extended'] =
	'EXT:ew_sfregister_extended/Classes/Domain/Model/FrontendUser.php';


Beside extending the domain model with property and get-/set-method a field needs to be
created for sql and registered in TCA.

**ext_tables.sql**::

	#
	# Table structure for table 'fe_users'
	#
	CREATE TABLE fe_users (
		extending varchar(60) DEFAULT '',
	);


**ext_tables.php**::

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

	t3lib_extMgm::addTCAcolumns('fe_users', $temporaryColumns, 1);
	t3lib_extMgm::addToAllTCAtypes('fe_users', 'extending');


Note of compatabilty
____________________

There is an internal extending solution which will be deleted in early 2015. There for the example provided
here is only for documentation and should not be used.

**ext_localconf.php**::

	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sf_register']['entities']['FrontendUser']['sfregister_extended'] =
	'EXT:sfregister_extended/Classes/Domain/Model/FrontendUser.php';


Since extending the model is possible now the field custom0 to custom9 will be dropped early 2015.
Please switch to extending the domain model if you need custom fields.


.. _SignalSlotDispatcher:

Hooks / Signal-Slot-Dispatcher
------------------------------

Because of signal-slot-dispatcher are the new hotness all hooks got replaced with
the call of this dispatcher. Well its not really this simple, but as signal-slot-dispatcher
are the extbase way of giving the opportunity to have some custom methods called
at a certain process, this will be the way to go in the future. Beside of obeying
this paradigm there are a lot more dispatcher call spread across the different
tasks.


How to implement a slot
-----------------------

As the different tasks emits signals there could be slots that fulfill them. To
have your own slots please understand how slots_ work.
After you read that introduction, here is a short example:

.. _slots: http://blog.foertel.com/2011/10/using-signalslots-in-extbase/

**ext_localconf.php**::

	/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class)
		->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
	$signalSlotDispatcher->connect(
		\Evoweb\SfRegister\Controller\FeuserCreateController::class,
		'formAction',
		'ExampleClassName',
		'ExampleMethodName',
		TRUE
	);


The code above show how to get an instance of the signal slot dispatcher and
then connect a slot for form action in the frontend user create controller to
your own slot with ExampleClassName and ExampleMethodName.
Its possible to have a optional fifth parameter that hands the information
about the calling signal to the slot. This would be useful if you want to
handle multiple signals with only one defined slot. Although this is possible
it's also highly discourage, because the scope is lost to easily.

Available signals
-----------------

All classnames need to be fully qualified. So please prefix all controllers
with Evoweb\\SfRegister\\Controller\\ and all services with
Evoweb\\SfRegister\\Services\\

.. container:: ts-properties

  ===================================================== ============================================================ =================================
  Class                                                 Method                                                       Parameter
  ===================================================== ============================================================ =================================
  FeuserCreateController                                formAction                                                   user, settings
  FeuserCreateController                                previewAction                                                user, settings
  FeuserCreateController                                saveAction                                                   user, settings
  FeuserCreateController                                confirmAction                                                user, settings
  FeuserCreateController                                refuseAction                                                 user, settings
  FeuserCreateController                                acceptAction                                                 user, settings
  FeuserCreateController                                declineAction                                                user, settings
  ===================================================== ============================================================ =================================

.. container:: ts-properties

  ===================================================== ============================================================ =================================
  Class                                                 Method                                                       Parameter
  ===================================================== ============================================================ =================================
  FeuserEditController                                  formAction                                                   user, settings
  FeuserEditController                                  previewAction                                                user, settings
  FeuserEditController                                  saveAction                                                   user, settings
  FeuserEditController                                  confirmAction                                                user, settings
  FeuserEditController                                  acceptAction                                                 user, settings
  ===================================================== ============================================================ =================================

.. container:: ts-properties

  ===================================================== ============================================================ =================================
  Class                                                 Method                                                       Parameter
  ===================================================== ============================================================ =================================
  FeuserPasswordController                              formAction                                                   settings
  FeuserPasswordController                              saveAction                                                   settings
  ===================================================== ============================================================ =================================

.. container:: ts-properties

  +----------------------------------------------------+------------------------------------------------------------+----------------------------------+
  | Class                                              | Method                                                     | Parameter                        |
  +====================================================+============================================================+==================================+
  | Services\Login                                     | initFEuser                                                 | frontend                         |
  +----------------------------------------------------+------------------------------------------------------------+----------------------------------+
  | Services\Mail                                      | sendAdminNotificationPostCreateSavePostSend                | result, arguments[mail, user,    |
  |                                                    +------------------------------------------------------------+ settings, objectManager]         |
  |                                                    | sendUserNotificationPostCreateSavePostSend                 |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendAdminNotificationPostCreateConfirmPostSend             |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendUserNotificationPostCreateConfirmPostSend              |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendAdminNotificationPostCreateRefusePostSend              |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendUserNotificationPostCreateRefusePostSend               |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendAdminNotificationPostCreateAcceptPostSend              |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendUserNotificationPostCreateAcceptPostSend               |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendAdminNotificationPostCreateDeclinePostSend             |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendUserNotificationPostCreateDeclinePostSend              |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendAdminNotificationPostEditSavePostSend                  |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendUserNotificationPostEditSavePostSend                   |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendAdminNotificationPostEditConfirmPostSend               |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendUserNotificationPostEditConfirmPostSend                |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendAdminNotificationPostEditAcceptPostSend                |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendUserNotificationPostEditAcceptPostSend                 |                                  |
  |                                                    +------------------------------------------------------------+                                  |
  |                                                    | sendMailPreSend                                            |                                  |
  +----------------------------------------------------+------------------------------------------------------------+----------------------------------+




.. _CreateCustomValidators:

Bring in your own captcha
-------------------------
