<?php

namespace Evoweb\SfRegister\Tests\Functional\Controller;

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

use Evoweb\SfRegister\Domain\Repository\FrontendUserGroupRepository;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Tests\Functional\Mock\FeuserCreateController;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

class FeuserCreateControllerTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/fe_users.csv');

        $this->createEmptyFrontendUser();
        $this->request = $this->initializeTypoScriptFrontendController();
    }

    /**
     * @test
     */
    public function isUserValidatorSet()
    {
        $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.'] = [
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
                    ]
                ]
            ]
        ];

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        /** @var File $file */
        $file = GeneralUtility::makeInstance(File::class);

        /** @var FrontendUserRepository $userRepository */
        $userRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);

        /** @var FrontendUserGroupRepository $userGroupRepository */
        $userGroupRepository = GeneralUtility::makeInstance(FrontendUserGroupRepository::class);

        $subject = new FeuserCreateController(
            $context,
            $file,
            $userRepository,
            $userGroupRepository
        );

        /** @var ReflectionService $reflectionService */
        $reflectionService = GeneralUtility::makeInstance(ReflectionService::class);
        $subject->injectReflectionService($reflectionService);

        /** @var ValidatorResolver $validationResolver */
        $validationResolver = GeneralUtility::makeInstance(ValidatorResolver::class);
        $subject->injectValidatorResolver($validationResolver);

        /** @var Request $request */
        $request = $this->getAccessibleMock(Request::class);
        $request->setArgument('action', 'preview');
        $request->setArgument('controller', 'FeuserCreate');
        $request->setArgument('user', [
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

        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $settings = $typoScriptService->convertTypoScriptArrayToPlainArray(
            $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
        );
        $subject->set('settings', $settings);

        $subject->call('initializeActionMethodArguments');
        $subject->call('initializeActionMethodValidators');

        /** @var \TYPO3\CMS\Extbase\Mvc\Controller\Arguments $arguments */
        $arguments = $subject->get('arguments');
        $validator = $arguments->getArgument('user')->getValidator();

        self::assertInstanceOf(UserValidator::class, $validator);
    }
}
