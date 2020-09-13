# TYPO3 Extension ``sf_register``
![build](https://github.com/evoWeb/sf_register/workflows/build/badge.svg?branch=develop)
[![Latest Stable Version](https://poser.pugx.org/evoweb/sf-register/v/stable)](https://packagist.org/packages/evoweb/sf-register)
[![Monthly Downloads](https://poser.pugx.org/evoweb/sf-register/d/monthly)](https://packagist.org/packages/evoweb/sf-register)
[![Total Downloads](https://poser.pugx.org/evoweb/sf-register/downloads)](https://packagist.org/packages/evoweb/sf-register)

## Installation

### via Composer

The recommended way to install sf_register is by using [Composer](https://getcomposer.org):

    composer require evoweb/sf-register

**Installation from TYPO3 Extension Repository**

Download and install the extension with the extension manager module or directly from the
[TER](https://extensions.typo3.org/extension/sf_register).

Repository and Issue Tracker can be found at https://github.com/evoWeb/sf_register

Suits all your needs to handle frontend users like register new users, edit data and change password.

So what's already in there?

- creating frontend user
    - flexible form generation by selecting fields in plugin
    - send notification to user and admin
    - activate via link in email by user or admin
    - notification email after activation
    - configure email addresses for user and admin mails separately
    - set different usergroup pre and post activation
    - agree to general terms and conditions as checkbox
    - old password verification before setting new password
    - password strength indicator without need of any js lib
    - email/password repeat validation
    - profile image upload, remove and edit with plain or encrypted filename
    - country as selectbox (values from static_info_tables)
    - country zone as selectbox (values from static_info_tables)
    - country zone change with ajax if country selectbox changed
    - language as selectbox (values from static_info_tables)
    - gender as radiobox
    - title as textbox and selectbox
    - pseudonym
    - timezone as selectbox
    - daylight saving as checkbox
    - privacy agreement as checkbox
    - salutation as radiobuttons and selectbox
    - birthdate as selectboxes
    - captcha with integration of existing captcha extensions
    - configuration email as username
- custom validators
    - user model
    - captcha
    - required
    - repeat
- custom viewhelpers
    - required
    - captcha
    - static info tables selectboxes
- edit frontend user
- change password
- different template file for every form, preview, save and email view, configurable to be overridden in sitepackages
- override template rootpath in plugin

If all that is already in, what is missing?

- complete documentation
- ajax handling
    - javascript validators in jquery
- model is extendable by other extensions
- better extendability of frontend user model, well this needs some love in extbase
- multistep creation and editing

How could you help?

- file issues about bugs and if you already have a solution send the patch in
- sponsor features you are in need of

Homepage https://www.evoweb.de/
