<?php

namespace Evoweb\SfRegister\Services\Setup;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

class UserGroupCheck
{
    public function check($settings): ?ResponseInterface
    {
        $result = null;
        if (
            $settings['notifyUser']['createSave']
            && (empty($settings['usergroupPostSave']) || empty($settings['usergroupPostConfirm']))
        ) {
            $result = new HtmlResponse(
                '<h3>Please check your setup.</h3>
                You need to configure usergroups to get double optin to work as intended.<br>
                Please have a look into TypoScript <b>constants</b>:
                <ul>
                <li>plugin.tx_sfregister.settings.usergroupPostSave</li>
                <li>plugin.tx_sfregister.settings.usergroupPostConfirm</li>
                </ul>'
            );
        }
        return $result;
    }
}
