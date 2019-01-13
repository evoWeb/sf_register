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

use Evoweb\SfRegister\Controller\FeuserCreateController;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class FeuserCreateControllerTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_users.xml');

        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();
    }

    /**
     * @test
     */
    public function isUserValidatorSet()
    {
        /** @var FeuserCreateController|AccessibleObjectInterface $subject */
        $subject = $this->getAccessibleMock(FeuserCreateController::class, ['dummy']);

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $subject->injectObjectManager($objectManager);

        /** @var \TYPO3\CMS\Extbase\Reflection\ReflectionService $reflectionService */
        $reflectionService = $objectManager->get(\TYPO3\CMS\Extbase\Reflection\ReflectionService::class);
        $subject->injectReflectionService($reflectionService);

        /** @var \TYPO3\CMS\Extbase\Validation\ValidatorResolver $validationResolver */
        $validationResolver = $objectManager->get(\TYPO3\CMS\Extbase\Validation\ValidatorResolver::class);
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
        $subject->_set('request', $request);
        $subject->_set('actionMethodName', 'previewAction');

        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        $settings = $typoScriptService->convertTypoScriptArrayToPlainArray(
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
        );
        $subject->_set('settings', $settings);

        $subject->_call('initializeActionMethodArguments');
        $subject->_call('initializeActionMethodValidators');

        $validator = $subject->_get('arguments')->getArgument('user')->getValidator();

        $this->assertInstanceOf(\Evoweb\SfRegister\Validation\Validator\UserValidator::class, $validator);
    }
}
