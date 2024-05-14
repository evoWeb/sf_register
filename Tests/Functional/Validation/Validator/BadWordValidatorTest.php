<?php

namespace Evoweb\SfRegister\Tests\Functional\Validation\Validator;

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

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Validation\Validator\BadWordValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class BadWordValidatorTest extends AbstractTestBase
{
    protected BadWordValidator|AccessibleObjectInterface|Mockobject $subject;

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
                    ]
                ]
            ]
        ]);
        $GLOBALS['TYPO3_REQUEST'] = $this->request;

        $this->subject = $this->get(BadWordValidator::class);
    }

    public function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    #[Test]
    public function settingsContainsValidTypoScriptSettings(): void
    {
        $property = $this->getPrivateProperty($this->subject, 'settings');

        self::assertArrayHasKey(
            'badWordList',
            $property->getValue($this->subject)
        );
    }

    #[Test]
    public function isValidReturnsFalseForWordOnBadWordList(): void
    {
        /** @var FrontendTypoScript $frontendTypoScript */
        $frontendTypoScript = $this->request->getAttribute('frontend.typoscript');
        $words = GeneralUtility::trimExplode(
            ',',
            $frontendTypoScript->getSetupArray()['plugin.']['tx_sfregister.']['settings.']['badWordList']
        );

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)->create('default');

        self::assertTrue($this->subject->validate(current($words))->hasErrors());
    }

    #[Test]
    public function isValidReturnsTrueForGoodPassword(): void
    {
        self::assertFalse($this->subject->validate('4dw$koL')->hasErrors());
    }
}
