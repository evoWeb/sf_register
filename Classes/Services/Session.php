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
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Service to handle values in the session of the frontend user
 *
 * Values are stored in the session data of the core frontend user, which takes
 * care of persisting the anonymous session and sending the session cookie.
 */
class Session implements SingletonInterface
{
    protected string $sessionKey = 'sf_register';

    /**
     * Values stored in session
     * @var array<string, mixed>
     */
    protected ?array $values = null;

    public function fetch(): self
    {
        if ($this->values === null) {
            $sessionValue = $this->getFrontendUser()?->getSessionData($this->sessionKey);
            $this->values = is_array($sessionValue) ? $sessionValue : [];
        }

        return $this;
    }

    public function store(): self
    {
        // null removes the key, so no session gets persisted for empty values
        $this->getFrontendUser()?->setAndSaveSessionData($this->sessionKey, $this->values ?: null);
        return $this;
    }

    public function has(string $key): bool
    {
        $this->fetch();
        return array_key_exists($key, $this->values ?? []);
    }

    public function get(string $key): mixed
    {
        $this->fetch();
        return $this->values[$key] ?? null;
    }

    public function set(string $key, mixed $value): self
    {
        $this->fetch();
        $this->values[$key] = $value;

        return $this->store();
    }

    public function remove(string $key): self
    {
        if ($this->has($key)) {
            unset($this->values[$key]);
            $this->store();
        }

        return $this;
    }

    protected function getFrontendUser(): ?FrontendUserAuthentication
    {
        $frontendUser = $this->getRequest()?->getAttribute('frontend.user');
        return $frontendUser instanceof FrontendUserAuthentication ? $frontendUser : null;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        return $request instanceof ServerRequestInterface ? $request : null;
    }
}
