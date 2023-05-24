<?php

namespace Evoweb\SfRegister\Tests\Unit\Domain\Model;

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

use Evoweb\SfRegister\Domain\Model\Password;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class PasswordTest extends UnitTestCase
{
    protected Password $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Password();
    }

    public function tearDown(): void
    {
        unset($this->subject);
    }

    #[Test]
    public function passwordOnInitializeIsEmptyString(): void
    {
        self::assertEquals('', $this->subject->getPassword());
    }

    #[Test]
    public function passwordReturnsStringSetBySetPassword(): void
    {
        $expected = 'test string';

        $this->subject->setPassword($expected);

        self::assertSame($expected, $this->subject->getPassword());
    }

    #[Test]
    public function passwordRepeatOnInitializeIsEmptyString(): void
    {
        self::assertEquals('', $this->subject->getPasswordRepeat());
    }

    #[Test]
    public function passwordAgainReturnsStringSetBySetPassword(): void
    {
        $expected = 'test string';

        $this->subject->setPasswordRepeat($expected);

        self::assertSame($expected, $this->subject->getPasswordRepeat());
    }

    #[Test]
    public function oldPasswordOnInitializeIsEmptyString(): void
    {
        self::assertEquals('', $this->subject->getOldPassword());
    }

    #[Test]
    public function oldPasswordReturnsStringSetBySetPassword(): void
    {
        $expected = 'test string';

        $this->subject->setOldPassword($expected);

        self::assertSame($expected, $this->subject->getOldPassword());
    }
}
