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

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\FrontenUserGroup as FrontenUserGroupService;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Services\Session as SessionService;
use Evoweb\SfRegister\Services\Setup\CheckFactory;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use EvowebTests\TestClasses\Controller\FeuserCreateController;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
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
                                    1 => '"Evoweb.SfRegister:Required"',
                                    2 => '"StringLength", options={"minimum": 4, "maximum": 80}',
                                    3 => '"Evoweb.SfRegister:Unique"',
                                    4 => '"Evoweb.SfRegister:Unique", options={"global": 1}',
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
        $request = new Request($this->request);
        $request = $request->withAttribute('currentContentObject', new ContentObjectRenderer());
        $request = $request->withControllerActionName('form');
        $request = $request->withControllerName('FeuserCreate');
        $request = $request->withArgument('user', [
            'gender' => 1,
            'title' => 'none',
            'firstName' => '',
            'lastName' => '',
            'username' => '',
            'passwort' => '',
            'passwortRepeat' => '',
            'email' => '',
            'emailRepeat' => '',
            'gtc' => '',
            'privacy' => '',
        ]);

        /** @var ModifyValidator $modifyValidator */
        $modifyValidator = $this->get(ModifyValidator::class);
        /** @var FileService $fileService */
        $fileService = $this->createMock(FileService::class);
        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        /** @var MailService $mailService */
        $mailService = $this->createMock(MailService::class);
        /** @var FrontendUserService $frontendUserService */
        $frontendUserService = $this->createMock(FrontendUserService::class);
        /** @var FrontenUserGroupService $frontenUserGroupService */
        $frontenUserGroupService = $this->createMock(FrontenUserGroupService::class);
        /** @var SessionService $sessionService */
        $sessionService = $this->createMock(SessionService::class);
        /** @var CheckFactory $checkFactory */
        $checkFactory = $this->createMock(CheckFactory::class);

        $subject = new FeuserCreateController(
            $modifyValidator,
            $fileService,
            $userRepository,
            $mailService,
            $frontendUserService,
            $frontenUserGroupService,
            $sessionService,
            $checkFactory
        );

        /** @var ReflectionService $reflectionService */
        $reflectionService = $this->get(ReflectionService::class);
        $subject->injectReflectionService($reflectionService);

        /** @var ValidatorResolver $validationResolver */
        $validationResolver = $this->get(ValidatorResolver::class);
        $subject->injectValidatorResolver($validationResolver);

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $subject->injectEventDispatcher($eventDispatcher);

        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        $configurationManager->setConfiguration([
            'extensionName' => 'SfRegister',
            'pluginName' => 'Create',
        ]);
        $subject->injectConfigurationManager($configurationManager);

        $subject->set('request', $request);
        $subject->set('actionMethodName', 'formAction');
        $subject->call('initializeActionMethodArguments');
        $subject->call('initializeActionMethodValidators');

        /** @var Arguments $arguments */
        $arguments = $subject->get('arguments');
        $validator = $arguments->getArgument('user')->getValidator();

        $this->assertInstanceOf(UserValidator::class, $validator);
    }
}
