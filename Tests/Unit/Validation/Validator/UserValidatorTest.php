<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Unit\Validation\Validator;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\ValidatableInterface;
use Evoweb\SfRegister\Validation\Validator\SetModelInterface;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class UserValidatorTest extends UnitTestCase
{
    protected UserValidator $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new UserValidator();
    }

    #[Test]
    public function isValidReturnsNoErrorsForValidObjectWithoutPropertyValidators(): void
    {
        $user = new FrontendUser('john', 'secret');

        $result = $this->subject->validate($user);

        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function isValidReturnsNoErrorsWhenPropertyValidatorReportsNoMessages(): void
    {
        $user = new FrontendUser('john', 'secret');

        $propertyValidator = $this->createMock(ValidatorInterface::class);
        $propertyValidator->expects($this->once())
            ->method('validate')
            ->with('john')
            ->willReturn(new Result());
        $this->subject->addPropertyValidator('username', $propertyValidator);

        $result = $this->subject->validate($user);

        self::assertFalse($result->hasErrors());
    }

    #[Test]
    public function isValidAddsErrorsReportedByPropertyValidatorForProperty(): void
    {
        $user = new FrontendUser('john', 'secret');

        $propertyResult = new Result();
        $propertyResult->addError(new Error('Username is invalid.', 1234567890));

        $propertyValidator = $this->createMock(ValidatorInterface::class);
        $propertyValidator->expects($this->once())
            ->method('validate')
            ->with('john')
            ->willReturn($propertyResult);
        $this->subject->addPropertyValidator('username', $propertyValidator);

        $result = $this->subject->validate($user);

        self::assertTrue($result->hasErrors());
        $errors = $result->forProperty('username')->getErrors();
        self::assertCount(1, $errors);
        self::assertSame(1234567890, $errors[0]->getCode());
        self::assertSame('Username is invalid.', $errors[0]->getMessage());
    }

    #[Test]
    public function isValidPassesModelToSubValidatorsImplementingSetModelInterface(): void
    {
        $user = new FrontendUser('john', 'secret');

        $propertyValidator = new class implements ValidatorInterface, SetModelInterface {
            public ?ValidatableInterface $receivedModel = null;

            public function validate(mixed $value): Result
            {
                return new Result();
            }

            /**
             * @param array<string, mixed> $options
             */
            public function setOptions(array $options): void {}

            /**
             * @return array<string, mixed>
             */
            public function getOptions(): array
            {
                return [];
            }

            public function setRequest(?ServerRequestInterface $request): void {}

            public function getRequest(): ?ServerRequestInterface
            {
                return null;
            }

            public function setModel(ValidatableInterface $model): void
            {
                $this->receivedModel = $model;
            }
        };
        $this->subject->addPropertyValidator('username', $propertyValidator);

        $this->subject->validate($user);

        self::assertSame($user, $propertyValidator->receivedModel);
    }

    #[Test]
    public function isValidHandlesObjectsNotImplementingValidatableInterfaceWithoutError(): void
    {
        self::markTestSkipped(
            'Pre-fix bug in df53334: UserValidator::isValid() unconditionally assigns the given '
            . '$object to the ValidatableInterface-typed $model property. When validate() is called '
            . 'with an object not implementing ValidatableInterface, this raises a TypeError instead '
            . 'of gracefully skipping validation. Fixed in 30e771a by an early return guard '
            . '(`if (!$object instanceof ValidatableInterface) { return; }`). '
            . 'Reactivate in roadmap step 2.'
        );

        // Intended (soll) behavior after the fix:
        // $result = $this->subject->validate(new \stdClass());
        // self::assertFalse($result->hasErrors());
    }
}
