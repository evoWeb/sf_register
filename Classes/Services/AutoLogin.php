<?php
namespace Evoweb\SfRegister\Services;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Service to handle user login
 */
class AutoLogin extends \TYPO3\CMS\Sv\AuthenticationService
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
