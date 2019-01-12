<?php
namespace Evoweb\SfRegister\Tests\Functional\Domain\Validator;

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

class BadWordValidatorTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    /**
     * @var \Evoweb\SfRegister\Validation\Validator\BadWordValidator
     */
    protected $subject;

    public function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__. '/../Fixtures/pages.xml');
        $this->importDataSet(__DIR__. '/../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__. '/../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__. '/../Fixtures/fe_users.xml');

        $this->subject = $this->getAccessibleMock(
            \Evoweb\SfRegister\Validation\Validator\BadWordValidator::Class,
            ['dummy']
        );
        $this->subject->_set('settings', $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']);
    }

    public function tearDown()
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
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    /**
     * @test
     */
    public function isValidReturnsFalseForWordOnBadwordlist()
    {
        $words = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
            ',',
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['badWordList']
        );

        $this->assertFalse($this->subject->isValid(current($words)));
    }

    /**
     * @test
     */
    public function isValidReturnsTrueForGoodPassword()
    {
        $this->assertTrue($this->subject->isValid('4dw$koL'));
    }
}
