<?php
namespace Evoweb\SfRegister\Tests\Domain\Validator;

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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class UniqueValidatorTest
 */
class UniqueValidatorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\UniqueValidator
     */
    protected $subject;

    public function setUp()
    {
        /** @var \Evoweb\SfRegister\Validation\Validator\UniqueValidator $fixture */
        $this->subject = $this->getAccessibleMock(
            \Evoweb\SfRegister\Validation\Validator\UniqueValidator::class,
            ['dummy'],
            ['global' => false]
        );
    }

    /**
     * @test
     */
    public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(\Evoweb\SfRegister\Domain\Repository\FrontendUserRepository::class);
        $repositoryMock->expects($this->once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->will($this->returnValue(0));
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        $this->assertTrue($this->subject->isValid($expected));
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfCountOfValueInFieldReturnsHigherThenZeroForLocalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(\Evoweb\SfRegister\Domain\Repository\FrontendUserRepository::class);
        $repositoryMock->expects($this->once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->will($this->returnValue(1));
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        $this->assertFalse($this->subject->isValid($expected));
    }

    /**
     * @test
     */
    public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalAndGlobalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(\Evoweb\SfRegister\Domain\Repository\FrontendUserRepository::class);
        $repositoryMock->expects($this->once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->will($this->returnValue(0));
        $repositoryMock->expects($this->any())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->will($this->returnValue(0));
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        $this->assertTrue($this->subject->isValid($expected));
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfCountOfValueInFieldReturnsZeroForLocalAndHigherThenZeroForGlobalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(
            \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository::class
        );
        $repositoryMock->expects($this->once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->will($this->returnValue(0));
        $repositoryMock->expects($this->any())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->will($this->returnValue(1));
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        $this->assertFalse($this->subject->isValid($expected));
    }
}
