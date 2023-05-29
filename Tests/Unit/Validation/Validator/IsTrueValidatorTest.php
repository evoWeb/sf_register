<?php

namespace Evoweb\SfRegister\Tests\Unit\Validation\Validator;

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

use Evoweb\SfRegister\Validation\Validator\IsTrueValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class IsTrueValidatorTest extends UnitTestCase
{
    protected IsTrueValidator $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->getMockBuilder(IsTrueValidator::class)
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();
    }

    public function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    #[Test]
    public function isValidReturnsTrueIfTrueWasUsed()
    {
        self::assertFalse($this->subject->validate(true)->hasErrors());
    }

    public static function nonTrueValues(): array
    {
        return [
            'stringIsNonTrue' => ['true'],
            'integerIsNonTrue' => [1],
            'arrayIsNonTrue' => [[]],
        ];
    }

    #[Test]
    #[DataProvider('nonTrueValues')]
    public function isValidReturnsFalseIfNonTrueWasUsed(mixed $input): void
    {
        self::assertTrue($this->subject->validate($input)->hasErrors());
    }
}
