.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt
.. include:: Images.txt


Templating
==========


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:


Customize the output:
---------------------

Per default, all templates are stored in

::

   typo3conf/ext/sf_register/Resources/Private/

Copy files you want to modify of the folder into the fileadmin.
Don't forget to set the path to this new templates folder with.

Since TYPO3 6.2 its possible to have so called fallback pathes for
template, partial and layout root paths. By this its not necessary to
copy all files of given path any more.

::

   plugin.tx_sfregister.view.templateRootPaths.0 = EXT:sf_register/Resources/Private/Templates/
   plugin.tx_sfregister.view.templateRootPaths.1 = EXT:example/Resources/Private/Templates/sf_register/
   plugin.tx_sfregister.view.templateRootPaths.2 = fileadmin/extension_templates/sf_register/Private/Templates/

   plugin.tx_sfregister.view.partialRootPaths.0 = EXT:sf_register/Resources/Private/Partials/
   plugin.tx_sfregister.view.partialRootPaths.1 = EXT:example/Resources/Private/Templates/sf_register/Partials/
   plugin.tx_sfregister.view.partialRootPaths.2 = fileadmin/extension_templates/sf_register/Private/Partials/

   plugin.tx_sfregister.view.layoutRootPaths.0 = EXT:sf_register/Resources/Private/Layouts/
   plugin.tx_sfregister.view.layoutRootPaths.1 = fileadmin/extension_templates/sf_register/Private/Layouts/
   plugin.tx_sfregister.view.layoutRootPaths.2 = fileadmin/extension_templates/sf_register/Private/Layouts/


In pre and post TYPO3 6.2 version its possible to define a template rootpath in
the registration plugin. This gets add/set to the view and used for rendering.


Viewhelper for templates:
~~~~~~~~~~~~~~~~~~~~~~~~~


Birthdate with three select boxes
"""""""""""""""""""""""""""""""""

::

   <register:form.rangeSelect start="1" end="31" property="dateOfBirthDay"/>
   -
   <register:form.rangeSelect start="1" end="12" property="dateOfBirthMonth"/>
   -
   <register:form.rangeSelect start="1960" end="2011" property="dateOfBirthYear"/>


|img-5|


Single select with radio buttons
""""""""""""""""""""""""""""""""

::

   <f:form.radio property="gender" value="1"/> <f:translate key="gender_male"/>
   <f:form.radio property="gender" value="2"/> <f:translate key="gender_female"/>

|img-6|


Single select as select box
"""""""""""""""""""""""""""

::

   <f:form.select property="gender" options="{
   	1: '{f:translate(key: \'gender_male\')}',
   	2: '{f:translate(key: \'gender_female\')}'
   }"/>

|img-7|


automatic marking of requried fields
""""""""""""""""""""""""""""""""""""

::

   <f:render partial="required" arguments="{field: 'gender'}"/>

you get the asterix (\*) behind your label, if the required validator
is active for this field.
