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

use Evoweb\SfRegister\Domain\Model\FrontendUser;

final class EditAcceptEvent
{
    /**
     * @var FrontendUser
     */
    protected $user;

    /**
     * @var array
     */
    protected $settings;

    public function __construct(FrontendUser $user, array $settings)
    {
        $this->user = $user;
        $this->settings = $settings;
    }

    /**
     * @return FrontendUser
     */
    public function getUser(): FrontendUser
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
