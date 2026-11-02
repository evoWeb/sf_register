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

namespace Evoweb\SfRegister\Tests\Functional\Validation\Validator;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\BlockDomainValidator;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class BlockDomainValidatorTest extends AbstractTestBase
{
    protected BlockDomainValidator $subject;

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
                        'blockDomainList' => 'blocked.example, spam.test',
                    ],
                ],
            ],
        ]);

        $this->request = $this->request->withAttribute('language', (new NullSite())->getDefaultLanguage());
        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        $this->subject = new BlockDomainValidator($this->createConfiguredConfigurationManager());
    }

    protected function createConfiguredConfigurationManager(): ConfigurationManagerInterface
    {
        /** @var ConfigurationManagerInterface $configurationManager */
        $configurationManager = $this->get(ConfigurationManagerInterface::class);
        $configurationManager->setRequest($this->request);
        // @extensionScannerIgnoreLine
        $configurationManager->setConfiguration([
            'extensionName' => 'SfRegister',
            'pluginName' => 'Create',
        ]);

        return $configurationManager;
    }

    #[Test]
    public function typoscriptContainsValidTypoScriptSettings(): void
    {
        /** @var FrontendTypoScript $typoScriptFrontend */
        $typoScriptFrontend = $this->request->getAttribute('frontend.typoscript');
        /** @var array<string, mixed> $typoScriptSetup */
        $typoScriptSetup = $typoScriptFrontend->getSetupArray();
        /** @var array<string, mixed> $plugin */
        $plugin = $typoScriptSetup['plugin.'];
        /** @var array<string, mixed> $sfRegisterPlugin */
        $sfRegisterPlugin = $plugin['tx_sfregister.'];
        /** @var array<string, mixed> $settings */
        $settings = $sfRegisterPlugin['settings.'];

        self::assertArrayHasKey('blockDomainList', $settings);
    }

    #[Test]
    public function settingsContainsValidTypoScriptSettings(): void
    {
        $property = $this->getPrivateProperty($this->subject, 'settings');

        /** @var array<string, mixed> $settings */
        $settings = $property->getValue($this->subject);

        self::assertArrayHasKey('blockDomainList', $settings);
    }

    #[Test]
    public function isValidReturnsErrorForEmailOnBlockDomainList(): void
    {
        self::assertTrue($this->subject->validate('user@blocked.example')->hasErrors());
    }

    #[Test]
    public function isValidReturnsErrorForSecondEntryOnBlockDomainList(): void
    {
        self::assertTrue($this->subject->validate('other@spam.test')->hasErrors());
    }

    #[Test]
    public function isValidReturnsNoErrorForEmailNotOnBlockDomainList(): void
    {
        self::assertFalse($this->subject->validate('user@allowed.example')->hasErrors());
    }

    #[Test]
    public function isValidReturnsNoErrorForDomainOnListWithDifferentCase(): void
    {
        // Domain comparison is case-sensitive (strict in_array), so a differently
        // cased domain is not recognized as blocked - characterizes current behaviour.
        self::assertFalse($this->subject->validate('user@BLOCKED.EXAMPLE')->hasErrors());
    }

    #[Test]
    public function isValidReturnsNoErrorForValueThatIsNotAValidEmail(): void
    {
        self::assertFalse($this->subject->validate('not-an-email')->hasErrors());
    }

    #[Test]
    public function isValidReturnsNoErrorWhenBlockDomainListIsEmpty(): void
    {
        $subject = new BlockDomainValidator($this->createConfiguredConfigurationManager());
        $property = $this->getPrivateProperty($subject, 'settings');

        /** @var array<string, mixed> $settings */
        $settings = $property->getValue($subject);
        unset($settings['blockDomainList']);
        $property->setValue($subject, $settings);

        self::assertFalse($subject->validate('user@blocked.example')->hasErrors());
    }
}
