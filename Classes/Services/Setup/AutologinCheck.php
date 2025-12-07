<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Services\Setup;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\HtmlResponse;

class AutologinCheck implements CheckInterface
{
    /**
     * @param array<string, string|array<string, int|array<int, string>>> $settings
     */
    public function check(array $settings): ?ResponseInterface
    {
        $result = null;
        if (
            (
                ($settings['confirmEmailPostCreate'] ?? false)
                || ($settings['acceptEmailPostCreate'] ?? false)
            )
            && $settings['autologinPostRegistration'] ?? false
        ) {
            $result = new HtmlResponse(
                '<h3>Please check your setup.</h3>
                Having
                <ul>
                    <li>plugin.tx_sfregister.settings.confirmEmailPostCreate</li>
                    <li>plugin.tx_sfregister.settings.acceptEmailPostCreate</li>
                </ul>
                <p>activated disallows also activating <b>plugin.tx_sfregister.settings.autologinPostRegistration</b>.</p>
                <p>This is because the user needs to be activated before the record can be logged in.</p>'
            );
        }
        return $result;
    }
}
