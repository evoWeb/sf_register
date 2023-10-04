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

class UserGroupCheck implements CheckInterface
{
    public function check(array $settings): ?ResponseInterface
    {
        $result = null;
        if (
            ($settings['notifyUser']['createSave'] ?? false)
            || (empty($settings['usergroupPostSave']) || empty($settings['usergroupPostConfirm']))
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
