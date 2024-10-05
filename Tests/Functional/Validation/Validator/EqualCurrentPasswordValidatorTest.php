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
use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
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
        $this->assertArrayHasKey(
            'badWordList',
            $typoScriptSetup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    #[Test]
    public function isUserLoggedInReturnsFalseIfNotLoggedIn(): void
    {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);

        $this->assertFalse((bool)$context->getPropertyFromAspect('frontend.user', 'isLoggedIn'));
    }

    #[Test]
    #[RequiresPhp('9.3.0')]
    public function isUserLoggedInReturnsTrueIfLoggedIn(): void
    {
        $this->loginFrontEndUser(1);

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $this->createMock(FrontendUserRepository::class);

        $frontendUserService = new FrontendUserService($context, $frontendUserRepository);

        $subject = new EqualCurrentPasswordValidator($frontendUserService);

        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertTrue($method->invoke($subject));
    }
}
