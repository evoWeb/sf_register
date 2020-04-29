<?php

namespace Evoweb\SfRegister\Interfaces;

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
 * Interface to be implemented by every captcha adapter that should get used with this registration
 */
interface CaptchaInterface
{
    /**
     * Getter for errors that needs to be implemented in every adapter
     */
    public function getErrors(): array;
}
