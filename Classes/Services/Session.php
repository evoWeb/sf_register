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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\SetCookieService;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\CMS\Core\Session\UserSessionManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Service to handle the user session
 */
class Session implements SingletonInterface
{
    /**
     * String to identify session values
     */
    protected string $sessionName = 'evoweb-sfregister-session';

    protected string $sessionKey = 'sf_register';

    /**
     * Values stored in session
     * @var array<string, mixed>
     */
    protected ?array $values = null;

    protected UserSessionManager $userSessionManager;

    protected UserSession $session;

    public function initializeUserSessionManager(?UserSessionManager $userSessionManager = null): void
    {
        $this->userSessionManager = $userSessionManager ?? UserSessionManager::create('FE');
        $this->session = $this->userSessionManager->createFromRequestOrAnonymous(
            $this->getRequest(),
            $this->sessionName,
        );
        $this->fetch();
    }

    public function fetch(): self
    {
        if ($this->values === null) {
            $sessionValue = $this->session->get($this->sessionKey);
            if (!empty($sessionValue)) {
                $this->values = unserialize($sessionValue);
            }
            if (!is_array($this->values)) {
                $this->values = [];
            }
        }

        return $this;
    }

    public function store(): self
    {
        $this->session->set($this->sessionKey, serialize($this->values));
        $this->userSessionManager->updateSession($this->session);
        $setCookieService = SetCookieService::create($this->sessionName, 'FE');
        $normalizedParams = NormalizedParams::createFromRequest($this->getRequest());
        $setCookieService->setSessionCookie($this->session, $normalizedParams);
        return $this;
    }

    public function has(string $key): bool
    {
        $result = false;

        if (array_key_exists($key, $this->values)) {
            $result = true;
        }

        return $result;
    }

    public function get(string $key): mixed
    {
        $result = null;

        if ($this->has($key)) {
            $result = $this->values[$key];
        }

        return $result;
    }

    public function set(string $key, mixed $value): self
    {
        $this->values[$key] = $value;

        return $this->store();
    }

    public function remove(string $key): self
    {
        if ($this->has($key)) {
            unset($this->values[$key]);
        }

        return $this->store();
    }

    public function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
