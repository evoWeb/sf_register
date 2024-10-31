<?php

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

namespace Evoweb\SfRegister\Services\Setup;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

class UserGroupCheck implements CheckInterface
{
    public function check(array $settings): ?ResponseInterface
    {
        $result = null;
        if (
            // notify user and post save is set but confirm save isn't
            (
                ($settings['notifyUser']['createSave'] ?? false)
                && ($settings['usergroupPostSave'] ?? false)
                && !($settings['usergroupPostConfirm'] ?? false)
            )
            // notify user and post confirm is set but post save isn't
            || (
                ($settings['notifyUser']['createSave'] ?? false)
                && !($settings['usergroupPostSave'] ?? false)
                && ($settings['usergroupPostConfirm'] ?? false)
            )
            // postSave and postConfirm are set but notify user isn't
            || (
                ($settings['usergroupPostSave'] ?? false)
                && ($settings['usergroupPostConfirm'] ?? false)
                && !($settings['notifyUser']['createSave'] ?? false)
            )
        ) {
            $result = new HtmlResponse(
                '<h3>Please check your setup.</h3>
                You need to configure usergroups to get double optin to work as intended.<br>
                Please have a look into TypoScript <b>constants</b>:
                <ul>
                <li>plugin.tx_sfregister.settings.notifyUser.createSave</li>
                <li>plugin.tx_sfregister.settings.usergroupPostSave</li>
                <li>plugin.tx_sfregister.settings.usergroupPostConfirm</li>
                </ul>'
            );
        }
        return $result;
    }
}
