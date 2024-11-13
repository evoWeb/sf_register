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

namespace Evoweb\SfRegister\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Interface to be implemented by every frontend user
 * model that should be used with this registration
 */
interface FrontendUserInterface extends DomainObjectInterface
{
    public function getUsername(): string;

    public function setUsername(string $username): void;

    public function getPassword(): string;

    public function setPassword(string $password): void;

    /**
     * @return ObjectStorage<FrontendUserGroup> An object storage containing the usergroup
     */
    public function getUsergroup(): ObjectStorage;

    public function getFirstName(): string;

    public function setFirstName(string $firstName): void;

    public function getLastName(): string;

    public function setLastName(string $lastName): void;

    public function getEmail(): string;

    public function setEmail(string $email): void;

    public function getInvitationEmail(): string;

    public function setInvitationEmail(string $invitationEmail): void;
}
