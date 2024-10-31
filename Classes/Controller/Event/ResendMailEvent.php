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

namespace Evoweb\SfRegister\Controller\Event;

use Evoweb\SfRegister\Domain\Model\Email;

final class ResendMailEvent
{
    public function __construct(protected Email $email, protected array $settings) {}

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
