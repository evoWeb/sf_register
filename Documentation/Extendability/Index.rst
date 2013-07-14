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

Due to lacking support from extbase to extend models its currently not possible
to add custom fields. To provide a replacement for this there are a bunch of
fields without any configuration. These fields are named custom[0-9] and are of
type string to have brought value support. Still these are not able to have
objects assigned to them.


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
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager')
		->get('TYPO3\CMS\Extbase\SignalSlot\Dispatcher');
	$signalSlotDispatcher->connect('FeuserCreateController', 'formAction', 'ExampleClassName', 'ExampleMethodName', [TRUE]);


The code above show how to get an instance of the signal slot dispatcher and
then connect a slot for form action in the frontend user create controller to
your own slot with ExampleClassName and ExampleMethodName.
Its possible to have a optional fifth parameter that hands the information
about the calling signal to the slot. This would be useful if you want to
handle multiple signals with only one defined slot. Although this is possible
it's also highly discourage, because the scope is lost to easily.

Available signals
-----------------

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
