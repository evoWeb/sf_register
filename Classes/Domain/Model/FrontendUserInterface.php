<?php

namespace Evoweb\SfRegister\Domain\Model;

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

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Interface to be implemented by every frontend user
 * model that should be used with this registration
 */
interface FrontendUserInterface
{
    /**
     * Getter for uid.
     *
     * @return int|null The uid or NULL if none set yet.
     */
    public function getUid(): ?int;

    /**
     * Returns the username value
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     * Returns the password value
     *
     * @return string
     */
    public function getPassword(): string;

    /**
     * Sets the password value
     *
     * @param string $password
     */
    public function setPassword(string $password);

    /**
     * Returns the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @return ObjectStorage<FrontendUserGroup> An object storage containing the usergroup
     */
    public function getUsergroup(): ObjectStorage;

    /**
     * Returns the firstName value
     *
     * @return string
     */
    public function getFirstName(): string;

    /**
     * Returns the lastName value
     *
     * @return string
     */
    public function getLastName(): string;

    /**
     * Returns the email value
     *
     * @return string
     */
    public function getEmail(): string;

    public function getInvitationEmail(): string;
}
