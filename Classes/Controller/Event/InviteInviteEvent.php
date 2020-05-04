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

final class InviteInviteEvent
{
    /**
     * @var FrontendUser
     */
    protected $user;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $doNotSendInvitation;

    public function __construct(FrontendUser $user, array $settings, bool $doNotSendInvitation)
    {
        $this->user = $user;
        $this->settings = $settings;
        $this->doNotSendInvitation = $doNotSendInvitation;
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

    /**
     * @return bool
     */
    public function isDoNotSendInvitation(): bool
    {
        return $this->doNotSendInvitation;
    }

    /**
     * @param bool $doNotSendInvitation
     */
    public function setDoNotSendInvitation(bool $doNotSendInvitation): void
    {
        $this->doNotSendInvitation = $doNotSendInvitation;
    }
}
