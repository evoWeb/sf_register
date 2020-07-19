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

class IsTrueValidatorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\IsTrueValidator
     */
    protected $subject;

    public function setUp(): void
    {
        $this->subject = $this->getMockBuilder(\Evoweb\SfRegister\Validation\Validator\IsTrueValidator::class)
            ->setMethods(['translateErrorMessage'])
            ->getMock();
    }

    public function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function isValidReturnsTrueIfTrueWasUsed()
    {
        $this->assertFalse($this->subject->validate(true)->hasErrors());
    }

    /**
     * @return array
     */
    public function nonTrueValues()
    {
        return [
            'stringIsNonTrue' => ['true'],
            'integerIsNonTrue' => [1],
            'arrayIsNonTrue' => [[]],
        ];
    }

    /**
     * @param mixed $input
     * @test
     * @dataProvider nonTrueValues
     */
    public function isValidReturnsFalseIfNonTrueWasUsed($input)
    {
        $this->assertTrue($this->subject->validate($input)->hasErrors());
    }
}
