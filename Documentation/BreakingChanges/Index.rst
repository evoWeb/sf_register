.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


Breaking Changes
================

- Method 'changeUsergroup' got pulled up from FeuserCreateController to FeuserController. If a controller extends
  FeuserCreateController the change in changeUsergroup needs to be copied.
- Method 'changeUsergroup' got the parameter '$usergroupIdToBeRemoved' removed. This is because all known usergroups
  previously set get removed now. So only the '$user' and '$usergroupIdToAdd' need to be provided. All usage of this
  method needs to be changed accordingly.