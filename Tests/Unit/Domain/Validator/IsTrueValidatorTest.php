<?php
namespace Evoweb\SfRegister\Tests\Unit\Domain\Validator;

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

    public function setUp()
    {
        $this->subject = new \Evoweb\SfRegister\Validation\Validator\IsTrueValidator();
    }

    public function tearDown()
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function isValidReturnsTrueIfTrueWasUsed()
    {
        $this->assertTrue($this->subject->isValid(true));
    }

    /**
     * @return array
     */
    public function isValidNonTrueDataProvider()
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
     * @dataProvider isValidNonTrueDataProvider
     */
    public function isValidReturnsFalseIfNonTrueWasUsed($input)
    {
        $this->assertFalse($this->subject->isValid($input));
    }
}
