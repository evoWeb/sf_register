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
 * Service to handle the user session
 */
class Session implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * String to identify session values
     *
     * @var string
     */
    protected $sessionKey = 'sf_register';

    /**
     * Values stored in session
     *
     * @var array
     */
    protected $values = null;

    /**
     * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication
     */
    protected $frontendUser;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->frontendUser = &$GLOBALS['TSFE']->fe_user;
        $this->fetch();
    }

    public function fetch(): self
    {
        if ($this->values === null) {
            $this->values = (array) unserialize($this->frontendUser->getKey('ses', $this->sessionKey));
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

    /**
     * Getter for value identified by key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        $result = null;

        if ($this->has($key)) {
            $result = $this->values[$key];
        }

        return $result;
    }

    /**
     * Setter for key to value
     *
     * @param string $key
     * @param mixed $value
     *
     * @return \Evoweb\SfRegister\Services\Session
     */
    public function set(string $key, $value): self
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
