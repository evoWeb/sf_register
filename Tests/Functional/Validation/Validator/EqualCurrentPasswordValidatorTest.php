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

class EqualCurrentPasswordValidatorTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
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
    }

    /**
     * @test
     */
    public function settingsContainsValidTyposcriptSettings()
    {
        $this->assertArrayHasKey(
            'badWordList',
            $this->typoScriptFrontendController->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsFalseIfNotLoggedIn()
    {
        $subject = new \Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator();
        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $subject->injectContext($context);

        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertFalse($method->invoke($subject));
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsTrueIfLoggedIn()
    {
        $this->createAndLoginFrontEndUser('2', [
            'password' => 'testOld',
            'comments' => ''
        ]);
        $this->initializeTypoScriptFrontendController();

        $subject = new \Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator();
        /** @var \TYPO3\CMS\Core\Context\Context $context */
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $subject->injectContext($context);

        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertTrue($method->invoke($subject));
    }
}
