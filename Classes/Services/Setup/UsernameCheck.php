<?php

namespace Evoweb\SfRegister\Services\Setup;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

class UsernameCheck
{
    public function check($settings): ?ResponseInterface
    {
        $result = null;
        if (
            $settings['useEmailAddressAsUsername']
            && in_array('username', $settings['fields']['selected'])
        ) {
            $result = new HtmlResponse(
                '<h3>Please check your setup.</h3>
                If the email should be used as username the field username should be left out of the selected fields<br>
                Please have a look into TypoScript <b>setup</b>:
                <ul>
                  <li>selected fields in the registration plugin</li>
                  <li>plugin.tx_sfregister.settings.useEmailAddressAsUsername</li>
                  <li>plugin.tx_sfregister.settings.fields.selected</li>
                </ul>'
            );
        }
        return $result;
    }
}
