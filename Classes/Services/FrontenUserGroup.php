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

namespace Evoweb\SfRegister\Services;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\FrontendUserGroup;
use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;

class FrontenUserGroup
{
    public function __construct(protected FrontendUserGroupRepository $userGroupRepository)
    {
    }

    /**
     * Determines whether a user is in given user groups.
     */
    public function isUserInUserGroups(FrontendUser $user, array $userGroupUids): bool
    {
        $return = false;

        foreach ($userGroupUids as $userGroupUid) {
            if ($this->isUserInUserGroup($user, $userGroupUid)) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Determines whether a user is in a given user group.
     */
    protected function isUserInUserGroup(FrontendUser $user, int $userGroupUid): bool
    {
        $in = false;
        /** @var FrontendUserGroup $userGroup */
        foreach ($user->getUsergroup() as $userGroup) {
            $in = $in || $userGroup->getUid() == $userGroupUid;
        }
        return $in;
    }

    public function changeUsergroup(array $settings, FrontendUser $user, int $userGroupIdToAdd): FrontendUser
    {
        $this->removePreviousUserGroups($settings, $user);

        if ($userGroupIdToAdd) {
            /** @var FrontendUserGroup $userGroupToAdd */
            $userGroupToAdd = $this->userGroupRepository->findByUid($userGroupIdToAdd);
            $user->addUsergroup($userGroupToAdd);
        }

        return $user;
    }

    protected function removePreviousUserGroups(array $settings, FrontendUser $user): void
    {
        $userGroupIds = $this->getUserGroupIds($settings);
        $assignedUserGroups = $user->getUsergroup();
        foreach ($assignedUserGroups as $singleUserGroup) {
            if (in_array($singleUserGroup->getUid(), $userGroupIds)) {
                $assignedUserGroups->detach($singleUserGroup);
            }
        }
        $user->setUsergroup($assignedUserGroups);
    }

    public function getConfiguredUserGroups(array $settings, int $currentUserGroup): array
    {
        $userGroups = $this->getUserGroupIds($settings);
        $currentIndex = array_search($currentUserGroup, array_values($userGroups));

        $reducedUserGroups = [];
        if ($currentIndex !== false && $currentIndex < count($userGroups)) {
            $reducedUserGroups = array_slice($userGroups, $currentIndex);
        }

        return $reducedUserGroups;
    }

    protected function getUserGroupIds(array $settings): array
    {
        $settingsUserGroupKeys = $this->getUserGroupIdSettingKeys($settings);

        $userGroups = [];
        foreach ($settingsUserGroupKeys as $settingsUserGroupKey) {
            $userGroup = (int)($settings[$settingsUserGroupKey] ?? 0);
            if ($userGroup) {
                $userGroups[$settingsUserGroupKey] = $userGroup;
            }
        }

        return $userGroups;
    }

    /**
     * Return the keys of the TypoScript configuration in the order which is relevant for the configured
     * registration workflow
     */
    protected function getUserGroupIdSettingKeys(array $settings): array
    {
        $defaultOrder = [
            'usergroup',
            'usergroupPostSave',
            'usergroupPostConfirm',
            'usergroupPostAccept',
        ];

        // Admin    [plugin.tx_sfregister.settings.acceptEmailPostCreate]
        $confirmEmailPostCreate = (bool)($settings['confirmEmailPostCreate'] ?? false);
        // User     [plugin.tx_sfregister.settings.confirmEmailPostCreate]
        $acceptEmailPostConfirm = (bool)($settings['acceptEmailPostConfirm'] ?? false);

        // First User:confirm then Admin:accept
        if ($confirmEmailPostCreate && $acceptEmailPostConfirm) {
            return $defaultOrder;
        }

        // User     [plugin.tx_sfregister.settings.confirmEmailPostAccept]
        $acceptEmailPostCreate = (bool)($settings['acceptEmailPostCreate'] ?? false);
        // Admin    [plugin.tx_sfregister.settings.acceptEmailPostConfirm]
        $confirmEmailPostAccept = (bool)($settings['confirmEmailPostAccept'] ?? false);

        // First Admin:accept then User:confirm
        if ($acceptEmailPostCreate && $confirmEmailPostAccept) {
            return [
                'usergroup',
                'usergroupPostSave',
                'usergroupPostAccept',
                'usergroupPostConfirm',
            ];
        }

        return $defaultOrder;
    }
}
