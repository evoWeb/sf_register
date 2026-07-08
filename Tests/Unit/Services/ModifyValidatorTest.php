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

namespace Evoweb\SfRegister\Tests\Unit\Services;

use Doctrine\Common\Annotations\DocParser;
use Evoweb\SfRegister\Controller\FeuserController;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Validation\Validator\EmptyValidator;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentUserValidator;
use Evoweb\SfRegister\Validation\Validator\RequiredValidator;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ModifyValidatorTest extends UnitTestCase
{
    protected ValidatorResolver&MockObject $validatorResolver;

    protected ModifyValidator $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->validatorResolver = $this->createMock(ValidatorResolver::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logManager = $this->createMock(LogManager::class);
        $logManager->method('getLogger')->willReturn($logger);

        $this->subject = new ModifyValidator($this->validatorResolver, $logManager);
    }

    /**
     * @param array<int, mixed> $arguments
     */
    protected function callInaccessibleMethod(object $object, string $methodName, array $arguments = []): mixed
    {
        $method = new \ReflectionMethod($object, $methodName);
        return $method->invokeArgs($object, $arguments);
    }

    // -- actionIsIgnored ------------------------------------------------------------------------

    #[Test]
    public function actionIsIgnoredReturnsTrueForActionListedInControllerSettings(): void
    {
        $settings = ['ignoredActions' => ['Create' => ['formAction']]];

        $result = $this->callInaccessibleMethod($this->subject, 'actionIsIgnored', [
            'Create',
            $settings,
            'formAction',
            [],
        ]);

        self::assertTrue($result);
    }

    #[Test]
    public function actionIsIgnoredReturnsTrueForActionListedInIgnoredActionsArgument(): void
    {
        $result = $this->callInaccessibleMethod($this->subject, 'actionIsIgnored', [
            'Create',
            [],
            'formAction',
            ['formAction'],
        ]);

        self::assertTrue($result);
    }

    #[Test]
    public function actionIsIgnoredReturnsFalseForActionNotListed(): void
    {
        $settings = ['ignoredActions' => ['Create' => ['newAction']]];

        $result = $this->callInaccessibleMethod($this->subject, 'actionIsIgnored', [
            'Create',
            $settings,
            'formAction',
            [],
        ]);

        self::assertFalse($result);
    }

    // -- skipValidation -------------------------------------------------------------------------

    #[Test]
    public function skipValidationReturnsFalseForControllerOtherThanCreate(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->never())->method('hasArgument');

        $result = $this->callInaccessibleMethod($this->subject, 'skipValidation', [
            'Edit',
            $request,
            'formAction',
        ]);

        self::assertFalse($result);
    }

    #[Test]
    public function skipValidationReturnsFalseForActionOtherThanFormAction(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('hasArgument')->with('user')->willReturn(true);
        $request->method('getArgument')->with('user')->willReturn(['byInvitation' => '1']);

        $result = $this->callInaccessibleMethod($this->subject, 'skipValidation', [
            'Create',
            $request,
            'createAction',
        ]);

        self::assertFalse($result);
    }

    #[Test]
    public function skipValidationReturnsFalseWhenUserArgumentIsMissing(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('hasArgument')->with('user')->willReturn(false);

        $result = $this->callInaccessibleMethod($this->subject, 'skipValidation', [
            'Create',
            $request,
            'formAction',
        ]);

        self::assertFalse($result);
    }

    #[Test]
    public function skipValidationReturnsFalseWhenByInvitationIsNotSet(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('hasArgument')->with('user')->willReturn(true);
        $request->method('getArgument')->with('user')->willReturn(['username' => 'foo']);

        $result = $this->callInaccessibleMethod($this->subject, 'skipValidation', [
            'Create',
            $request,
            'formAction',
        ]);

        self::assertFalse($result);
    }

    #[Test]
    public function skipValidationReturnsTrueWhenByInvitationIsSet(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('hasArgument')->with('user')->willReturn(true);
        $request->method('getArgument')->with('user')->willReturn(['byInvitation' => '1']);

        $result = $this->callInaccessibleMethod($this->subject, 'skipValidation', [
            'Create',
            $request,
            'formAction',
        ]);

        self::assertTrue($result);
    }

    // -- shouldValidationBeModified -------------------------------------------------------------

    #[Test]
    public function shouldValidationBeModifiedReturnsFalseWhenActionIsIgnored(): void
    {
        $controller = $this->createMock(FeuserController::class);
        $controller->method('getControllerName')->willReturn('Create');

        $request = $this->createMock(RequestInterface::class);

        $result = $this->subject->shouldValidationBeModified(
            $controller,
            [],
            $request,
            'formAction',
            ['formAction'],
        );

        self::assertFalse($result);
    }

    #[Test]
    public function shouldValidationBeModifiedReturnsFalseWhenValidationShouldBeSkipped(): void
    {
        $controller = $this->createMock(FeuserController::class);
        $controller->method('getControllerName')->willReturn('Create');

        $request = $this->createMock(RequestInterface::class);
        $request->method('hasArgument')->with('user')->willReturn(true);
        $request->method('getArgument')->with('user')->willReturn(['byInvitation' => '1']);

        $result = $this->subject->shouldValidationBeModified(
            $controller,
            [],
            $request,
            'formAction',
            [],
        );

        self::assertFalse($result);
    }

    #[Test]
    public function shouldValidationBeModifiedReturnsTrueWhenNeitherIgnoredNorSkipped(): void
    {
        $controller = $this->createMock(FeuserController::class);
        $controller->method('getControllerName')->willReturn('Edit');

        $request = $this->createMock(RequestInterface::class);

        $result = $this->subject->shouldValidationBeModified(
            $controller,
            [],
            $request,
            'editAction',
            [],
        );

        self::assertTrue($result);
    }

    // -- getValidatorByConfiguration ------------------------------------------------------------

    #[Test]
    public function getValidatorByConfigurationResolvesSimpleValidatorConfiguration(): void
    {
        $expectedValidator = $this->createMock(RequiredValidator::class);
        $expectedValidator->expects($this->once())->method('setPropertyName')->with('username');

        $request = $this->createMock(RequestInterface::class);

        $this->validatorResolver->expects($this->once())
            ->method('createValidator')
            ->with(RequiredValidator::class, [], $request)
            ->willReturn($expectedValidator);

        $result = $this->callInaccessibleMethod($this->subject, 'getValidatorByConfiguration', [
            '"' . RequiredValidator::class . '"',
            new DocParser(),
            'username',
            $request,
        ]);

        self::assertSame($expectedValidator, $result);
    }

    #[Test]
    public function getValidatorByConfigurationPassesConfiguredOptionsToResolver(): void
    {
        // StringLengthValidator is declared "final" and cannot be doubled, a generic
        // ValidatorInterface mock is used as stand-in return value instead.
        $expectedValidator = $this->createMock(ValidatorInterface::class);

        $request = $this->createMock(RequestInterface::class);

        $this->validatorResolver->expects($this->once())
            ->method('createValidator')
            ->with(StringLengthValidator::class, ['minimum' => 8, 'maximum' => 40], $request)
            ->willReturn($expectedValidator);

        $result = $this->callInaccessibleMethod($this->subject, 'getValidatorByConfiguration', [
            '"' . StringLengthValidator::class . '", options={"minimum": 8, "maximum": 40}',
            new DocParser(),
            'password',
            $request,
        ]);

        self::assertSame($expectedValidator, $result);
    }

    // -- addUidValidator -------------------------------------------------------------------------

    #[Test]
    public function addUidValidatorAddsEqualCurrentUserValidatorForEditController(): void
    {
        $equalCurrentUserValidator = $this->createMock(EqualCurrentUserValidator::class);
        $this->validatorResolver->expects($this->once())
            ->method('createValidator')
            ->with(EqualCurrentUserValidator::class)
            ->willReturn($equalCurrentUserValidator);

        $validator = new UserValidator();

        $this->callInaccessibleMethod($this->subject, 'addUidValidator', ['Edit', $validator]);

        $uidValidators = iterator_to_array($validator->getPropertyValidators('uid'));
        self::assertSame([$equalCurrentUserValidator], $uidValidators);
    }

    #[Test]
    public function addUidValidatorAddsEqualCurrentUserValidatorForDeleteController(): void
    {
        $equalCurrentUserValidator = $this->createMock(EqualCurrentUserValidator::class);
        $this->validatorResolver->expects($this->once())
            ->method('createValidator')
            ->with(EqualCurrentUserValidator::class)
            ->willReturn($equalCurrentUserValidator);

        $validator = new UserValidator();

        $this->callInaccessibleMethod($this->subject, 'addUidValidator', ['Delete', $validator]);

        $uidValidators = iterator_to_array($validator->getPropertyValidators('uid'));
        self::assertSame([$equalCurrentUserValidator], $uidValidators);
    }

    #[Test]
    public function addUidValidatorAddsEmptyValidatorForOtherControllers(): void
    {
        $emptyValidator = $this->createMock(EmptyValidator::class);
        $this->validatorResolver->expects($this->once())
            ->method('createValidator')
            ->with(EmptyValidator::class)
            ->willReturn($emptyValidator);

        $validator = new UserValidator();

        $this->callInaccessibleMethod($this->subject, 'addUidValidator', ['Create', $validator]);

        $uidValidators = iterator_to_array($validator->getPropertyValidators('uid'));
        self::assertSame([$emptyValidator], $uidValidators);
    }

    // -- modifyValidatorsBasedOnSettings / modifyArgumentValidators -----------------------------

    #[Test]
    public function modifyArgumentValidatorsComposesUserValidatorFromConfiguredSettings(): void
    {
        $requiredValidator = $this->createMock(RequiredValidator::class);
        $emptyValidator = $this->createMock(EmptyValidator::class);

        $this->validatorResolver->method('createValidator')
            ->willReturnCallback(function (string $validatorType) use ($requiredValidator, $emptyValidator) {
                return match ($validatorType) {
                    UserValidator::class => new UserValidator(),
                    RequiredValidator::class => $requiredValidator,
                    EmptyValidator::class => $emptyValidator,
                    default => null,
                };
            });

        $controller = $this->createMock(FeuserController::class);
        $controller->method('getControllerName')->willReturn('Create');

        $request = $this->createMock(RequestInterface::class);

        $settings = [
            'validation' => [
                'create' => [
                    'username' => '"' . RequiredValidator::class . '"',
                ],
            ],
            'fields' => [
                'selected' => ['username'],
            ],
        ];

        $arguments = new Arguments();
        $arguments->addNewArgument('user', 'array');

        $this->subject->modifyArgumentValidators($controller, $settings, $request, $arguments);

        $appliedValidator = $arguments->getArgument('user')->getValidator();
        self::assertInstanceOf(UserValidator::class, $appliedValidator);

        $usernameValidators = iterator_to_array($appliedValidator->getPropertyValidators('username'));
        self::assertSame([$requiredValidator], $usernameValidators);

        $uidValidators = iterator_to_array($appliedValidator->getPropertyValidators('uid'));
        self::assertSame([$emptyValidator], $uidValidators);
    }

    #[Test]
    public function modifyArgumentValidatorsSkipsFieldsNotInSelectedFieldsSettings(): void
    {
        $emptyValidator = $this->createMock(EmptyValidator::class);

        $this->validatorResolver->method('createValidator')
            ->willReturnCallback(function (string $validatorType) use ($emptyValidator) {
                return match ($validatorType) {
                    UserValidator::class => new UserValidator(),
                    EmptyValidator::class => $emptyValidator,
                    default => null,
                };
            });

        $controller = $this->createMock(FeuserController::class);
        $controller->method('getControllerName')->willReturn('Create');

        $request = $this->createMock(RequestInterface::class);

        $settings = [
            'validation' => [
                'create' => [
                    'username' => '"' . RequiredValidator::class . '"',
                ],
            ],
            'fields' => [
                'selected' => [],
            ],
        ];

        $arguments = new Arguments();
        $arguments->addNewArgument('user', 'array');

        $this->subject->modifyArgumentValidators($controller, $settings, $request, $arguments);

        $appliedValidator = $arguments->getArgument('user')->getValidator();
        self::assertInstanceOf(UserValidator::class, $appliedValidator);

        self::assertSame([], $appliedValidator->getPropertyValidators('username'));
        $uidValidators = iterator_to_array($appliedValidator->getPropertyValidators('uid'));
        self::assertSame([$emptyValidator], $uidValidators);
    }

    #[Test]
    public function modifyArgumentValidatorsIgnoresArgumentsNotInAllowList(): void
    {
        $controller = $this->createMock(FeuserController::class);
        $controller->method('getControllerName')->willReturn('Create');

        $request = $this->createMock(RequestInterface::class);

        $this->validatorResolver->expects($this->never())->method('createValidator');

        $arguments = new Arguments();
        $arguments->addNewArgument('someOtherArgument', 'string');

        $this->subject->modifyArgumentValidators($controller, [], $request, $arguments);

        self::assertNull($arguments->getArgument('someOtherArgument')->getValidator());
    }
}
