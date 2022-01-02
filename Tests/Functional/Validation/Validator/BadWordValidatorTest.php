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
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class BadWordValidatorTest extends AbstractTestBase
{
    /**
     * @var BadWordValidator|AccessibleObjectInterface
     */
    protected $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../../../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../../../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../../../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../../../Fixtures/fe_users.xml');

        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();

        $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['badWordList'] =
            'god, sex, password';

        /** @var ConfigurationManager|MockObject $repositoryMock */
        $configurationMock = $this->createMock(ConfigurationManager::class);

        $this->subject = $this->getMockBuilder($this->buildAccessibleProxy(BadWordValidator::class))
            ->setConstructorArgs([$configurationMock])
            ->setMethods(['dummy'])
            ->getMock();
        $this->subject->_set(
            'settings',
            $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    public function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function settingsContainsValidTyposcriptSettings()
    {
        self::assertArrayHasKey(
            'badWordList',
            $this->subject->_get('settings')
        );
    }

    /**
     * @test
     */
    public function isValidReturnsFalseForWordOnBadwordlist()
    {
        $words = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
            ',',
            $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['badWordList']
        );

        $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);

        self::assertTrue($this->subject->validate(current($words))->hasErrors());
    }

    /**
     * @test
     */
    public function isValidReturnsTrueForGoodPassword()
    {
        self::assertFalse($this->subject->validate('4dw$koL')->hasErrors());
    }
}
