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

namespace Evoweb\SfRegister\Tests\Unit\Services\Captcha;

/**
 * Stand-in for SJBR\SrFreecap\PiBaseApi (which does not exist in this test environment, see
 * SrFreecapAdapterTest::createCaptchaServiceStub()). Only exposes what SrFreecapAdapter::isValid()
 * actually calls, plus call tracking so delegation and pass-through can be asserted.
 */
class CaptchaServiceStub
{
    public int $checkWordCallCount = 0;

    public ?string $receivedValue = null;

    public function __construct(private readonly bool $checkWordResult) {}

    public function checkWord(string $value): bool
    {
        $this->checkWordCallCount++;
        $this->receivedValue = $value;

        return $this->checkWordResult;
    }
}
