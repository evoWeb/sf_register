<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Services\Event;

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

use Evoweb\SfRegister\Domain\Model\FrontendUser;

abstract class AbstractEventWithUser
{
    protected FrontendUser $user;

    protected array $settings;

    protected array $arguments;

    public function __construct(FrontendUser $user, array $settings, array $arguments)
    {
        $this->user = $user;
        $this->settings = $settings;
        $this->arguments = $arguments;
    }

    public function getUser(): FrontendUser
    {
        return $this->user;
    }

    public function getResult(): FrontendUser
    {
        return $this->user;
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
