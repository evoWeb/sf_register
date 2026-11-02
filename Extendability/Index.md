---
title: "Extendability"
manual: "Frontend User Registration: Multipurpose configurable registration"
version: "14.0.4"
permalink: "https://docs.typo3.org/permalink/evoweb/sf-register:extendability@14.0.4"
source: "Extendability/Index.rst"
rendered: "2026-11-02T18:38:00+00:00"
---

# Extendability {#extendability}

-   [Use your own flavor of fields](https://docs.typo3.org/permalink/evoweb/sf-register:use-your-own-flavor-of-fields@14.0.4)
-   [Adding custom properties](https://docs.typo3.org/permalink/evoweb/sf-register:adding-custom-properties@14.0.4)
-   [Bring in your own captcha](https://docs.typo3.org/permalink/evoweb/sf-register:bring-in-your-own-captcha@14.0.4)
-   [PSR-14 Events](https://docs.typo3.org/permalink/evoweb/sf-register:psr-14-events@14.0.4)
-   [How to implement a slot](https://docs.typo3.org/permalink/evoweb/sf-register:how-to-implement-a-slot@14.0.4)
-   [Available events](https://docs.typo3.org/permalink/evoweb/sf-register:available-events@14.0.4)

## Use your own flavor of fields {#createcustomfields}

Since version 8.8.0 its easier then ever to user your own fields. Just add your
partial folder via typoscript and register your own field configuration. After
that you need to tell the user ts config that the new type is available to
be selected in the plugin too.

**Fields.typoscript**

```typoscript
plugin.tx_sfregister.view.partialRootPaths.50 = EXT:your_extension/Resources/Private/Partials/
plugin.tx_sfregister.settings.fields.configuration {
    your_field_key {
        partial = YourFieldKey
        # @see Feature: #93334 - Translation Domain Mapping
        backendLabel = your_extension.be:your_field_key
    }
}
```

**ext_localconf.php**

```php
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '@import \'EXT:sf_register/Configuration/TypoScript/Fields.typoscript\''
);
```

**Setup**

```php
@import 'EXT:sf_register/Configuration/TypoScript/Fields.typoscript'
```

By using the same fields file both in typoscript as well as in user ts config.
No additional configuration is needed.

In your partials there are the following information available

-   `{user}` the user object with previous entered values
-   `{fieldName}` the name of the field in the user object
-   `{options}` every value that is inside of the field config {partial, backendLabel, etc}
-   `{settings}` the general plugin settings

## Adding custom properties {#addcustomproperties}

Since late the frontend user domain model can be extended. This can be done the
extension [evoweb/extender](https://github.com/evoWeb/extender) which sole purpose is to extend extbase domain models.
There is an [example](https://github.com/evoWeb/ew_sfregister_extended) on how to use the extender.

If you run into problems extending please be aware that the only solution
supported is by the use of 'extender'.

In this example its noteworthy that the last array key is not required but advised.
The path to the file matches extension key and extbase compatible path to the domain model.

For highlighting purpose its advised to let the domain model extend
from `\Evoweb\SfRegister\Domain\Model\FrontendUser`

**ext_localconf.php**

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sf_register']['extender'][
    \Evoweb\SfRegister\Domain\Model\FrontendUser::class
]['site-package'] = 'EXT:site_package/Classes/Domain/Model/FrontendUser.php';
```

Beside extending the domain model with property and get-/set-method a field
needs to be created for sql and registered in TCA.

**ext_tables.sql**

```sql
#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
    extending varchar(60) DEFAULT ''
);
```

**TCA/Overrides/fe_users.php**

```php
$temporaryColumns = [
    'extending' => [
        'exclude' => 1,
        'label' => 'extending',
        'config' => [
            'type' => 'input',
            'readOnly' => TRUE,
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $temporaryColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'extending');
```

## Bring in your own captcha {#addcustomcaptcha}

By implementing a adapter which is extending the
`\Evoweb\SfRegister\Services\Captcha\AbstractAdapter` you are able to add
an own captcha. The adapter now then has to be configured to be usable by adding
typoscript settings like the following taken from [recaptcha](https://github.com/evoWeb/recaptcha):

**Captcha.typoscript**

```typoscript
plugin.tx_sfregister.settings {
    # register recaptcha as captcha possibility
    captcha.recaptcha = Evoweb\Recaptcha\Adapter\SfRegisterAdapter

    fields {
        configuration {
            # change captcha field type to recaptcha
            captcha.type = Recaptcha
        }
    }

    validation.create {
        # tell validation to use recaptcha adapter
        captcha = Evoweb\SfRegister\Validation\Validator\CaptchaValidator(type = recaptcha)
    }
}
```

## PSR-14 Events {#psr14event}

This kind of event is superseding Hooks and Signal-Slots in TYPO3 and are the
way to go. That's why all signals are replaced with their event counterparts.

## How to implement a slot {#how-to-implement-a-slot}

An [overview](https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Hooks/EventDispatcher/Index.html) on how to configure and interact with events was given on the
Developer Days in 2019. The detailed example shows how to configure them in the
Services.yaml:

**YourEventListener.php**

```php
use Evoweb\SfRegister\Controller\Event\ProcessInitializeActionEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;

class YourEventListener
{
    #[AsEventListener('your-extension-identifier', ProcessInitializeActionEvent::class)]
    public function __invoke(ProcessInitializeActionEvent $event): void
    {
    }
}
```

The code above shows how to get an event listener is registered to an event.

## Available events {#available-events}

### FeuserController {#feusercontroller}

-   **Evoweb\\SfRegister\\Controller\\Event\\InitializeActionEvent**

    -   *$controller:* `\Evoweb\SfRegister\Controller\FeuserController`
    -   *$settings:* `array`
    -   *$response:* `\Psr\Http\Message\ResponseInterface`

### FeuserCreateController {#feusercreatecontroller}

-   **Evoweb\\SfRegister\\Controller\\Event\\CreateFormEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\CreatePreviewEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\CreateSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\CreateConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\CreateRefuseEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\CreateAcceptEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\CreateDeclineEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

### FeuserDeleteController {#feuserdeletecontroller}

-   **Evoweb\\SfRegister\\Controller\\Event\\DeleteFormEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\DeleteSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\DeleteConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

### FeuserEditController {#feusereditcontroller}

-   **Evoweb\\SfRegister\\Controller\\Event\\EditFormEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\EditPreviewEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\EditSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\EditConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\EditAcceptEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

### FeuserInviteController {#feuserinvitecontroller}

-   **Evoweb\\SfRegister\\Controller\\Event\\InviteFormEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\InviteInviteEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

### FeuserPasswordController {#feuserpasswordcontroller}

-   **Evoweb\\SfRegister\\Controller\\Event\\PasswordFormEvent**

    -   *$password:* `\Evoweb\SfRegister\Domain\Model\Password`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\PasswordSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

### FeuserResendController {#feuserresendcontroller}

-   **Evoweb\\SfRegister\\Controller\\Event\\ResendFormEvent**

    -   *$email:* `\Evoweb\SfRegister\Domain\Model\Email`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Controller\\Event\\ResendMailEvent**

    -   *$email:* `\Evoweb\SfRegister\Domain\Model\Email`
    -   *$settings:* `array`

### Mail {#mail}

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminCreateAcceptEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminCreateConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminCreateDeclineEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminCreateRefuseEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminCreateSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminDeleteConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminDeleteSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminEditAcceptEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminEditConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminEditSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminInviteInviteEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyAdminResendMailEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserCreateAcceptEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserCreateConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserCreateDeclineEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserCreateRefuseEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserCreateSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserDeleteConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserDeleteSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserEditAcceptEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserEditConfirmEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserEditSaveEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserInviteInviteEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\NotifyUserResendMailEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\InvitationToRegisterEvent**

    -   *$user:* `\Evoweb\SfRegister\Domain\Model\FrontendUser`
    -   *$settings:* `array`

-   **Evoweb\\SfRegister\\Services\\Event\\PreSubmitMailEvent**

    -   *$mail:* `\TYPO3\CMS\Core\Mail\MailMessage`
    -   *$settings:* `array`
    -   *$arguments:* `array`
