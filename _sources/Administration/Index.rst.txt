.. include:: ../Includes.txt


.. _administration:

Administration
==============

- Install the extension with the extension manager

- Install the captcha extension you want to use (sr\_freecap or
  jw\_recaptcha work out of the box)

- install static-info-tables

- include the static template

- create a sysfolder to store the user data

- create two fe\_usergroups inside this sysfolder

- BE AWARE: if you use older fe\_usergroups, check the record type and set it to Tx\_Extbase\_Domain\_Model\_FrontendUserGroup, if this is missing!

- Use the Constants Editor to configure the extension.

- Create a page for register, insert the Plugin

  - Code is Create

  - record storage must include the sysfolder with your user data

- create a page for edit the profile, insert the Plugin

  - limit the access to the usergroup the user gets after activation

  - Code is Edit

  - record storage must include the sysfolder with your user data

- create a page for password change, include the Plugin

  - limit the access to the usergroup the user gets after activation

  - Code is Password

  - record storage must include the sysfolder with your user data


FAQ
---

- Possible subsections: FAQ
