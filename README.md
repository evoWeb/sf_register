# Overview

Repository and Issue Tracker can be found at https://github.com/evoWeb/sf_register

Suits all your needs to handle frontend users like register new users, edit data and change password.

So whats already in there?

- creating frontend user
    - send notification to user and admin
    - activate via link in email by user or admin
    - notification email after activation
    - configure email addresses for user and admin mails separately
    - set different usergroup pre and post activation
    - general terms and conditions acception as checkbox
    - old password verification before setting new password
    - password strength indicator without need of any js lib
    - email/password repeat validation
    - profilimage upload, remove and edit with plain or encrypted filename
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
- different template file for every form, preview, save and email view, configurable so they do not need to stay in extension
- override template rootpath in plugin
- realurl settings preconfigured for controller, actions and mailhash

If all that is already in, what is missing?

- complete documentation
- ajax handling
    - javascript validators in jquery, extjs you name it
- add interface for user model to enable other extension to extend the model (still needs changes to extbase)
- better extendability of frontend user model, well this needs some love in extbase
- multistep creation and editing

How could you help?

- file issues about bugs and if you already have a solution send the patch in
- sponsor features you are in need of

Homepage http://www.evoweb.de/
