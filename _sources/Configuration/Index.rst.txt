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
