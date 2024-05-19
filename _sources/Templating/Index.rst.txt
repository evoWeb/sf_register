.. include:: ../Includes.txt


.. _templating:

Templating
==========


.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:


Template for dynamic fields:
----------------------------

Since version 8.8.0 the forms are rendered by partials. Create and Edit form fields are defined in typoscript or
selected in the plugin and then rendered by a loop which calls the configured partial per field.

This was made to be able to only change one field alone through modifying the partial.

Additionaly its now possible to select the fields needed in the content instead the need to add typoscript for the one
page where the registration should be different.

As its not always needed to define your field selection there are some defaults configured out of the box.

**Fields/setup.typoscript**::

   plugin.tx_sfregister.settings.fields.defaultSelected {
      create { }
      edit { }
   }


Word of advise to those that upgrade an existing installation. To make use of this feature in your old templates you
need to replace all <div> tags between the form tags with the loop.

**Form.html**::

		<f:for each="{settings.fields.selected}" as="selectedField">
			<f:alias map="{options: '{settings.fields.configuration.{selectedField}}'}">
				<f:render partial="Form/{options.partial}"
					arguments="{user: user, fieldName: selectedField, options: options, settings: settings}"/>
			</f:alias>
		</f:for>


Customize the output:
---------------------

Per default, all templates are stored in

::

   typo3conf/ext/sf_register/Resources/Private/

Copy files you want to modify of the folder into the fileadmin.
Don't forget to set the path to this new templates folder with.

Since TYPO3 6.2 its possible to have so called fallback paths for
template, partial and layout root paths. By this its not necessary to
copy all files of given path any more.

::

   plugin.tx_sfregister.view.templateRootPaths.0 = EXT:sf_register/Resources/Private/Templates/
   plugin.tx_sfregister.view.templateRootPaths.1 = EXT:example/Resources/Private/Templates/sf_register/

   plugin.tx_sfregister.view.partialRootPaths.0 = EXT:sf_register/Resources/Private/Partials/
   plugin.tx_sfregister.view.partialRootPaths.1 = EXT:example/Resources/Private/Templates/sf_register/Partials/

   plugin.tx_sfregister.view.layoutRootPaths.0 = EXT:sf_register/Resources/Private/Layouts/
   plugin.tx_sfregister.view.layoutRootPaths.1 = EXT:example/Resources/Private/Templates/sf_register/Layouts/


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


.. figure:: Images/screenshot_dateselectors.png
   :alt:
   :align: left


Single select with radio buttons
""""""""""""""""""""""""""""""""

::

   <f:form.radio property="gender" value="1"/> <f:translate key="gender_1"/>
   <f:form.radio property="gender" value="2"/> <f:translate key="gender_2"/>


.. figure:: Images/screenshot_genderradio.png
   :alt:
   :align: left


Single select as select box
"""""""""""""""""""""""""""

::

   <f:form.select property="gender" options="{
   	1: '{f:translate(key: \'gender_1\')}',
   	2: '{f:translate(key: \'gender_2\')}'
   }"/>


.. figure:: Images/screenshot_genderselect.png
   :alt:
   :align: left


Automatic marking of requried fields
""""""""""""""""""""""""""""""""""""

::

   <f:render partial="required" arguments="{field: 'gender'}"/>

you get the asterix (\*) behind your label, if the required validator
is active for this field.
