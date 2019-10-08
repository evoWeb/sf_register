.. include:: ../Includes.txt


.. _validation:

Validation
==========

.. contents::
   :local:
   :depth: 1


Purpose of Validators
---------------------

Validators are a mechanism to ensure that all information given by the user
meet your expectation as an admin. Either if the values make sense in terms
of format like emails oder in required information value like age verification.

If values need to be enforce your should use the RequiredValidator_ because
this validator does not only check if the value of the configured field is
filled but it also serves as a signal for the Required Partial to render the
corresponding flag.

All validators are optional, could be set single or may be even assigned multiple
times to a field. Despite the concept of extbase you are free to choose how many
validators should take care of a value.


Changes since version 9.0.0
---------------------------

Since version 9 validators are only used for selected fields, it's not necessary
to remove validation configuration only because certain fields should not be
present in the form.
Beside that the configuration changed from using "Evoweb\SfRegister\Validation\Validator\RequiredValidator"
to "Evoweb.SfRegister:Required"

In general the pattern for a validation rule is

::

   "name", options={"parameter1": valueA, "parameter2": valueB}

The name needs to be quoted followed with a comma if options are following. Then
the options with an equal sign and json notated properties and values.


Different possibilities of assigning validators
-----------------------------------------------

In case you want to have no validation at all for a field was was configured
by default as required you need to empty the associated validators. Look at the
example given where the validators of the email gets removed.

Remove validators:
''''''''''''''''''

::

	plugin.tx_sfregister.settings.validation.create.email >


If only one validator is needed you could assign it directly to the field like
below.

Assign only one validator:
''''''''''''''''''''''''''

::

 	plugin.tx_sfregister.settings.validation.create.passwordRepeat = "Evoweb.SfRegister:Repeat"


And finaly it's possible to have multiple validators for one field like in the
next example.

Assign multiple validators:
'''''''''''''''''''''''''''

::

	plugin.tx_sfregister.settings.validation.create.password {
		1 = "Evoweb.SfRegister:Required"
		2 = "Evoweb.SfRegister:BadWord"
	}


Regarding validator it's possible to have values attached to the assigned one.
This is beneficial if you want to check against conditions that are not equal
in different cases. One point where this is shown best with is the length of
passwords. One would like to keep it simple as possible  because the loss would
be nearly not existing while another would prefer the passwords stronger than
for Fort Knox.

Poor man´s passwords with short length:
'''''''''''''''''''''''''''''''''''''''

::

	plugin.tx_sfregister.settings.validation.create.password {
		1 = "Evoweb.SfRegister:Required"
		2 = "StringLength", options={"minimum": 8, "maximum": 40}
		3 = "Evoweb.SfRegister:BadWord"
	}


Bulletproof passwords with long length:
'''''''''''''''''''''''''''''''''''''''

::

	plugin.tx_sfregister.settings.validation.create.password {
		1 = "Evoweb.SfRegister:Required"
		2 = "StringLength", options={"minimum": 8, "maximum": 40}
		3 = "Evoweb.SfRegister:BadWord"
	}


In total you have five possible combination of validator assignments for each
field that you use in your form. You have none, one and multiple validators.
And in case of a validator present you can add options too override the default
that is set in the validator.

Separate validators per process
-------------------------------
Each process has its own needs. While you want to have the username validated on
creation, this is not needed in the edit or password change form. This could
be achieved by separating the TypoScript settings in different conditions per
page. But this is mostly inconvenient as you don't see what settings is met until
the condition. Because of this the validation settings are split for any process.

::

	plugin.tx_sfregister.settings.validation {
		create {
			[...]
		}
		edit {
			[...]
		}
		password {
			[...]
		}
		invite {
			[...]
		}
	}


Special validators
------------------

The UserValidator is not meant to be used for field validation. This validator
is a special construct to make the configuration via TypoScript possible. All
others are free to combine. If a validator is only suited for a certain field
it will be mentioned in the detail configuration.

Prefixing needed for non extbase validators
-------------------------------------------

To use the extension validators you need to prefix them in the TypoScript with
Evoweb.SfRegister: . For all validators without this prefix the validation assumes
that they are extbase specific validators and use them as such.

Secondly this makes it possible to use custom validators that do not come with
extbase or sf_register. Just code your validator and make it available for auto
loading (either in an extbase standard path or via ext_autoload.php). Afterwards
you are ready to use your validator like in the following example.

Custom validator usage:
'''''''''''''''''''''''

::

	plugin.tx_sfregister.settings.validation.create.password = "Evoweb.SfRegister:Required"


Available validators
--------------------

