<?php
namespace Evoweb\SfRegister\Services\Captcha;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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
 * Class AbstractAdapter
 *
 * @package Evoweb\SfRegister\Services\Captcha
 */
abstract class AbstractAdapter implements \Evoweb\SfRegister\Interfaces\CaptchaInterface
{
    /**
     * Captcha object
     *
     * @var object
     */
    protected $captcha = null;

    /**
     * Settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Renders the output of an concrete captcha
     *
     * @return string
     */
    abstract public function render();

    /**
     * Returns if the result of the validation was valid or not
     *
     * @param string $value
     *
     * @return bool
     */
    abstract public function isValid($value);

    /**
     * Setter for settings
     *
     * @param array $settings
     *
     * @return void
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Creates a new validation error object and adds it to $this->errors
     *
     * @param string $message The error message
     * @param integer $code The error code (a unix timestamp)
     *
     * @return void
     */
    protected function addError($message, $code)
    {
        $this->errors[] = new \TYPO3\CMS\Extbase\Validation\Error($message, $code);
    }

    /**
     * Getter for errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
