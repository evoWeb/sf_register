.. include:: /Includes.rst.txt
..  index:: Configuration
.. _configuration:

=============
Configuration
=============

More complex configuration
--------------------------

.. toctree::
   :titlesonly:
   :glob:

   Emails/Index
   Validation/Index

Table of content
----------------

.. contents::
   :local:

Integrate other captcha extension
=================================

You have to write a captcha adapter for this purpose. You find the
adapters here in `vendor/evoweb/sf-register/Classes/Services/Captcha`.
Your class should extend :php:`\Evoweb\SfRegister\Services\Captcha\AbstractAdapter`.
The functions `render()` and `isValid()` are required for the adapter to work.

Write own validators
====================

You can write your own validator. Validators are stored in
`vendor/evoweb/sf-register/Classes/Domain/Validator` extends class
:php:`\TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator`
and require the function `isValid()`.

Settings
========

plugin.tx_sfregister.settings.*

..  confval-menu::
    :name: settings-reference
    :display: table
    :type:
    :Default:

    ..  _badWordValidator:

    ..  confval:: badWordList
        :type: string
        :Default: god, sex, password

        Comma separated list of word, that validator badWordFilter will avoid

    ..  _redirectPostRegistrationPageId:

    ..  confval:: redirectPostRegistrationPageId
        :type: integer

        Redirect page after registration

    ..  _redirectPostActivationPageId:

    ..  confval:: redirectPostActivationPageId
        :type: integer

        Redirect page after activation

    ..  _useEmailAddressAsUsername:

    ..  confval:: useEmailAddressAsUsername
        :type: boolean

        Use email address as username

    ..  _useEncryptedFilename:

    ..  confval:: useEncryptedFilename
        :type: integer
        :Default: 0

        Encrypt filenames

        - 0 none
        - 1 md5
        - 2 sha1

    ..  _autologinPostRegistration:

    ..  confval:: autologinPostRegistration
        :type: integer

        Log in user after registration

    ..  _autologinPostConfirmation:

    ..  confval:: autologinPostConfirmation
        :type: integer

        Log in user after activation

    ..  _usergroupPostSave:

    ..  confval:: usergroupPostSave
        :type: integer

        Frontend usergroup after registration

    ..  _usergroupPostConfirm:

    ..  confval:: usergroupPostConfirm
        :type: integer

        Frontend usergroup after activation

    ..  _usergroup:

    ..  confval:: usergroup
        :type: integer

        Frontend usergroup after activation

    ..  _captcha-jmrecaptcha:

    ..  confval:: captcha.jmrecaptcha
        :type: string
        :default: \\Evoweb\\SfRegister\\Services\\Captcha\\JmRecaptchaAdapter

        Adapter for Captcha-Extension jm_recaptcha

    ..  _captcha-srfreecap:

    ..  confval:: captcha.srfreecap
        :type: string
        :default: \\Evoweb\\SfRegister\\Services\\Captcha\\SrFreecapAdapter

        Adapter for Captcha-Extension sr_freecap

 - :Property:
         enableConfirmationButtonForEmailLinks

   :Data type:
         boolean

   :Description:
         If set to true, email links will not directly manipulate the state of an user subscription,
         but check for the HTTP method first. In case of HEAD method, a confirmation page with a button
         will be shown. This should prevent double click issues by MicroSoft's SafeLinks feature. In case
         of other methods, the default action gets called immediately.

   :Default:
         0



 - :Property:
         forceConfirmationButtonForEmailLinks

   :Data type:
         boolean

   :Description:
         If `enableConfirmationButtonForEmailLinks` is true and this setting is set to true,
         the confirmation page is always shown regardless of the HTTP method. This can help against
         the link preview features of some email clients.

   :Default:
         0



 - :Property:
         captcha.jmrecaptcha

   :Data type:
         String

   :Description:
         Adapter for Captcha-Extension jm\_recaptcha

   :Default:
         \\Evoweb\\SfRegister\\Services\\Captcha\\JmRecaptchaAdapter



 - :Property:
         captcha.srfreecap

   :Data type:
         string

   :Description:
         Adapter for Captcha-Extension sr\_freecap

   :Default:
         \\Evoweb\\SfRegister\\Services\\Captcha\\SrFreecapAdapter



Persistence
===========

plugin.tx_sfregister.persistence.*

..  confval-menu::
    :name: persistence-reference
    :display: table
    :type:
    :Default:

    ..  _storagePid:

    ..  confval:: storagePid
        :type: integer

        Sysfolder with Frontend User records
