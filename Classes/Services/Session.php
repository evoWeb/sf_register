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

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Service to handle the user session
 */
class Session implements SingletonInterface
{
    /**
     * String to identify session values
     *
     * @var string
     */
    protected string $sessionKey = 'sf_register';

    /**
     * Values stored in session
     *
     * @var ?array
     */
    protected ?array $values = null;

    protected ?FrontendUserAuthentication $frontendUser;

    public function __construct(?FrontendUserAuthentication $frontendUser = null)
    {
        if ($GLOBALS['TSFE']->fe_user) {
            $this->frontendUser = $GLOBALS['TSFE']->fe_user;
        } else {
            $this->frontendUser = $frontendUser;
            $this->frontendUser->start($GLOBALS['TYPO3_REQUEST']);
        }
        $this->fetch();
    }

    public function fetch(): self
    {
        if ($this->values === null) {
            $sessionValue = $this->frontendUser->getKey('ses', $this->sessionKey);
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
        $this->frontendUser->setKey('ses', $this->sessionKey, serialize($this->values));

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
}
