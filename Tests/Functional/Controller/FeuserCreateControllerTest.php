<?php

declare(strict_types=1);

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

namespace Evoweb\SfRegister\Tests\Functional\Controller;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use EvowebTests\TestClasses\Controller\FeuserCreateController;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Crypto\HashService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Security\HashScope;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class FeuserCreateControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->initializeRequest();
        $this->initializeFrontendTypoScript([
            'plugin.' => [
                'tx_sfregister.' => [
                    'settings.' => [
                        'fields' => [
                            'selected' => 'username',
                        ],
                        'validation.' => [
                            'create.' => [
                                'username.' => [
                                    1 => '"Evoweb\\SfRegister\\Validation\\Validator\\RequiredValidator"',
                                    2 => '"TYPO3\\CMS\\Extbase\\Validation\\Validator\\StringLengthValidator", options={"minimum": 4, "maximum": 80}',
                                    3 => '"Evoweb\\SfRegister\\Validation\\Validator\\UniqueValidator"',
                                    4 => '"Evoweb\\SfRegister\\Validation\\Validator\\UniqueValidator", options={"global": 1}',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function isUserValidatorSet(): void
    {
        $validator = $this->getUserValidatorAfterInitialization('formAction');

        $this->assertInstanceOf(UserValidator::class, $validator);
    }

    #[Test]
    #[DataProvider('actionsWithIgnoredValidationProvider')]
    public function noValidatorIsSetForArgumentWithIgnoredValidation(string $actionMethodName): void
    {
        $validator = $this->getUserValidatorAfterInitialization($actionMethodName);

        self::assertNull($validator);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function actionsWithIgnoredValidationProvider(): iterable
    {
        yield 'proxy' => ['proxyAction'];
        yield 'removeImage' => ['removeImageAction'];
    }

    protected function getUserValidatorAfterInitialization(string $actionMethodName): ?ValidatorInterface
    {
        // configurationManager is a shared object, and will be a constructor parameter of the controller
        // @see Bootstrap::initializeConfiguration
        $configuration = [
            'extensionName' => 'SfRegister',
            'pluginName' => 'Create',
        ];
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        $configurationManager->setConfiguration($configuration);

        // @see RequestBuilder::build
        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Create');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserCreate');
        $extbaseAttribute->setControllerActionName(substr($actionMethodName, 0, -6));

        $extbaseAttribute->setArgument('user', [
            'gender' => 1,
            'title' => 'none',
            'firstName' => '',
            'lastName' => '',
            'username' => '',
            'passwort' => '',
            'passwortRepeat' => '',
            'email' => '',
            'emailRepeat' => '',
            'gtc' => 1,
            'privacy' => 1,
        ]);

        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));
        $request = $request->withAttribute('currentContentObject', new ContentObjectRenderer());

        /** @var FeuserCreateController $subject */
        $subject = $this->get(FeuserCreateController::class);

        $subject->set('request', $request);
        $subject->set('actionMethodName', $actionMethodName);
        $subject->call('initializeActionMethodArguments');
        $subject->call('initializeActionMethodValidators');

        /** @var Arguments $arguments */
        $arguments = $subject->get('arguments');
        return $arguments->getArgument('user')->getValidator();
    }

    /**
     * Forwarding to the referring preview action would validate the original form
     * data again. Because a captcha can only be solved once, this ends in an
     * InfiniteLoopException, so the form needs to be displayed instead.
     */
    #[Test]
    #[DataProvider('actionsReferredByPreviewProvider')]
    public function failedActionReferredByPreviewForwardsToForm(string $actionMethodName): void
    {
        $subject = $this->getSubjectWithFailedValidation($actionMethodName, 'preview');

        /** @var ForwardResponse $response */
        $response = $this->getPrivateMethod($subject, 'errorAction')->invoke($subject);

        self::assertInstanceOf(ForwardResponse::class, $response);
        self::assertSame('form', $response->getActionName());
        self::assertSame([], $response->getArguments());
        self::assertTrue($response->getArgumentsValidationResult()->hasErrors());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function actionsReferredByPreviewProvider(): iterable
    {
        yield 'proxy' => ['proxyAction'];
        yield 'save' => ['saveAction'];
        // forwarded to the preview by a failed save of an older version
        yield 'preview' => ['previewAction'];
    }

    #[Test]
    public function failedActionNotReferredByPreviewForwardsToReferringRequest(): void
    {
        $subject = $this->getSubjectWithFailedValidation('saveAction', 'form');

        /** @var ForwardResponse $response */
        $response = $this->getPrivateMethod($subject, 'errorAction')->invoke($subject);

        self::assertInstanceOf(ForwardResponse::class, $response);
        self::assertSame('form', $response->getActionName());
        self::assertSame(self::REFERRING_ARGUMENTS, $response->getArguments());
    }

    protected const REFERRING_ARGUMENTS = ['user' => ['username' => 'tester', 'captcha' => 'solved']];

    protected function getSubjectWithFailedValidation(
        string $actionMethodName,
        string $referringAction,
    ): FeuserCreateController {
        /** @var HashService $hashService */
        $hashService = $this->get(HashService::class);

        $extbaseAttribute = new ExtbaseRequestParameters();
        $extbaseAttribute->setPluginName('Create');
        $extbaseAttribute->setControllerExtensionName('SfRegister');
        $extbaseAttribute->setControllerName('FeuserCreate');
        $extbaseAttribute->setControllerActionName(substr($actionMethodName, 0, -6));
        $extbaseAttribute->setArgument('__referrer', [
            '@request' => $hashService->appendHmac(
                (string)json_encode([
                    '@extension' => 'SfRegister',
                    '@controller' => 'FeuserCreate',
                    '@action' => $referringAction,
                ]),
                HashScope::ReferringRequest->prefix()
            ),
            'arguments' => $hashService->appendHmac(
                base64_encode(serialize(self::REFERRING_ARGUMENTS)),
                HashScope::ReferringArguments->prefix()
            ),
        ]);
        $request = new Request($this->request->withAttribute('extbase', $extbaseAttribute));

        $failingValidator = new class () extends AbstractValidator {
            protected function isValid(mixed $value): void
            {
                $this->addError('captcha not correct', 1306910429);
            }
        };
        $arguments = new Arguments();
        $arguments->addNewArgument('user', FrontendUser::class, true)
            ->setValidator($failingValidator)
            ->setValue(new FrontendUser());

        /** @var FeuserCreateController $subject */
        $subject = $this->get(FeuserCreateController::class);
        $subject->set('request', $request);
        $subject->set('arguments', $arguments);
        $subject->set('actionMethodName', $actionMethodName);

        return $subject;
    }
}
