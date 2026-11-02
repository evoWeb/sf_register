---
title: "Configuration"
manual: "Frontend User Registration: Multipurpose configurable registration"
version: "14.0.3"
permalink: "https://docs.typo3.org/permalink/evoweb/sf-register:configuration@14.0.3"
source: "Configuration/Index.rst"
rendered: "2026-11-02T17:07:04+00:00"
---

# Configuration {#configuration}

## More complex configuration {#more-complex-configuration}

-   [Emails](https://docs.typo3.org/permalink/evoweb/sf-register:emails@14.0.3)
-   [Validation](https://docs.typo3.org/permalink/evoweb/sf-register:validation@14.0.3)

## Table of content {#table-of-content}

-   [Integrate other captcha extension](https://docs.typo3.org/permalink/evoweb/sf-register:integrate-other-captcha-extension@14.0.3)
-   [Write own validators](https://docs.typo3.org/permalink/evoweb/sf-register:write-own-validators@14.0.3)
-   [Settings](https://docs.typo3.org/permalink/evoweb/sf-register:settings@14.0.3)
-   [Persistence](https://docs.typo3.org/permalink/evoweb/sf-register:persistence@14.0.3)

### Integrate other captcha extension {#integrate-other-captcha-extension}

You have to write a captcha adapter for this purpose. You find the
adapters here in `vendor/evoweb/sf-register/Classes/Services/Captcha`.
Your class should extend `\Evoweb\SfRegister\Services\Captcha\AbstractAdapter`.
The functions `render()` and `isValid()` are required for the adapter to work.

### Write own validators {#write-own-validators}

You can write your own validator. Validators are stored in
`vendor/evoweb/sf-register/Classes/Domain/Validator` extends class
`\TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator`
and require the function `isValid()`.

### Settings {#settings}

plugin.tx_sfregister.settings.\*

> -   **badWordList**
>
>     -   *Type:* string
>     -   *Default:* god, sex, password
>
>     Comma separated list of word, that validator badWordFilter will avoid
>
> -   **redirectPostRegistrationPageId**
>
>     -   *Type:* integer
>
>     Redirect page after registration
>
> -   **redirectPostActivationPageId**
>
>     -   *Type:* integer
>
>     Redirect page after activation
>
> -   **useEmailAddressAsUsername**
>
>     -   *Type:* boolean
>
>     Use email address as username
>
> -   **useEncryptedFilename**
>
>     -   *Type:* integer
>     -   *Default:* 0
>
>     Encrypt filenames
>
>     -   0 none
>     -   1 md5
>     -   2 sha1
>
> -   **autologinPostRegistration**
>
>     -   *Type:* integer
>
>     Log in user after registration
>
> -   **autologinPostConfirmation**
>
>     -   *Type:* integer
>
>     Log in user after activation
>
> -   **usergroupPostSave**
>
>     -   *Type:* integer
>
>     Frontend usergroup after registration
>
> -   **usergroupPostConfirm**
>
>     -   *Type:* integer
>
>     Frontend usergroup after activation
>
> -   **usergroup**
>
>     -   *Type:* integer
>
>     Frontend usergroup after activation
>
> -   **captcha.jmrecaptcha**
>
>     -   *Type:* string
>     -   *Default:* \\Evoweb\\SfRegister\\Services\\Captcha\\JmRecaptchaAdapter
>
>     Adapter for Captcha-Extension jm_recaptcha
>
> -   **captcha.srfreecap**
>
>     -   *Type:* string
>     -   *Default:* \\Evoweb\\SfRegister\\Services\\Captcha\\SrFreecapAdapter
>
>     Adapter for Captcha-Extension sr_freecap

-   -   *Property:* enableConfirmationButtonForEmailLinks
    -   *Data type:* boolean
    -   *Description:*

        If set to true, email links will not directly manipulate the state of an user subscription,
        but check for the HTTP method first. In case of HEAD method, a confirmation page with a button
        will be shown. This should prevent double click issues by MicroSoft's SafeLinks feature. In case
        of other methods, the default action gets called immediately.

    -   *Default:*
-   -   *Property:* forceConfirmationButtonForEmailLinks
    -   *Data type:* boolean
    -   *Description:*

        If `enableConfirmationButtonForEmailLinks` is true and this setting is set to true,
        the confirmation page is always shown regardless of the HTTP method. This can help against
        the link preview features of some email clients.

    -   *Default:*
-   -   *Property:* captcha.jmrecaptcha
    -   *Data type:* String
    -   *Description:* Adapter for Captcha-Extension jm_recaptcha
    -   *Default:* \\Evoweb\\SfRegister\\Services\\Captcha\\JmRecaptchaAdapter
-   -   *Property:* captcha.srfreecap
    -   *Data type:* string
    -   *Description:* Adapter for Captcha-Extension sr_freecap
    -   *Default:* \\Evoweb\\SfRegister\\Services\\Captcha\\SrFreecapAdapter

### Persistence {#persistence}

plugin.tx_sfregister.persistence.\*

-   **storagePid**

    -   *Type:* integer

    Sysfolder with Frontend User records
