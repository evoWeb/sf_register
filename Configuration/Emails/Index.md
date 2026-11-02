---
title: "Emails"
manual: "Frontend User Registration: Multipurpose configurable registration"
version: "14.0.3"
permalink: "https://docs.typo3.org/permalink/evoweb/sf-register:configuration-emails@14.0.3"
source: "Configuration/Emails/Index.rst"
rendered: "2026-11-02T17:07:04+00:00"
---

# Emails {#configuration-emails}

-   [Notify admin create properties](https://docs.typo3.org/permalink/evoweb/sf-register:notify-admin-create-properties@14.0.3)
-   [Notify admin edit properties](https://docs.typo3.org/permalink/evoweb/sf-register:notify-admin-edit-properties@14.0.3)
-   [Notify user create properties](https://docs.typo3.org/permalink/evoweb/sf-register:notify-user-create-properties@14.0.3)
-   [Notify user edit properties](https://docs.typo3.org/permalink/evoweb/sf-register:notify-user-edit-properties@14.0.3)
-   [Address and subject properties](https://docs.typo3.org/permalink/evoweb/sf-register:address-and-subject-properties@14.0.3)

## Notify admin create properties {#configuration-emails-notify-admin-create-properties}

-   **notifyAdminPostCreateSave**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the user saved the registration

-   **notifyAdminPostCreateConfirm**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the user confirmed the registration

-   **notifyAdminPostCreateRefuse**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the user refused the registration

-   **notifyAdminPostCreateAccept**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the admin accepted the registration

-   **notifyAdminPostCreateDecline**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the admin declined the registration

-   **acceptEmailPostCreate**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin need to accept the registration

## Notify admin edit properties {#configuration-emails-notify-admin-edit-properties}

-   **notifyAdminPostEditSave**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the user saved the changes

-   **notifyAdminPostEditConfirm**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the user confirmed the email change

-   **notifyAdminPostEditAccept**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin should get an email after the user accepted the email change

-   **acceptEmailPostEdit**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the admin need to accepted the email change

## Notify user create properties {#configuration-emails-notify-user-create-properties}

-   **notifyUserPostCreateSave**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the user saved the registration

-   **notifyUserPostCreateConfirm**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the user confirmed the registration

-   **notifyUserPostCreateRefuse**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the user refused the registration

-   **notifyUserPostCreateAccept**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the admin accepted the registration

-   **notifyUserPostCreateDecline**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the admin declined the registration

-   **confirmEmailPostCreate**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user need to confirm the registration

## Notify user edit properties {#configuration-emails-notify-user-edit-properties}

-   **notifyUserPostEditSave**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the user saved the changes

-   **notifyUserPostEditConfirm**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the user confirmed the email change

-   **notifyUserPostEditAccept**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user should get an email after the user accepted the email change

-   **confirmEmailPostEdit**

    -   *Type:* boolean
    -   *Default:* 0

    Defines wether the user need to confirm the email change

## Address and subject properties {#configuration-emails-address-and-subject-properties}

-   **sitename**

    -   *Type:* string
    -   *Default:* dummy Site

    Page Title for email subject

-   **userEmail.fromName**

    -   *Type:* string
    -   *Default:* userEmail from

    Name used as from for email to user

-   **userEmail.fromEmail**

    -   *Type:* string
    -   *Default:* [userEmailfrom@test.local](mailto:userEmailfrom@test.local)

    Email used as from for email to user

-   **userEmail.replyName**

    -   *Type:* string
    -   *Default:* userEmail reply

    Name used as reply for email to user

-   **userEmail.replyEmail**

    -   *Type:* string
    -   *Default:* [userEmailreply@test.local](mailto:userEmailreply@test.local)

    Email used as reply for email to user

-   **adminEmail.toName**

    -   *Type:* string
    -   *Default:* adminEmail to

    Name used as recipient for email to admin

-   **adminEmail.toEmail**

    -   *Type:* string
    -   *Default:* [adminToEmail@test.local](mailto:adminToEmail@test.local)

    Email used as recipient for email to admin

-   **adminEmail.fromName**

    -   *Type:* string
    -   *Default:* adminEmail from

    Name used as from for email to admin

-   **adminEmail.fromEmail**

    -   *Type:* string
    -   *Default:* [adminEmailfrom@test.local](mailto:adminEmailfrom@test.local)

    Email used as from for email to admin

-   **adminEmail.replyName**

    -   *Type:* string
    -   *Default:* adminEmail reply

    Name used as reply for email to admin

-   **adminEmail.replyEmail**

    -   *Type:* string
    -   *Default:* [adminEmailreply@test.local](mailto:adminEmailreply@test.local)

    Email used as reply for email to admin
