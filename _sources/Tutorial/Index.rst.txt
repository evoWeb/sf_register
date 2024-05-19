.. include:: ../Includes.txt


.. _tutorial:

Tutorial
========


How to setup different registration processes?
----------------------------------------------

It is possible to have simple registrations like directly activated user accounts as well as complex ones like double optin
confirmations with admin acceptance. So if its possible to have different approches how to set them up?


Simple like peanut butter jelly sandwich:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
- In the Extension Manager

  - install the extension

  - in Extension Settings choose 'minimal' in TypoScript complexity

- In modul List

  - add a storage folder and remember the storage folder id

  - add a usergroup with name of your choice in this storage folder

  - add a page named Registration

  - in the page page properties on tab Resources select the storage folder in field 'General Record Storage Page'

  - on this page add a plugin type 'Registration for create, edit or delete Frontend Users'

- In the plugin

  - select 'Create' in the 'What action should be taken?' select box

  - leave the Template root path empty until you know what you do

- In modul Template

  - choose 'Info/Modify'

  - edit the whole template record

  - add the include static 'Feuser Register [minimal] (sf_register)' and save and close the TypoScript

  - choose the Constant Editor

  - activate and set the remembered usergroup id in the field 'usergroup set if no activation is needed':
    (the result in constants field is plugin.tx_sfregister.settings.usergroup = 3)

Somewhat like creme bruleé:
~~~~~~~~~~~~~~~~~~~~~~~~~~~


Your christmas stuffed Turkey:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
