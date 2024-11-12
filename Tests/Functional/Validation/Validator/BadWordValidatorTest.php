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

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\BadWordValidator;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class BadWordValidatorTest extends AbstractTestBase
{
    protected BadWordValidator $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_users.csv');

        $this->initializeRequest();
        $this->initializeFrontendTypoScript([
            'plugin.' => [
                'tx_sfregister.' => [
                    'settings.' => [
                        'badWordList' => 'god, sex, password',
                    ],
                ],
            ],
        ]);

        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        $configurationManager->setConfiguration([
            'extensionName' => 'SfRegister',
            'pluginName' => 'Create',
        ]);
        $this->subject = new BadWordValidator($configurationManager);
    }

    public function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    #[Test]
    public function typoscriptContainsValidTypoScriptSettings(): void
    {
        $typoScriptSetup = $this->request->getAttribute('frontend.typoscript')->getSetupArray();
        $this->assertArrayHasKey(
            'badWordList',
            $typoScriptSetup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    #[Test]
    public function settingsContainsValidTypoScriptSettings(): void
    {
        $property = $this->getPrivateProperty($this->subject, 'settings');

        $this->assertArrayHasKey('badWordList', $property->getValue($this->subject));
    }

    #[Test]
    public function isValidReturnsFalseForWordOnBadWordList(): void
    {
        $this->request = $this->request->withAttribute('language', (new NullSite())->getDefaultLanguage());
        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        $typoScriptSetup = $this->request->getAttribute('frontend.typoscript')->getSetupArray();
        $words = GeneralUtility::trimExplode(
            ',',
            $typoScriptSetup['plugin.']['tx_sfregister.']['settings.']['badWordList']
        );

        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->create('default');

        $this->assertTrue($this->subject->validate(current($words))->hasErrors());
    }

    #[Test]
    public function isValidReturnsTrueForGoodPassword(): void
    {
        $this->assertFalse($this->subject->validate('4dw$koL')->hasErrors());
    }
}
