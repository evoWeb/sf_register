<?php

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

namespace Evoweb\SfRegister\Tests\Unit\Validation\Validator;

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Validation\Validator\UniqueValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class UniqueValidatorTest extends UnitTestCase
{
    #[Test]
    public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalSearch(): void
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects($this->once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);

        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();
        $subject->setPropertyName($fieldName);

        $this->assertFalse($subject->validate($expected)->hasErrors());
    }

    #[Test]
    public function isValidReturnsFalseIfCountOfValueInFieldReturnsHigherThenZeroForLocalSearch(): void
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects($this->once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(1);

        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();
        $subject->setPropertyName($fieldName);

        $this->assertTrue($subject->validate($expected)->hasErrors());
    }

    #[Test]
    public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalAndGlobalSearch(): void
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects($this->once())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $repositoryMock->expects($this->any())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->willReturn(0);

        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();
        $subject->setPropertyName($fieldName);

        $this->assertFalse($subject->validate($expected)->hasErrors());
    }

    #[Test]
    public function isValidReturnsFalseIfCountOfValueInFieldReturnsZeroForLocalAndHigherThenZeroForGlobalSearch(): void
    {
        $fieldName = 'username';
        $expected = 'myValue';

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);
        $repositoryMock->expects($this->any())
            ->method('countByField')
            ->with($fieldName, $expected)
            ->willReturn(0);
        $repositoryMock->expects($this->once())
            ->method('countByFieldGlobal')
            ->with($fieldName, $expected)
            ->willReturn(1);

        /** @var UniqueValidator $subject */
        $subject = $this->getMockBuilder(UniqueValidator::class)
            ->setConstructorArgs([$repositoryMock])
            ->onlyMethods(['translateErrorMessage'])
            ->getMock();
        // @extensionScannerIgnoreLine
        $subject->setOptions(['global' => 1]);
        $subject->setPropertyName($fieldName);

        $current = $subject->validate($expected);
        $this->assertTrue($current->hasErrors());
    }
}
