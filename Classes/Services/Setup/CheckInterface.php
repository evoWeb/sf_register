<?php

declare(strict_types=1);

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

interface CheckInterface
{
    /**
     * @param array<string, string|array<string, int|array<int, string>>> $settings
     */
    public function check(array $settings): ?ResponseInterface;
}
