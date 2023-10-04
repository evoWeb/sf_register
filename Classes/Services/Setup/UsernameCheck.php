<?php

namespace Evoweb\SfRegister\Services\Setup;

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

class UsernameCheck implements CheckInterface
{
    public function check(array $settings): ?ResponseInterface
    {
        $result = null;
        if (
            ($settings['useEmailAddressAsUsername'] ?? false)
            || !in_array('username', $settings['fields']['selected'])
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
