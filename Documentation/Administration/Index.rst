..  include:: /Includes.rst.txt
..  index:: Administration
..  _administration:

==============
Administration
==============

- install the extension with composer

  ..  code-block:: bash
      :caption: shell

      composer req evoweb/sf-register

- install a compatible captcha extension of your choice

  ..  code-block:: bash
      :caption: shell

      composer req evoweb/recaptcha
      // or
      composer req sjbr/sr-freecap

- install static-info-tables

  ..  code-block:: bash
      :caption: shell

      composer req sjbr/static-info-tables

- include the static template, since TYPO3 13 its possible to use one of the two
  available SiteSets instead.

- create a sysfolder to store the user data

- create two fe_usergroups inside this sysfolder

- use the Constants Editor to configure the extension. If you use a SiteSet
  instead of TypoScript static template, you need to use 'Site Management >
  Settings' instead

- create a page for register, insert a "Registration: Create user" content element

  - record storage must include the sysfolder with your user data

- create a page to edit the profile, insert a "Registration: Edit user" content element

  - limit the access to the usergroup the user gets after activation

  - record storage must include the sysfolder with your user data

- create a page to change the password, insert a "Registration: Change password" content element

  - limit the access to the usergroup the user gets after activation

  - record storage must include the sysfolder with your user data
