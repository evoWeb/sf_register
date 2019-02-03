.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


Breaking Changes
================

2019.02.03
''''''''''

Drop custom form styles in favor for Bootstrap 4.2 styles. Be aware, to get the styles.css
from older releases if you depend on it. If you use the Bootstrap 4.2 form styles you are
good to go.


2019.02.02
''''''''''

The password strength meter got replaced with the <meter> element. If you still need the old
iframe variant for old browser or for the looks, please override the file
EXT:sf_register/Resources/Private/Partials/Form/Password.html in your sitepackage and replace

Before:
::
   <meter min="0" low="20" optimum="30" high="40" max="50" id="bargraph"></meter>

After:
::
   <iframe id="bargraph" frameborder="none" scrolling="no"
      src="/typo3conf/ext/sf_register/Resources/Public/Images/progressbar.svg"></iframe>



2019.01.17
''''''''''

The core changed away from saltedpasswords towards integrated passwordHashing in EXT:Core (see
Deprecation: #85804 - Salted password hash class deprecations)

By this its always possible to properly hash passwords.

Due to this shift the support for md5 and sha1 configuration is dropped in
EqualCurrentPasswordValidator::isValid and FeuserController::encryptPassword.



2019.01.13
''''''''''

Changes in validation were done to match the new pattern used since TYPO3 9. To ensure that the user/password model
still validates you need to check whether you changed rules in plugin.tx_sfregister.settings.validation.*.*

Here are some examples how old rules need to be converted:

Before:
::
   Evoweb\SfRegister\Validation\Validator\RequiredValidator
After:
::
   "Evoweb.SfRegister:Required"

Before:
::
   StringLength(minimum = 4, maximum = 80)
After:
::
   "StringLength", options={"minimum": 4, "maximum": 80}

Before:
::
   Evoweb\SfRegister\Validation\Validator\UniqueValidator(global = 1)
After:
::
   "Evoweb.SfRegister:Unique", options={"global": 1}

In general 'Evoweb\SfRegister\Validation\Validator\' needs to be replaced with '"Evoweb.SfRegister:' and the
ending 'Validator' with '"'



2015.11.15
''''''''''

- Method 'changeUsergroup' got pulled up from FeuserCreateController to FeuserController. If a controller extends
  FeuserCreateController the change in changeUsergroup needs to be copied.
- Method 'changeUsergroup' got the parameter '$usergroupIdToBeRemoved' removed. This is because all known usergroups
  previously set get removed now. So only the '$user' and '$usergroupIdToAdd' need to be provided. All usage of this
  method needs to be changed accordingly.

- Drop mailhash, setMailhash() and getMailhash() from frontend user model as it was deprecated since 2014.
