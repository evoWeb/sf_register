..  include:: /Includes.rst.txt
..  index:: Extendability
..  _extendability:

=============
Extendability
=============

.. contents::
   :local:

.. _CreateCustomFields:

Use your own flavor of fields
=============================

Since version 8.8.0 its easier then ever to user your own fields. Just add your
partial folder via typoscript and register your own field configuration. After
that you need to tell the user ts config that the new type is available to
be selected in the plugin too.

..  code-block:: typoscript
    :caption: Fields.typoscript

    plugin.tx_sfregister.view.partialRootPaths.50 = EXT:your_extension/Resources/Private/Partials/
    plugin.tx_sfregister.settings.fields.configuration {
        your_field_key {
            partial = YourFieldKey
            backendLabel = LLL:EXT:your_extension/Resources/Private/Language/locallang_be.xlf:your_field_key
        }
    }

..  code-block:: php
    :caption: ext_localconf.php

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
        '@import \'EXT:sf_register/Configuration/TypoScript/Fields.typoscript\''
    );

..  code-block:: php
    :caption: Setup

    @import 'EXT:sf_register/Configuration/TypoScript/Fields.typoscript'

By using the same fields file both in typoscript as well as in user ts config.
No additional configuration is needed.

In your partials there are the following information available

* `{user}` the user object with previous entered values
* `{fieldName}` the name of the field in the user object
* `{options}` every value that is inside of the field config {partial, backendLabel, etc}
* `{settings}` the general plugin settings

.. _AddCustomProperties:

Adding custom properties
========================

Since late the frontend user domain model can be extended. This can be done the
extension `evoweb/extender`_ which sole purpose is to extend extbase domain models.
There is an `example`_ on how to use the extender.

If you run into problems extending please be aware that the only solution
supported is by the use of 'extender'.

In this example its noteworthy that the last array key is not required but advised.
The path to the file matches extension key and extbase compatible path to the domain model.

For highlighting purpose its advised to let the domain model extend
from :php:`\Evoweb\SfRegister\Domain\Model\FrontendUser`

..  code-block:: php
    :caption: ext_localconf.php

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sf_register']['extender'][
        \Evoweb\SfRegister\Domain\Model\FrontendUser::class
    ]['site-package'] = 'EXT:site_package/Classes/Domain/Model/FrontendUser.php';

Beside extending the domain model with property and get-/set-method a field
needs to be created for sql and registered in TCA.

..  code-block:: sql
    :caption: ext_tables.sql

    #
    # Table structure for table 'fe_users'
    #
    CREATE TABLE fe_users (
        extending varchar(60) DEFAULT ''
    );

..  code-block:: php
    :caption: TCA/Overrides/fe_users.php

    $temporaryColumns = [
        'extending' => [
            'exclude' => 1,
            'label' => 'extending',
            'config' => [
                'type' => 'input',
                'readOnly' => TRUE,
            ],
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns, 1);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'extending');

.. _AddCustomCaptcha:

Bring in your own captcha
=========================

By implementing a adapter which is extending the
:php:`\Evoweb\SfRegister\Services\Captcha\AbstractAdapter` you are able to add
an own captcha. The adapter now then has to be configured to be usable by adding
typoscript settings like the following taken from `recaptcha`_:

..  code-block:: typoscript
    :caption: Captcha.typoscript

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
=============

This kind of event is superseding Hooks and Signal-Slots in TYPO3 and are the
way to go. That's why all signals are replaced with their event counterparts.

How to implement a slot
=======================

An `overview`_ on how to configure and interact with events was given on the
Developer Days in 2019. The detailed example shows how to configure them in the
Services.yaml:

..  code-block:: php
    :caption: YourEventListener.php

    use Evoweb\SfRegister\Controller\Event\ProcessInitializeActionEvent;
    use TYPO3\CMS\Core\Attribute\AsEventListener;

    class YourEventListener
    {
        #[AsEventListener('your-extension-identifier', ProcessInitializeActionEvent::class)]
        public function __invoke(ProcessInitializeActionEvent $event): void
        {
        }
    }

The code above shows how to get an event listener is registered to an event.

Available events
================

FeuserController
----------------

..  confval:: Evoweb\SfRegister\Controller\Event\InitializeActionEvent
    :$controller: :php:`Evoweb\SfRegister\Controller\FeuserController`
    :$settings: :php:`array`
    :$response: :php:`Psr\Http\Message\ResponseInterface`

FeuserCreateController
----------------------

..  confval:: Evoweb\SfRegister\Controller\Event\CreateFormEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\CreatePreviewEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\CreateSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\CreateConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\CreateRefuseEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\CreateAcceptEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\CreateDeclineEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

FeuserDeleteController
----------------------

..  confval:: Evoweb\SfRegister\Controller\Event\DeleteFormEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\DeleteSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\DeleteConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

FeuserEditController
--------------------

..  confval:: Evoweb\SfRegister\Controller\Event\EditFormEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\EditPreviewEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\EditSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\EditConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\EditAcceptEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

FeuserInviteController
----------------------

..  confval:: Evoweb\SfRegister\Controller\Event\InviteFormEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\InviteInviteEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

FeuserPasswordController
------------------------

..  confval:: Evoweb\SfRegister\Controller\Event\PasswordFormEvent
    :$password: :php:`Evoweb\SfRegister\Domain\Model\Password`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\PasswordSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

FeuserResendController
----------------------

..  confval:: Evoweb\SfRegister\Controller\Event\ResendFormEvent
    :$email: :php:`Evoweb\SfRegister\Domain\Model\Email`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Controller\Event\ResendMailEvent
    :$email: :php:`Evoweb\SfRegister\Domain\Model\Email`
    :$settings: :php:`array`

Mail
----

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminCreateAcceptEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminCreateConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminCreateDeclineEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminCreateRefuseEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminCreateSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminDeleteConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminDeleteSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminEditAcceptEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminEditConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminEditSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminInviteInviteEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyAdminResendMailEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserCreateAcceptEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserCreateConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserCreateDeclineEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserCreateRefuseEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserCreateSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserDeleteConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserDeleteSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserEditAcceptEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserEditConfirmEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserEditSaveEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserInviteInviteEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\NotifyUserResendMailEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\InvitationToRegisterEvent
    :$user: :php:`Evoweb\SfRegister\Domain\Model\FrontendUser`
    :$settings: :php:`array`

..  confval:: Evoweb\SfRegister\Services\Event\PreSubmitMailEvent
    :$mail: :php:`TYPO3\CMS\Core\Mail\MailMessage`
    :$settings: :php:`array`
    :$arguments: :php:`array`

.. _evoweb/extender: https://github.com/evoWeb/extender
.. _recaptcha: https://github.com/evoWeb/recaptcha
.. _example: https://github.com/evoWeb/ew_sfregister_extended
.. _overview: https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Hooks/EventDispatcher/Index.html
