<?php
namespace Evoweb\SfRegister\Tests\Domain\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-18 Sebastian Fischer <typo3@evoweb.de>
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

class IsTrueValidatorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\IsTrueValidator
     */
    protected $fixture;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->fixture = new \Evoweb\SfRegister\Validation\Validator\IsTrueValidator();
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     * @return void
     */
    public function isValidReturnsTrueIfTrueWasUsed()
    {
        $this->assertTrue(
            $this->fixture->isValid(true)
        );
    }

    /**
     * @return array
     */
    public function isValidNonTrueDataProvider()
    {
        return array(
            'stringIsNonTrue' => array('true'),
            'integerIsNonTrue' => array(1),
            'arrayIsNonTrue' => array(array()),
        );
    }

    /**
     * @param mixed $input
     * @test
     * @return void
     * @dataProvider isValidNonTrueDataProvider
     */
    public function isValidReturnsFalseIfNonTrueWasUsed($input)
    {
        $this->assertFalse($this->fixture->isValid($input));
    }
}