Beside the validators that come with extbase and which are although available
in the different processes, the registration come with a set of specific ones
that are tailored to the special need. The following lists all validators
which are suited for the usage on fields.


.. container:: ts-properties

  ===================================================== ==============================================================================================
  Validator                                             Options
  ===================================================== ==============================================================================================
  BadWordValidator_                                     configured by plugin.tx_sfregister.settings.badWordList
  IsTrueValidator_
  RequiredValidator_
  ===================================================== ==============================================================================================


.. _BadWordValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         BadWordValidator

   Data type
         string

   Description
         Checks if the field contains non of the word in the list is present

   Configured in
         plugin.tx_sfregister.settings.badWordList

   Default
         god, sex, password


.. _IsTrueValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         IsTrueValidator

   Data type
         string

   Description
         Checks if the field is set for example the general terms and conditions


.. _RequiredValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         RequiredValidator

   Data type
         string

   Description
         This validator serves two purpose. First of check if the field contains
         a value and that it is not empty. Second the rendering uses this
         validator as condition to render required sign or not.


Special fields Validators
-------------------------

.. container:: ts-properties

  ===================================================== ============================================================= ================== =============
  Validator                                             Assign to field                                               Options            Default
  ===================================================== ============================================================= ================== =============
  CaptchaValidator_                                     captcha                                                       type               srfreecap
  EmptyValidator_                                       auto-assigned to uid in create
  EqualCurrentPasswordValidator_                        oldPassword
  EqualCurrentUserValidator_                            auto-assigned to uid in edit
  ImageUploadValidator_                                 image
  RepeatValidator_                                      email oder password
  UniqueValidator_                                      any, best for email and username                              global             0
  ===================================================== ============================================================= ================== =============


.. _CaptchaValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         CaptchaValidator

   Data type
         string

   Description
         Checks if the entered value matches the captcha text by using the same
         captcha adapter like the one used for the rendering. Therefor the option
         type needs to equal the configuration of the rendering.

         Likewise the rendering of the captcha its possible to use custom
         captchas to validate. How to use create custom is described in
         :ref:`Bring in your own captcha <AddCustomCaptcha>`

   Options:
         type = [srfreecap, jmrecaptcha]

   Default:
         srfreecap


.. _EmptyValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         EmptyValidator

   Data type
         string

   Description
         Checks if the field uid is empty to no unauthorized user manipulation
         could happen

   Specialty:
         This validator gets auto-assigned to the uid field in the create process


.. _EqualCurrentPasswordValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         EqualCurrentPasswordValidator

   Data type
         string

   Description
         This validator is used in the password change process. It compares if the
         entered old password is equal to the current set password for the account
         the user is logged in with.


.. _EqualCurrentUserValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         EqualCurrentUserValidator

   Data type
         string

   Description
         On edit process it's important that the correct user data get modified. To
         ensure that the submitted data does not temper the user id. This
         validator compares the user id send by the form with the user id of
         the logged in user to prevent and change of the id.

   Specialty:
         This validator gets auto-assigned to the uid field in the edit process


.. _ImageUploadValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         ImageUploadValidator

   Data type
         string

   Description
         The image upload validator checks if the file uploaded has an allowed
         file extension and if the file size is in the allowed max size. Both
         checks uses system configuration.

   Configuration:
         file extension is configured by $GLOBALS['TCA']['fe_users']['columns']['image']['config']['allowed']
         file size is configured by $GLOBALS['TCA']['fe_users']['columns']['image']['config']['max_size']


.. _UniqueValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         UniqueValidator

   Data type
         string

   Description
         Checks if the value is unique for the field in the storage folder. If
         the option 'global' is set a second check for the value in the field is
         done system wide.

         By this you could define that an email address is unique either in the
         local context or for the complex TYPO3 cms installation. So if you have
         multiple newsletters that could get registered its ideal to choose only
         the global = 0 check, so an user could register multiple times.

         If the username should be only available once in the system to prevent
         collisions in authentication you would choose to set globals = 1 .

   Options:
         global = [0,1]

   Default
         0


.. _RepeatValidator:
.. ### BEGIN~OF~TABLE ###

.. container:: table-row

   Property
         RepeatValidator

   Data type
         string

   Description
         To ensure that the input of a user was done without mistake by the user
         self it's a good idea to have the values entered in two fields and then
         compare their values. That is possible with this validator.

         The fields need to be present with the pattern [fieldName] and
         [fieldName]Repeat. Where fieldName is free to choose. While Repeat is
         not configurable by now this validator is only usable for email and
         password fields.
