<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Validation\Validator;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\BadWordValidator;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
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

        $this->createServerRequest();
        $this->initializeFrontendTypoScript([
            'plugin.' => [
                'tx_sfregister.' => [
                    'settings.' => [
                        'badWordList' => 'god, sex, password',
                    ],
                ],
            ],
        ]);

        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration([
            'extensionName' => 'SfRegister',
            'pluginName' => 'Create',
        ]);
        $this->subject = new BadWordValidator($configurationManager);
    }

    #[Test]
    public function typoscriptContainsValidTypoScriptSettings(): void
    {
        /** @var FrontendTypoScript $frontendTypoScript */
        $frontendTypoScript = $this->request->getAttribute('frontend.typoscript');
        $typoScriptSetup = $frontendTypoScript->getSetupArray();
        /** @var array<string, mixed> $settings */
        $settings = $typoScriptSetup['plugin.']['tx_sfregister.']['settings.'];
        self::assertArrayHasKey('badWordList', $settings);
    }

    #[Test]
    public function settingsContainsValidTypoScriptSettings(): void
    {
        $property = $this->getPrivateProperty($this->subject, 'settings');

        /** @var array<string, mixed> $settings */
        $settings = $property->getValue($this->subject);
        self::assertArrayHasKey('badWordList', $settings);
    }

    #[Test]
    public function isValidReturnsFalseForWordOnBadWordList(): void
    {
        $this->request = $this->request->withAttribute('language', (new NullSite())->getDefaultLanguage());
        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        /** @var FrontendTypoScript $frontendTypoScript */
        $frontendTypoScript = $this->request->getAttribute('frontend.typoscript');
        $typoScriptSetup = $frontendTypoScript->getSetupArray();
        $words = GeneralUtility::trimExplode(
            ',',
            $typoScriptSetup['plugin.']['tx_sfregister.']['settings.']['badWordList']
        );

        self::assertTrue($this->subject->validate(current($words))->hasErrors());
    }

    #[Test]
    public function isValidReturnsTrueForGoodPassword(): void
    {
        self::assertFalse($this->subject->validate('4dw$koL')->hasErrors());
    }
}
