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

namespace Evoweb\SfRegister\Tests\Functional\Controller;

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use Evoweb\SfRegister\Services\FrontenUserGroup as FrontenUserGroupService;
use Evoweb\SfRegister\Services\Mail as MailService;
use Evoweb\SfRegister\Services\ModifyValidator;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\Mock\FeuserCreateController;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
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

        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();
    }

    #[Test]
    public function isUserValidatorSet(): void
    {
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

        /** @var ValidatorResolver $validationResolver */
        $validationResolver = GeneralUtility::makeInstance(ValidatorResolver::class);
        $modifyValidator = new ModifyValidator($validationResolver);

        /** @var FileService $fileService */
        $fileService = $this->createMock(FileService::class);

        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

        /** @var MailService $mailService */
        $mailService = $this->createMock(MailService::class);
        /** @var FrontendUserService $frontendUserService */
        $frontendUserService = $this->createMock(FrontendUserService::class);
        /** @var FrontenUserGroupService $frontenUserGroupService */
        $frontenUserGroupService = $this->createMock(FrontenUserGroupService::class);

        $subject = new FeuserCreateController(
            $modifyValidator,
            $fileService,
            $userRepository,
            $mailService,
            $frontendUserService,
            $frontenUserGroupService
        );

        /** @var ReflectionService $reflectionService */
        $reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
        $subject->injectReflectionService($reflectionService);

        $subject->injectValidatorResolver($validationResolver);

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
        $subject->injectEventDispatcher($eventDispatcher);

        $this->request = $this->request->withAttribute('extbase', new ExtbaseRequestParameters());
        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        $request = new Request($this->request);
        $request = $request->withAttribute('currentContentObject', new ContentObjectRenderer());
        $request = $request->withArgument('action', 'preview');
        $request = $request->withArgument('controller', 'FeuserCreate');
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
        $subject->set('request', $request);
        $subject->set('actionMethodName', 'previewAction');

        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $settings = $typoScriptService->convertTypoScriptArrayToPlainArray(
            $frontendTypoScript->getSetupArray()['plugin.']['tx_sfregister.']['settings.']
        );
        $subject->set('settings', $settings);

        $subject->call('initializeActionMethodArguments');
        $subject->call('initializeActionMethodValidators');

        /** @var Arguments $arguments */
        $arguments = $subject->get('arguments');
        $validator = $arguments->getArgument('user')->getValidator();

        $this->assertInstanceOf(UserValidator::class, $validator);
    }
}
