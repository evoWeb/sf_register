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

namespace Evoweb\SfRegister\Tests\Functional\Validation\Validator;

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;

class EqualCurrentPasswordValidatorTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_users.csv');

        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();

        $this->initializeFrontendTypoScript([
            'plugin.' => [
                'tx_sfregister.' => [
                    'settings.' => [
                        'badWordList' => 'god, sex, password',
                    ],
                ],
            ],
        ]);
    }

    #[Test]
    public function settingsContainsValidTypoScriptSettings(): void
    {
        $typoScriptSetup = $this->request->getAttribute('frontend.typoscript')->getSetupArray();
        self::assertArrayHasKey(
            'badWordList',
            $typoScriptSetup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    #[Test]
    public function isUserLoggedInReturnsFalseIfNotLoggedIn(): void
    {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        self::assertFalse((bool)$context->getPropertyFromAspect('frontend.user', 'isLoggedIn'));
    }

    #[Test]
    #[RequiresPhp('9.3.0')]
    public function isUserLoggedInReturnsTrueIfLoggedIn(): void
    {
        $this->createAndLoginFrontEndUser('2', [
            'password' => 'testOld',
            'comments' => '',
        ]);
        $this->initializeTypoScriptFrontendController();

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        /** @var FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->createMock(FrontendUserRepository::class);

        /** @var ConfigurationManager|MockObject $repositoryMock */
        $configurationMock = $this->createMock(ConfigurationManager::class);

        $subject = new EqualCurrentPasswordValidator($context, $repositoryMock, $configurationMock);

        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        self::assertTrue($method->invoke($subject));
    }
}
