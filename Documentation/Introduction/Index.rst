.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt
.. include:: Images.txt

Introduction
============

This Documentation was written for version 2.0.0 of the extension.


What does it do?
----------------


Registration with admin review and all notifications:
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The new user registers hinself with his data. He gets support by
password strength indicator and required fields validation.
Emailaddress and password are inserted twice to prevend misspelling.
After send the user can review his data on a review site to change
something. If he consists, all data are stored in the database with a
temporary usergroup. Uploaded pictures are stored in a temporary
folder until activation. Cleanup is simlified by this.

After storage of data the user gets an email with a link in it to
verify his emailaddress. This is the double opt in subscription.

In the same time the admin gets an email with the notification of user
registration.

After the user has verified hin emailaddress, the admin gets another
mail with an activation link. If he decides to accept the new user, he
uses this link and the stored data will be updated with the regular
usergroup for users. After this the user is able to log into his
account and see all the pages and contents his usergroup allowes him
to see. To make sure the user notices his acception, there will be one
last email to the user, telling him he may enter.


Features:
~~~~~~~~~~~~~~

- Simple frontend user registration

- uses extbase and fluid

- admin review optional

- email notification to users after each step

- email notification to admin after each step

- password strength indicator without javascript library

- localisation support by static info tables

- daylight saving support

- respect AGB checkbox included

- Captcha integration

- email as username supported

- required fields validator and some more validators out of the box

- edit profile

- change password

- change frontend view for every form and registration step

- english and german localisation included

- mechanism to avoid profile images as file transfer by encrypted
  filenames and storage in temporary folders

- saltedpassword encryption (if activated) or sha1- and md5 encryption
  support

- configuration by TypoScript – customize to your needs


Screenshots:
~~~~~~~~~~~~


|img-3| *Abbildung 1: simple register form with recaptcha*
|img-4|  *Abbildung 2: extensive register form with sr\_freecap*
