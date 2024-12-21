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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FrontendUserGroup extends AbstractEntity
{
    protected string $title = '';

    protected string $description = '';

    protected int $feloginRedirectPid = 0;

    /**
     * Keep in mind that the property is called "subgroup" although it can hold several subgroups.
     *
     * @var ObjectStorage<FrontendUserGroup>
     */
    protected ObjectStorage $subgroup;

    public function __construct(string $title = '')
    {
        $this->title = $title;
        $this->initializeObject();
    }

    public function initializeObject(): void
    {
        $this->subgroup = new ObjectStorage();
    }

    /**
     * @return ObjectStorage<FrontendUserGroup>
     */
    public function getSubgroup(): ObjectStorage
    {
        return $this->subgroup;
    }

    /**
     * @param ObjectStorage<FrontendUserGroup> $subgroup
     */
    public function setSubgroup(ObjectStorage $subgroup): void
    {
        $this->subgroup = $subgroup;
    }

    public function addSubgroup(FrontendUserGroup $subgroup): void
    {
        $this->subgroup->attach($subgroup);
    }

    public function removeSubgroup(FrontendUserGroup $subgroup): void
    {
        $this->subgroup->detach($subgroup);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getFeloginRedirectPid(): int
    {
        return $this->feloginRedirectPid;
    }

    public function setFeloginRedirectPid(int $feloginRedirectPid): void
    {
        $this->feloginRedirectPid = $feloginRedirectPid;
    }
}
