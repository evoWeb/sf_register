<?php
namespace Evoweb\SfRegister\Tests\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This class is a backport of the corresponding class of FLOW3.
 *  All credits go to the v5 team.
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class PasswordTest
 */
class PasswordTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Domain\Model\Password
     */
    protected $fixture;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->fixture = new \Evoweb\SfRegister\Domain\Model\Password();
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
    public function passwordOnInitializeIsNull()
    {
        $this->assertNull(
            $this->fixture->getPassword()
        );
    }

    /**
     * @test
     * @return void
     */
    public function passwordReturnsStringSetBySetPassword()
    {
        $expected = 'teststring';

        $this->fixture->setPassword($expected);

        $this->assertSame(
            $expected,
            $this->fixture->getPassword()
        );
    }

    /**
     * @test
     * @return void
     */
    public function passwordRepeatOnInitializeIsNull()
    {
        $this->assertNull(
            $this->fixture->getPasswordRepeat()
        );
    }

    /**
     * @test
     * @return void
     */
    public function passwordAgainReturnsStringSetBySetPassword()
    {
        $expected = 'teststring';

        $this->fixture->setPasswordRepeat($expected);

        $this->assertSame(
            $expected,
            $this->fixture->getPasswordRepeat()
        );
    }

    /**
     * @test
     * @return void
     */
    public function oldPasswordOnInitializeIsNull()
    {
        $this->assertNull(
            $this->fixture->getOldPassword()
        );
    }

    /**
     * @test
     * @return void
     */
    public function oldPasswordReturnsStringSetBySetPassword()
    {
        $expected = 'teststring';

        $this->fixture->setOldPassword($expected);

        $this->assertSame(
            $expected,
            $this->fixture->getOldPassword()
        );
    }
}
