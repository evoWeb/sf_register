<?php

namespace Evoweb\SfRegister\Interfaces;

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

/**
 * Interface to be implemented by every frontend user
 * model that should be used with this registration
 */
interface FrontendUserInterface
{
    /**
     * Returns the username value
     *
     * @return string
     */
    public function getUsername();

    /**
     * Returns the firstName value
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Returns the lastName value
     *
     * @return string
     */
    public function getLastName();

    /**
     * Returns the email value
     *
     * @return string
     */
    public function getEmail();

    public function getInvitationEmail(): string;
}
