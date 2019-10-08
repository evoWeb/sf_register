.. include:: ../Includes.txt


.. _configuration:

Configuration
=============


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   Emails/Index
   Validation/Index

use another captcha extension
-----------------------------

You have to write a captcha adapter for this purpose. You find the
adapters here:

::

   typo3conf/ext/sf_register/Classes/Services/Captcha

Extend class

::

   \\Evoweb\\SfRegister\\Services\\Captcha\\AbstractAdapter

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

   \\TYPO3\\CMS\\Extbase\\Validation\\Validator\\AbstractValidator

and require the function

::

   isValid().


Reference
---------

plugin.tx\_sfregister.settings:

 - :Property:
         badWordList

   :Data type:
         string

   :Description:
         Comma separated list of word, that validator badWordFilter will avoid

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
         usergroupPostSave

   :Data type:
         Int

   :Description:
         FE usergroup after registration

   :Default:



 - :Property:
         usergroupPostConfirm

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
         \\Evoweb\\SfRegister\\Services\\Captcha\\JmRecaptchaAdapter



 - :Property:
         captcha.srfreecap

   :Data type:
         string

   :Description:
         Adapter for Captcha-Extension sr\_freecap

   :Default:
         \\Evoweb\\SfRegister\\Services\\Captcha\\SrFreecapAdapter



plugin.tx\_sfregister.persistence:

.. container:: table-row

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
