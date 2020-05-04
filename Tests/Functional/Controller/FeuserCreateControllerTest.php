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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class FeuserCreateControllerTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    public function setUp(): void
    {
        defined('LF') ?: define('LF', chr(10));
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_users.xml');

        $this->initializeTypoScriptFrontendController();
        $this->createEmptyFrontendUser();
    }

    /**
     * @test
     */
    public function isUserValidatorSet()
    {
        $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.'] = [
            'validation.' => [
                'create.' => [
                    'username.' => [
                        '1.' => '"Evoweb.SfRegister:Required"',
                        '2.' => '"StringLength", options={"minimum": 4, "maximum": 80}',
                        '3.' => '"Evoweb.SfRegister:Unique"',
                        '4.' => '"Evoweb.SfRegister:Unique", options={"global": 1}',
                    ]
                ]
            ]
        ];

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        $subject = new \Evoweb\SfRegister\Tests\Functional\Mock\FeuserCreateController($context);

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $subject->injectObjectManager($objectManager);

        /** @var \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService */
        $reflectionService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Reflection\ReflectionService::class);
        $subject->injectReflectionService($reflectionService);

        /** @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver $validationResolver */
        $validationResolver = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Validation\ValidatorResolver::class);
        $subject->injectValidatorResolver($validationResolver);

        /** @var \TYPO3\CMS\Extbase\Mvc\Request $request */
        $request = $this->getAccessibleMock(\TYPO3\CMS\Extbase\Mvc\Request::class);
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

        $this->assertInstanceOf(\Evoweb\SfRegister\Validation\Validator\UserValidator::class, $validator);
    }
}
