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

use PHPUnit\Framework\MockObject\MockObject;

class UniqueValidatorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\UniqueValidator
     */
    protected $subject;

    public function setUp(): void
    {
        $this->subject = $this->getMockBuilder(\Evoweb\SfRegister\Validation\Validator\UniqueValidator::class)
            ->setMethods(['translateErrorMessage'])
            ->getMock();
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

        $this->assertFalse($this->subject->validate($expected)->hasErrors());
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

        $this->assertTrue($this->subject->validate($expected)->hasErrors());
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

        $this->assertFalse($this->subject->validate($expected)->hasErrors());
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

        $this->assertTrue($this->subject->validate($expected)->hasErrors());
    }
}
