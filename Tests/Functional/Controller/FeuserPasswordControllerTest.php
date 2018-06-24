<?php
namespace Evoweb\SfRegister\Tests\Functional\Controller;

/*
 * This file is developed by evoweb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Evoweb\SfRegister\Controller\FeuserPasswordController;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class FeuserPasswordControllerTest extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();

        $typoScriptFrontendController = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            null,
            1,
            0
        );
        $typoScriptFrontendController->sys_page = GeneralUtility::makeInstance(PageRepository::class);
        $typoScriptFrontendController->tmpl = GeneralUtility::makeInstance(TemplateService::class);
        $GLOBALS['TSFE'] = $typoScriptFrontendController;
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsFalseIfNotLoggedIn()
    {
        /** @var FeuserPasswordController $subject */
        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertFalse(
            $method->invoke($subject)
        );
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsTrueIfLoggedIn()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', ['password' => 'testOld']);

        /** @var FeuserPasswordController $subject */
        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertTrue(
            $method->invoke($subject)
        );
    }

    /**
     * @test
     * @return void
     */
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository()
    {
        /** @var FeuserPasswordController|AccessibleObjectInterface $subject */
        $subject = $this->getAccessibleMock(
            FeuserPasswordController::class,
            null
        );

        // we don't want to test the encryption here
        if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
            unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
        }
        $expected = 'myPassword';

        $userId = $this->testingFramework->createAndLoginFrontEndUser('', ['password' => $expected]);
        // we need to clone the create object else the isClone param
        // is not set and the both object wont match
        $userMock = clone(new \Evoweb\SfRegister\Domain\Model\FrontendUser());

        $repositoryMock = $this->getMock(
            \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository::class,
            [],
            [],
            '',
            false
        );
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($userId)
            ->will($this->returnValue($userMock));
        $userMock->setPassword($expected);
        $repositoryMock->expects($this->once())
            ->method('update')
            ->with($userMock);
        $subject->injectUserRepository($repositoryMock);

        /** @var \Evoweb\SfRegister\Domain\Model\Password|\PHPUnit_Framework_MockObject_MockObject $passwordMock */
        $passwordMock = $this->getMock(\Evoweb\SfRegister\Domain\Model\Password::class);
        $passwordMock->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($expected));
        $subject->saveAction($passwordMock);
    }

    public function getPrivateMethod($obj, $name)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
