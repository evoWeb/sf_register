<?php
namespace Evoweb\SfRegister\Tests\Functional\Controller;

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

use Evoweb\SfRegister\Controller\FeuserPasswordController;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class FeuserPasswordControllerTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/fe_users.xml');
    }

    /**
     * @test
     */
    public function userIsLoggedInReturnsFalseIfNotLoggedIn()
    {
        $this->createEmptyFrontendUser();
        $this->initializeTypoScriptFrontendController();

        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertFalse($method->invoke($subject));
    }

    /**
     * @test
     */
    public function userIsLoggedInReturnsTrueIfLoggedIn()
    {
        $this->createAndLoginFrontEndUser('2', [
            'password' => 'testOld',
            'comments' => ''
        ]);
        $this->initializeTypoScriptFrontendController();

        $subject = new FeuserPasswordController();
        $method = $this->getPrivateMethod($subject, 'userIsLoggedIn');
        $this->assertTrue($method->invoke($subject));
    }

    /**
     * @test
     */
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository()
    {
        $expected = 'myPassword';

        $userId = $this->createAndLoginFrontEndUser('2', [
            'password' => $expected,
            'comments' => ''
        ]);
        $this->initializeTypoScriptFrontendController();

        // we don't want to test the encryption here
        if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
            unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
        }
        $GLOBALS['TSFE']->tmpl->setup['module.']['tx_sfregister.'] =
            $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.'];

        $subject = new FeuserPasswordController();

        $property = $this->getPrivateProperty($subject, 'settings');
        $property->setValue($subject, ['encryptPassword' => '']);

        // we need to clone the create object, else the isClone parameter is not set and both object wont match
        $userMock = clone(new \Evoweb\SfRegister\Domain\Model\FrontendUser());
        $userMock->setPassword($expected);

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $subject->injectObjectManager($objectManager);

        /** @var FrontendUserRepository|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        $repositoryMock = $this->getMockBuilder(FrontendUserRepository::class)
            ->setMethods(['findByUid', 'update'])
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($this->equalTo($userId))
            ->willReturn($userMock);
        $repositoryMock->expects($this->once())
            ->method('update')
            ->with($this->equalTo($userMock));
        $subject->injectUserRepository($repositoryMock);

        /** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
        $signalSlotDispatcher = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
        $subject->injectSignalSlotDispatcher($signalSlotDispatcher);

        /** @var \Evoweb\SfRegister\Domain\Model\Password $passwordMock */
        $passwordMock = GeneralUtility::makeInstance(\Evoweb\SfRegister\Domain\Model\Password::class);
        $passwordMock->_setProperty('password', $expected);
        $subject->saveAction($passwordMock);
    }
}
