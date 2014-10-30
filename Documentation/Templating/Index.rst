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

Copy this Folder into the fileadmin and edit the templates you want to
change. Don't forget to set the path to this new templates folder with

::

   plugin.tx_sfregister.view.templateRootPath = fileadmin/extension_templates/sf_register/Private/Templates/
   plugin.tx_sfregister.view.partialRootPath = fileadmin/extension_templates/sf_register/Private/Partials/
   plugin.tx_sfregister.view.layoutRootPath = fileadmin/extension_templates/sf_register/Private/Layouts/

The Plugins can take the path as well.


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
