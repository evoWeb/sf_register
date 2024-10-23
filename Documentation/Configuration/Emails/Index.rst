..  include:: /Includes.rst.txt
..  index:: Configuration; Emails
..  _configuration-emails:

======
Emails
======

..  contents::
   :local:

..  _configuration-emails-notify-admin-create-properties:

Notify admin create properties
==============================

..  confval-menu::
    :name: admin-create
    :display: table
    :type:
    :Default:

    ..  _notifyAdminPostCreateSave:

    ..  confval:: notifyAdminPostCreateSave
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the user saved the registration

    .. _notifyAdminPostCreateConfirm:

    ..  confval:: notifyAdminPostCreateConfirm
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the user confirmed the registration

    .. _notifyAdminPostCreateRefuse:

    ..  confval:: notifyAdminPostCreateRefuse
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the user refused the registration

    .. _notifyAdminPostCreateAccept:

    ..  confval:: notifyAdminPostCreateAccept
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the admin accepted the registration

    .. _notifyAdminPostCreateDecline:

    ..  confval:: notifyAdminPostCreateDecline
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the admin declined the registration

    .. _acceptEmailPostCreate:

    ..  confval:: acceptEmailPostCreate
        :type: boolean
        :Default: 0

        Defines wether the admin need to accept the registration


..  _configuration-emails-notify-admin-edit-properties:

Notify admin edit properties
============================

..  confval-menu::
    :name: admin-edit
    :display: table
    :type:
    :Default:

    .. _notifyAdminPostEditSave:

    ..  confval:: notifyAdminPostEditSave
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the user saved the changes

    .. _notifyAdminPostEditConfirm:

    ..  confval:: notifyAdminPostEditConfirm
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the user confirmed the email change

    .. _notifyAdminPostEditAccept:

    ..  confval:: notifyAdminPostEditAccept
        :type: boolean
        :Default: 0

        Defines wether the admin should get an email after the user accepted the email change

    .. _acceptEmailPostEdit:

    ..  confval:: acceptEmailPostEdit
        :type: boolean
        :Default: 0

        Defines wether the admin need to accepted the email change


..  _configuration-emails-notify-user-create-properties:

Notify user create properties
==============================

..  confval-menu::
    :name: user-create
    :display: table
    :type:
    :Default:

    .. _notifyUserPostCreateSave:

    ..  confval:: notifyUserPostCreateSave
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the user saved the registration

    .. _notifyUserPostCreateConfirm:

    ..  confval:: notifyUserPostCreateConfirm
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the user confirmed the registration

    .. _notifyUserPostCreateRefuse:

    ..  confval:: notifyUserPostCreateRefuse
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the user refused the registration

    .. _notifyUserPostCreateAccept:

    ..  confval:: notifyUserPostCreateAccept
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the admin accepted the registration

    .. _notifyUserPostCreateDecline:

    ..  confval:: notifyUserPostCreateDecline
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the admin declined the registration

    .. _confirmEmailPostCreate:

    ..  confval:: confirmEmailPostCreate
        :type: boolean
        :Default: 0

        Defines wether the user need to confirm the registration


..  _configuration-emails-notify-user-edit-properties:

Notify user edit properties
===========================

..  confval-menu::
    :name: user-edit
    :display: table
    :type:
    :Default:

    .. _notifyUserPostEditSave:

    ..  confval:: notifyUserPostEditSave
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the user saved the changes

    .. _notifyUserPostEditConfirm:

    ..  confval:: notifyUserPostEditConfirm
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the user confirmed the email change

    .. _notifyUserPostEditAccept:

    ..  confval:: notifyUserPostEditAccept
        :type: boolean
        :Default: 0

        Defines wether the user should get an email after the user accepted the email change

    .. _confirmEmailPostEdit:

    ..  confval:: confirmEmailPostEdit
        :type: boolean
        :Default: 0

        Defines wether the user need to confirm the email change


..  _configuration-emails-address-and-subject-properties:

Address and subject properties
==============================

..  confval-menu::
    :name: address-and-subject
    :display: table
    :type:
    :Default:

    .. _sitename:

    ..  confval:: sitename
        :type: string
        :Default: dummy Site

        Page Title for email subject

    .. _userEmail.fromName:

    ..  confval:: userEmail.fromName
        :type: string
        :Default: userEmail from

        Name used as from for email to user

    .. _userEmail.fromEmail:

    ..  confval:: userEmail.fromEmail
        :type: string
        :Default: userEmailfrom@test.local

        Email used as from for email to user

    .. _userEmail.replyName:

    ..  confval:: userEmail.replyName
        :type: string
        :Default: userEmail reply

        Name used as reply for email to user

    .. _userEmail.replyEmail:

    ..  confval:: userEmail.replyEmail
        :type: string
        :Default: userEmailreply@test.local

        Email used as reply for email to user

    .. _adminEmail.toName:

    ..  confval:: adminEmail.toName
        :type: string
        :Default: adminEmail to

        Name used as recipient for email to admin

    .. _adminEmail.toEmail:

    ..  confval:: adminEmail.toEmail
        :type: string
        :Default: adminToEmail@test.local

        Email used as recipient for email to admin

    .. _adminEmail.fromName:

    ..  confval:: adminEmail.fromName
        :type: string
        :Default: adminEmail from

        Name used as from for email to admin

    .. _adminEmail.fromEmail:

    ..  confval:: adminEmail.fromEmail
        :type: string
        :Default: adminEmailfrom@test.local

        Email used as from for email to admin

    .. _adminEmail.replyName:

    ..  confval:: adminEmail.replyName
        :type: string
        :Default: adminEmail reply

        Name used as reply for email to admin

    .. _adminEmail.replyEmail:

    ..  confval:: adminEmail.replyEmail
        :type: string
        :Default: adminEmailreply@test.local

        Email used as reply for email to admin
