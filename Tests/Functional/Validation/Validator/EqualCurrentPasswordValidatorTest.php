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

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;

class EqualCurrentPasswordValidatorTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/fe_users.xml');
    }

    /**
     * @test
     */
    public function settingsContainsValidTyposcriptSettings()
    {
        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();

        $this->assertArrayHasKey(
            'badWordList',
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
        );
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsFalseIfNotLoggedIn()
    {
        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();

        $subject = new \Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator();
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
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $subject->injectContext($context);

        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertTrue($method->invoke($subject));
    }

    /**
     * @test
     */
    public function loggedinUserFoundInDbHasEqualUnencryptedPassword()
    {
        $expected = 'myFancyPassword';

        $userId = $this->createAndLoginFrontEndUser('2', [
            'password' => $expected,
            'comments' => ''
        ]);
        $this->initializeTypoScriptFrontendController();

        // we don't want to test the encryption here
        if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
            unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
        }

        $subject = new \Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator();
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $subject->injectContext($context);

        $property = $this->getPrivateProperty($subject, 'settings');
        $property->setValue($subject, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']);

        /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getAccessibleMock(\Evoweb\SfRegister\Domain\Model\FrontendUser::class);
        $userMock->expects($this->any())->method('getPassword')->willReturn($expected);

        /** @var FrontendUserRepository|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        $repositoryMock = $this->getAccessibleMock(
            FrontendUserRepository::class,
            ['findByUid'],
            [],
            '',
            false
        );
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($this->equalTo($userId))
            ->willReturn($userMock);
        $subject->injectUserRepository($repositoryMock);

        $this->assertFalse($subject->validate($expected)->hasErrors());
    }

    /**
     * @test
     */
    public function loggedinUserFoundInDbHasEqualMd5EncryptedPassword()
    {
        $expected = 'myFancyPassword';

        $userId = $this->createAndLoginFrontEndUser('2', [
            'password' => $expected,
            'comments' => ''
        ]);
        $this->initializeTypoScriptFrontendController();

        $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'] = 'md5';

        $subject = new \Evoweb\SfRegister\Validation\Validator\EqualCurrentPasswordValidator();
        $context = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $subject->injectContext($context);

        $property = $this->getPrivateProperty($subject, 'settings');
        $property->setValue($subject, $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']);

        /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser|\PHPUnit_Framework_MockObject_MockObject $userMock */
        $userMock = $this->getAccessibleMock(\Evoweb\SfRegister\Domain\Model\FrontendUser::class);
        $userMock->expects($this->any())->method('getPassword')->willreturn(md5($expected));

        /** @var FrontendUserRepository|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        $repositoryMock = $this->getAccessibleMock(
            FrontendUserRepository::class,
            ['findByUid'],
            [],
            '',
            false
        );
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($this->equalTo($userId))
            ->willReturn($userMock);
        $subject->injectUserRepository($repositoryMock);

        $this->assertFalse($subject->validate($expected)->hasErrors());
    }
}
