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

use Evoweb\SfRegister\Validation\Validator\UniqueValidator;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class UniqueValidatorTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);

        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->setMethods(['translateErrorMessage'])
            ->getMock();
        $subject->setPropertyName($fieldName);

        self::assertFalse($subject->validate($expected)->hasErrors());
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfCountOfValueInFieldReturnsHigherThenZeroForLocalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(1);

        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->setMethods(['translateErrorMessage'])
            ->getMock();
        $subject->setPropertyName($fieldName);

        self::assertTrue($subject->validate($expected)->hasErrors());
    }

    /**
     * @test
     */
    public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalAndGlobalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $repositoryMock->expects(self::any())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->willReturn(0);

        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->setMethods(['translateErrorMessage'])
            ->getMock();
        $subject->setPropertyName($fieldName);

        self::assertFalse($subject->validate($expected)->hasErrors());
    }

    /**
     * @test
     */
    public function isValidReturnsFalseIfCountOfValueInFieldReturnsZeroForLocalAndHigherThenZeroForGlobalSearch()
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects(self::once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $repositoryMock->expects(self::any())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->willReturn(1);

        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->setMethods(['translateErrorMessage'])
            ->getMock();
        $subject->setOptions(['global' => 1]);
        $subject->setPropertyName($fieldName);

        $current = $subject->validate($expected);
        self::assertTrue($current->hasErrors());
    }
}
