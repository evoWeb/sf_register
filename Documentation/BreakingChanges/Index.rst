.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


Breaking Changes
================

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
