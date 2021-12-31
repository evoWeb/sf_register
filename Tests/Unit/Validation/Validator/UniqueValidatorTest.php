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

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UniqueValidatorTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\UniqueValidator
     */
    protected $subject;

    public function setUp(): void
    {
        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

        $this->subject = $this->getMockBuilder(\Evoweb\SfRegister\Validation\Validator\UniqueValidator::class)
            ->setConstructorArgs([$userRepository])
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
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        self::assertFalse($this->subject->validate($expected)->hasErrors());
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
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(1);
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        self::assertTrue($this->subject->validate($expected)->hasErrors());
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
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $repositoryMock->expects(self::any())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        self::assertFalse($this->subject->validate($expected)->hasErrors());
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
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $repositoryMock->expects(self::any())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->willReturn(1);
        $this->subject->injectUserRepository($repositoryMock);
        $this->subject->setPropertyName($fieldName);

        self::assertTrue($this->subject->validate($expected)->hasErrors());
    }
}
