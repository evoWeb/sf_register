.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt
.. include:: Images.txt


Configuration
=============


Customize the output:
---------------------

Per default, all templates are stored in

::

   typo3conf/ext/sf_register/Resouces/Private.

Copy this Folder into the fileadmin and edit the templates you want to
change. Don't forget to set the path to this new templates folder with

::

   plugin.tx_sfregister.templateRootPath = fileadmin/extension_templates/sf_register/Private

The Plugins can take the path as well.


Viewhelper for templates:
~~~~~~~~~~~~~~~~~~~~~~~~~


Birthdate with three select boxes
"""""""""""""""""""""""""""""""""

::

   <register:form.rangeSelect start="1" end="31" property="dateOfBirthDay"/>
   -
   <register:form.rangeSelect start="1" end="12" property="dateOfBirthMonth"/>
   -
   <register:form.rangeSelect start="1960" end="2011" property="dateOfBirthYear"/>


|img-5|


Single select with radio buttons
""""""""""""""""""""""""""""""""

::

   <f:form.radio property="gender" value="1"/> <f:translate key="gender_male"/>
   <f:form.radio property="gender" value="2"/> <f:translate key="gender_female"/>

|img-6|


Single select as select box
"""""""""""""""""""""""""""

::

   <f:form.select property="gender" options="{
   	1: '{f:translate(key: \'gender_male\')}',
   	2: '{f:translate(key: \'gender_female\')}'
   }"/>

|img-7|


automatic marking of requried fields
""""""""""""""""""""""""""""""""""""

::

   <f:render partial="required" arguments="{field: 'gender'}"/>

you get the asterix (\*) behind your label, if the required validator
is active for this field.


Use Validators:
---------------

Validators check the user input. Some often needed validatores come
with this extension, so can use them. To a field in the form you can
add none, one or some validators.


More than one validator:
~~~~~~~~~~~~~~~~~~~~~~~~

plugin.tx\_sfregister.settings.validation.<action>.<fieldname>.<nummer
> = <Validator>


Example
"""""""

::

   plugin.tx_sfregister.settings.validation.create.password {
     1 = Tx_SfRegister_Domain_Validator_RequiredValidator
     2 = StringLength(minimum = 8, maximum = 40)
     3 = Tx_SfRegister_Domain_Validator_BadWordValidator
   }


one validator:
~~~~~~~~~~~~~~

plugin.tx\_sfregister.settings.validation.<action>.<fieldname> =
<Validator>


Example
"""""""

::

   plugin.tx_sfregister.settings.validation.create.title = StringLength(minimum = 2, maximum = 80)


remove all validators:
~~~~~~~~~~~~~~~~~~~~~~

plugin.tx\_sfregister.settings.validation.<action>.<fieldname> >


Example
"""""""

::

   plugin.tx_sfregister.settings.validation.create.title >


available validators:
---------------------

- StringLength(minimum = 1, maximum = 80) → check for lenght

- EmailAddress → check for valid email address

- Tx\_SfRegister\_Domain\_Validator\_UniqueValidator → check if input is
  unique in storage folder

- Tx\_SfRegister\_Domain\_Validator\_UniqueValidator(global = 1) → check
  if input is unique in whole system

- Tx\_SfRegister\_Domain\_Validator\_RepeatValidator → check if the
  input is similar to the input in sibling field (password and
  passwortrepeat, email and emailrepeat)

- Tx\_SfRegister\_Domain\_Validator\_BadWordValidator → check for
  unwanted words

- Tx\_SfRegister\_Domain\_Validator\_IsTrueValidator → check if checkbox
  is activated

- Tx\_SfRegister\_Domain\_Validator\_ImageUploadValidator → check for
  valid image file

- Tx\_SfRegister\_Domain\_Validator\_CaptchaValidator(type = srfreecap)
  → check for valid captcha response (you need a captcha extension for
  this)

- Tx\_SfRegister\_Domain\_Validator\_EqualCurrentPasswordValidator →
  check a given password with the currenty used (for change password
  action)


add more fields:
----------------

Due to lacking support from extbase, this is not possible at this
moment. You would need to change the database and the model of the
extension. Support ist announced for extbase version 1.4, which will
be shipped with TYPO3 4.6.


use another captcha extension
-----------------------------

You have to write a captcha adapter for this purpose. You find the
adapters here:

::

   typo3conf/ext/sf_register/Classes/Services/Captcha

Extend class

::

   Tx_SfRegister_Services_Captcha_AbstractAdapter

The functions

::

   render()

and

::

   isValid()

are required for the adapter to work.


Write own validators
--------------------

You can write your own validator. Validators are stored in

::

   typo3conf/ext/sf_register/Classes/Domain/Validator ,

extends class

::

   Tx_Extbase_Validation_Validator_AbstractValidator

and require the function

::

   isValid().


Reference
=========

plugin.tx\_sfregister.settings:

