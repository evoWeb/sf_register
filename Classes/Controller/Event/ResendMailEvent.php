<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Controller\Event;

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

use Evoweb\SfRegister\Domain\Model\Email;

final class ResendMailEvent
{
    /**
     * @var Email
     */
    protected $email;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(Email $email, array $settings)
    {
        $this->email = $email;
        $this->settings = $settings;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
