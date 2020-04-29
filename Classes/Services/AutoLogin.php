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

/**
 * Service to handle user login
 */
class AutoLogin extends \TYPO3\CMS\Core\Authentication\AuthenticationService
{
    /**
     * @var bool
     */
    protected static $autoLoginActivated = false;

    /**
     * Find a user (eg. look up the user record in database when a login is sent)
     *
     * @return array|bool User array or FALSE
     */
    public function getUser()
    {
        session_start();
        $hmac = $_SESSION['sf-register-user'];

        /** @var \TYPO3\CMS\Core\Registry $registry */
        $registry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Registry::class);
        $userId = $registry->get('sf-register', $hmac);

        $registry->remove('sf-register', $hmac);
        unset($_SESSION['sf-register-user']);

        $user = $this->fetchUserRecord(
            $userId,
            '',
            array_merge($this->db_user, ['username_column' => 'uid','check_pid_clause' => ''])
        );

        self::$autoLoginActivated = intval($userId) > 0 && !empty($user);

        return $user;
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
