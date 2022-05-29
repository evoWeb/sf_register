<?php

namespace Evoweb\SfRegister\Services;

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

use TYPO3\CMS\Core\Authentication\AuthenticationService;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service to handle user login
 */
class AutoLogin extends AuthenticationService
{
    protected static bool $autoLoginActivated = false;

    /**
     * Find a user (eg. look up the user record in database when a login is sent)
     *
     * @return array User array or null
     */
    public function getUser(): ?array
    {
        session_start();
        $hmac = $_SESSION['sf-register-user'] ?? null;
        if ($hmac === null) {
            return null;
        }

        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = GeneralUtility::makeInstance(Registry::class);
        $userId = $registry->get('sf-register', $hmac);

        $registry->remove('sf-register', $hmac);
        unset($_SESSION['sf-register-user']);

        $user = $this->fetchUserRecord(
            $userId,
            '',
            array_merge($this->db_user, ['username_column' => 'uid', 'check_pid_clause' => ''])
        );

        self::$autoLoginActivated = (int)$userId > 0 && !empty($user);

        return is_array($user) ? $user : null;
    }

    /**
     * Authenticate a user based on a value set in session before redirect
     *
     * @param array $user Data of user.
     *
     * @return int >= 200: User authenticated successfully.
     *                     No more checking is needed by other auth services.
     *             >= 100: User not authenticated; this service is not responsible.
     *                     Other auth services will be asked.
     *             > 0:    User authenticated successfully.
     *                     Other auth services will still be asked.
     *             <= 0:   Authentication failed, no more checking needed
     *                     by other auth services.
     */
    public function authUser(array $user): int
    {
        $OK = 100;

        if (self::$autoLoginActivated) {
            $OK = 200;
        }

        return $OK;
    }
}
