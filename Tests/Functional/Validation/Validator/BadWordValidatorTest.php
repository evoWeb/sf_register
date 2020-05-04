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

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class BadWordValidatorTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\BadWordValidator|AccessibleObjectInterface
     */
    protected $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/fe_users.xml');

        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();

        $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['badWordList'] =
            'god, sex, password';

        $this->subject = $this->getAccessibleMock(
            \Evoweb\SfRegister\Validation\Validator\BadWordValidator::class,
            ['dummy']
        );
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
        $this->assertArrayHasKey(
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

        $this->assertTrue($this->subject->validate(current($words))->hasErrors());
    }

    /**
     * @test
     */
    public function isValidReturnsTrueForGoodPassword()
    {
        $this->assertFalse($this->subject->validate('4dw$koL')->hasErrors());
    }
}