.. field-list-table::
 :header-rows: 1

 - :Property:
         Property:

   :Data type:
         Data type:

   :Description:
         Description:

   :Default:
         Default:



 - :Property:
         useDataOfLoggedinFeuser

   :Data type:
         boolean

   :Description:
         Fill the edit form with data of currently logged in user

   :Default:
         1



 - :Property:
         badWordList

   :Data type:
         string

   :Description:
         Comma sererated list of word, that validator badWordFilterwill avoid

   :Default:
         god, sex, password



 - :Property:
         redirectPostRegistrationPageId

   :Data type:
         integer

   :Description:
         Redirect page after registration

   :Default:



 - :Property:
         redirectPostActivationPageId

   :Data type:
         Int

   :Description:
         Redirect page after activation

   :Default:



 - :Property:
         useEmailAddressAsUsername

   :Data type:
         boolean

   :Description:
         Use email adress as username

   :Default:



 - :Property:
         encryptPassword

   :Data type:
         Int

   :Description:
         Encrypt password

         0 – none1 – md5

         2 - sha1

   :Default:
         0



 - :Property:
         useEncryptedFilename

   :Data type:
         int

   :Description:
         Encrypt filenames

         0 – none1 – md5

         2 - sha1

   :Default:
         0



 - :Property:
         notifyToAdmin

   :Data type:
         boolean

   :Description:
         Send notification to admin that user has registered

   :Default:
         0



 - :Property:
         notifyAdminPreConfirmation

   :Data type:
         boolean

   :Description:
         Send notification to admin that user has registered

   :Default:
         0



 - :Property:
         notifyAdminPostConfirmation

   :Data type:
         boolean

   :Description:
         Send notification to admin that user has activated his account

   :Default:
         0



 - :Property:
         autologinPostRegistration

   :Data type:
         boolean

   :Description:
         Log in user after registration

   :Default:
         0



 - :Property:
         autologinPostConfirmation

   :Data type:
         boolean

   :Description:
         Log in user after activation

   :Default:
         0



 - :Property:
         usergroupPreConfirmation

   :Data type:
         Int

   :Description:
         FE usergroup after registration

   :Default:



 - :Property:
         usergroupPostConfirmation

   :Data type:
         Int

   :Description:
         FE usergroup after activation

   :Default:



 - :Property:
         usergroup

   :Data type:
         Int

   :Description:
         FE Usergroup after activation

   :Default:



 - :Property:
         sitename

   :Data type:
         String

   :Description:
         Page Title for email subject

   :Default:
         dummy Site



 - :Property:
         userEmail.fromName

   :Data type:
         string

   :Description:


   :Default:
         userEmail from



 - :Property:
         userEmail.fromEmail

   :Data type:
         string

   :Description:


   :Default:
         userEmailfrom@test.local



 - :Property:
         userEmail.replyName

   :Data type:
         string

   :Description:


   :Default:
         userEmail reply



 - :Property:
         userEmail.replyEmail

   :Data type:
         string

   :Description:


   :Default:
         userEmailreply@test.local



 - :Property:
         adminEmail.toName

   :Data type:
         string

   :Description:


   :Default:
         adminEmail to



 - :Property:
         adminEmail.toEmail

   :Data type:
         string

   :Description:


   :Default:
         adminToEmail@test.local



 - :Property:
         adminEmail.fromName

   :Data type:
         string

   :Description:


   :Default:
         adminEmail from



 - :Property:
         adminEmail.fromEmail

   :Data type:
         string

   :Description:


   :Default:
         adminEmailfrom@test.local



 - :Property:
         adminEmail.replyName

   :Data type:
         string

   :Description:


   :Default:
         adminEmail reply



 - :Property:
         adminEmail.replyEmail

   :Data type:
         string

   :Description:


   :Default:
         adminEmailreply@test.local



 - :Property:
         validation.create.<feldname>

   :Data type:
         string/array

   :Description:
         Validators for create form

   :Default:



 - :Property:
         validation.edit.<feldname>

   :Data type:
         string/array

   :Description:
         Validators for edit form

   :Default:



 - :Property:
         validation.password.<feldname>

   :Data type:
         string/array

   :Description:
         Validators for change password form

   :Default:



 - :Property:
         filefieldname

   :Data type:
         String

   :Description:
         Fild for filenames after upload

   :Default:
         image



 - :Property:
         captcha.jmrecaptcha

   :Data type:
         String

   :Description:
         Adapter for Captcha-Extension jm\_recaptcha

   :Default:
         Tx\_SfRegister\_Services\_Captcha\_JmRecaptchaAdapter



 - :Property:
         captcha.srfreecap

   :Data type:
         string

   :Description:
         Adapter for Captcha-Extension sr\_freecap

   :Default:
         Tx\_SfRegister\_Services\_Captcha\_SrFreecapAdapter



plugin.tx\_sfregister.persistence:

.. field-list-table::
 :header-rows: 1

 - :Property:
         Property:

   :Data type:
         Data type:

   :Description:
         Description:

   :Default:
         Default:



 - :Property:
         StoragePid

   :Data type:
         integer

   :Description:
         Sysfolder with FE User records

   :Default:



 - :Property:
         classes

   :Data type:
         Array

   :Description:
         Database tables in use

   :Default:




Available Hooks
---------------

::

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sf_register']['Tx_SfRegister_Services_Mail']

For Email generation are this hooks available:

- sendAdminNotificationMail

- sendAdminNotificationMailPostActivation

- sendAdminNotificationMailPreActivation

- sendUserNotificationMail

- sendUserNotificationMailPostActivation

- sendUserNotificationMailPreActivation

- sendAdminActivationMailAfterEdit

- sendUserActivationMailAfterEdit

- sendAdminNotificationMailAfterEdit

- sendUserNotificationMailAfterEdit

::

   $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser']