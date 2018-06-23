<?php
namespace Evoweb\SfRegister\Tests\Functional\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-18 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\TestingFramework\Core\AccessibleObjectInterface;

class FeuserPasswordControllerTest extends \TYPO3\TestingFramework\Core\Functional\FunctionalTestCase
{
    /**
     * @test
     * @return void
     */
    public function isUserLoggedInReturnsFalseIfNotLoggedIn()
    {
        /** @var \Evoweb\SfRegister\Controller\FeuserPasswordController|AccessibleObjectInterface $subject */
        $subject = $this->getAccessibleMock(
            \Evoweb\SfRegister\Controller\FeuserPasswordController::class,
            null
        );
        $this->assertFalse(
            $subject->_call('isUserLoggedIn')
        );
    }

    /**
     * @test
     * @return void
     */
    public function isUserLoggedInReturnsTrueIfLoggedIn()
    {
        $this->testingFramework->createAndLoginFrontEndUser('', array('password' => 'testOld'));

        $this->assertTrue(
            $this->fixture->_call('isUserLoggedIn')
        );
    }

    /**
     * @test
     * @return void
     */
    public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserRepository()
    {
        // we don't want to test the encryption here
        if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
            unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
        }
        $expected = 'myPassword';

        $userId = $this->testingFramework->createAndLoginFrontEndUser('', array('password' => $expected));
        // we need to clone the create object else the isClone param
        // is not set and the both object wont match
        $userMock = clone(new \Evoweb\SfRegister\Domain\Model\FrontendUser());

        $repositoryMock = $this->getMock('Evoweb\\SfRegister\\Domain\\Repository\\FrontendUserRepository', array(), array(), '', FALSE);
        $repositoryMock->expects($this->once())
            ->method('findByUid')
            ->with($userId)
            ->will($this->returnValue($userMock));
        $userMock->setPassword($expected);
        $repositoryMock->expects($this->once())
            ->method('update')
            ->with($userMock);
        $this->fixture->injectUserRepository($repositoryMock);

        /** @var \Evoweb\SfRegister\Domain\Model\Password|\PHPUnit_Framework_MockObject_MockObject $passwordMock */
        $passwordMock = $this->getMock('Evoweb\\SfRegister\\Domain\\Model\\Password');
        $passwordMock->expects($this->once())
            ->method('getPassword')
            ->will($this->returnValue($expected));
        $this->fixture->saveAction($passwordMock);
    }
}
