<?php

namespace Evoweb\SfRegister\Services\Captcha;

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

use Evoweb\SfRegister\Interfaces\CaptchaInterface;
use TYPO3\CMS\Extbase\Validation\Error;

abstract class AbstractAdapter implements CaptchaInterface
{
    protected ?object $captcha;

    protected array $settings = [];

    protected array $errors = [];

    /**
     * Renders the output of an concrete captcha
     *
     * @return string|array
     */
    abstract public function render();

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
        $this->errors[] = new Error($message, $code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
