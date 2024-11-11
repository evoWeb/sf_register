<?php

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

namespace Evoweb\SfRegister\Services\Captcha;

use TYPO3\CMS\Extbase\Validation\Error;

abstract class AbstractAdapter implements CaptchaInterface
{
    protected ?object $captcha = null;

    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * @var Error[]
     */
    protected array $errors = [];

    /**
     * Renders the output of a concrete captcha
     * @return array<string, string>|string
     */
    abstract public function render(): array|string;

    /**
     * Returns if the result of the validation was valid or not
     */
    abstract public function isValid(string $value): bool;

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    protected function addError(string $message, int $code): void
    {
        $this->errors[] = new Error($message, $code);
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
