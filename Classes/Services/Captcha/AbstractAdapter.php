<?php
namespace Evoweb\SfRegister\Services\Captcha;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
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

abstract class AbstractAdapter implements \Evoweb\SfRegister\Interfaces\CaptchaInterface
{
    /**
     * @var object
     */
    protected $captcha;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Renders the output of an concrete captcha
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Returns if the result of the validation was valid or not
     *
     * @param string $value
     *
     * @return bool
     */
    abstract public function isValid(string $value): bool;

    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    protected function addError(string $message, int $code)
    {
        $this->errors[] = new \TYPO3\CMS\Extbase\Validation\Error($message, $code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
