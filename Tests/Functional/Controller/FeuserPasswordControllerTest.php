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
use PHPUnit\Framework\MockObject\MockObject;

use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class FeuserPasswordControllerTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    public function setUp()
    {
        $this->testExtensionsToLoad[] = 'typo3conf/ext/sf_register';

        parent::setUp();
        $this->importDataSet('../Tests/Functional/Fixtures/fe_groups.xml');
        $this->initializeTypoScriptFrontendController();
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsFalseIfNotLoggedIn()
    {
        /** @var FeuserPasswordController $subject */
        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertFalse($method->invoke($subject));
    }

    /**
     * @test
     */
    public function isUserLoggedInReturnsTrueIfLoggedIn()
    {
        $this->createAndLoginFrontEndUser($GLOBALS['TSFE'], '1', [
            'password' => 'testOld',
            'comments' => ''
        ]);

        /** @var FeuserPasswordController $subject */
        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertTrue($method->invoke($subject));
    }

    /**
     * @test
     */
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository()
    {
        /** @var FeuserPasswordController|AccessibleObjectInterface $subject */
        $subject = $this->getAccessibleMock(
            FeuserPasswordController::class,
            null
        );
        $expected = 'myPassword';

        // we don't want to test the encryption here
        if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
            unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
        }

        $userId = $this->createAndLoginFrontEndUser($GLOBALS['TSFE'], '1', [
            'password' => $expected,
            'comments' => ''
        ]);

        // we need to clone the create object else the isClone param
        // is not set and the both object wont match
        $userMock = clone(new \Evoweb\SfRegister\Domain\Model\FrontendUser());
        $userMock->setPassword($expected);

        /** @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository|MockObject $repositoryMock */
        $repositoryMock = $this->getMockBuilder(\Evoweb\SfRegister\Domain\Repository\FrontendUserRepository::class)
            ->setMethods(['findByUid', 'update'])
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($userId)
            ->will($this->returnValue($userMock));
        $repositoryMock->expects($this->once())
            ->method('update')
            ->with($userMock);
        $subject->injectUserRepository($repositoryMock);

        /** @var \Evoweb\SfRegister\Domain\Model\Password|MockObject $passwordMock */
        $passwordMock = $this->getMockBuilder(\Evoweb\SfRegister\Domain\Model\Password::class)
            ->getMock();
        $passwordMock->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($expected));
        $subject->saveAction($passwordMock);
    }
}
