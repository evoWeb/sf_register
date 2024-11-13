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

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\UserValidator;
use EvowebTests\TestClasses\Controller\FeuserCreateController;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
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
        $extbaseAttribute->setControllerActionName('create');

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
        $subject->set('actionMethodName', 'formAction');
        $subject->call('initializeActionMethodArguments');
        $subject->call('initializeActionMethodValidators');

        /** @var Arguments $arguments */
        $arguments = $subject->get('arguments');
        $validator = $arguments->getArgument('user')->getValidator();

        $this->assertInstanceOf(UserValidator::class, $validator);
    }
}
