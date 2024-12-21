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

use TYPO3\CMS\Core\Authentication\AuthenticationService;
use TYPO3\CMS\Core\Registry;

/**
 * Service to handle user login
 */
class AutoLogin extends AuthenticationService
{
    public function __construct(protected Registry $registry)
    {
    }

    /**
     * Find a user (e.g. look up the user record in database when a login is sent)
     * @return array<string, mixed>|null
     */
    public function getUser(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $hmac = $_SESSION[FrontendUser::SESSION_KEY] ?? null;
        if ($hmac === null) {
            return null;
        }
        unset($_SESSION[FrontendUser::SESSION_KEY]);

        $userId = (string)$this->registry->get('sf-register', $hmac);
        $this->registry->remove('sf-register', $hmac);

        $dbUserSetup = [...$this->db_user, 'username_column' => 'uid', 'enable_clause' => ''];
        // @extensionScannerIgnoreLine
        $user = $this->fetchUserRecord($userId, '', $dbUserSetup);

        if (!empty($user)) {
            $user[FrontendUser::ADDITIONAL_SECRET] = true;
        }

        return is_array($user) ? $user : null;
    }

    /**
     * Authenticate a user based on a value set in session before redirect
     * @param array<string, mixed> $user
     * @return int = 200: User authenticated successfully.
     *                    No more checking is needed by other auth services.
     *             = 100: User not authenticated; this service is not responsible.
     *                    Other auth services will be asked.
     */
    public function authUser(array $user): int
    {
        return ($user[FrontendUser::ADDITIONAL_SECRET] ?? false) ? 200 : 100;
    }
}
