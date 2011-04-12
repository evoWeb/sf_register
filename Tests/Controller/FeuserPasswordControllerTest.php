<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
*  All rights reserved
*
*  This class is a backport of the corresponding class of FLOW3.
*  All credits go to the v5 team.
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

class Tx_SfRegister_Domain_Model_FeuserPasswordControllerTest extends Tx_Extbase_BaseTestCase {
	/**
	 * @var Tx_SfRegister_Controller_FeuserPasswordController
	 */
	protected $fixture;

	/**
	 * @var Tx_Phpunit_Framework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new Tx_Phpunit_Framework('fe_users');
		$pageUid = $this->testingFramework->createFrontEndPage();
		$this->testingFramework->createTemplate($pageUid, array('include_static_file' => 'EXT:sf_register/Configuration/TypoScript/'));
		$this->testingFramework->createFakeFrontEnd($pageUid);
		
		$this->fixture = $this->getAccessibleMock('Tx_SfRegister_Controller_FeuserPasswordController', array('dummy'));
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}

	/**
	 * @test
	 */
	public function isUserLoggedInReturnsFalseIfNotLoggedIn() {
		$this->assertFalse(
			$this->fixture->_call('isUserLoggedIn')
		);
	}

	/**
	 * @test
	 */
	public function isUserLoggedInReturnsTrueIfLoggedIn() {
		$this->testingFramework->createAndLoginFrontEndUser('', array('password' => 'testOld'));

		$this->assertTrue(
			$this->fixture->_call('isUserLoggedIn')
		);
	}

	/**
	 * @test
	 */
	public function saveActionFetchUserObjectIfLoggedInSetsThePasswordAndCallsUpdateOnUserrepository() {
			// we dont want to test the encryption here
		if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
			unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
		}
		$expected = 'myPassword';

		$userId = $this->testingFramework->createAndLoginFrontEndUser('', array('password' => $expected));
			// we need to clone the create object else the isClone param is not set and the both object wont match
		$userMock = clone(new Tx_SfRegister_Domain_Model_FrontendUser());

		$repositoryMock = $this->getMock('Tx_SfRegister_Domain_Repository_FrontendUserRepository', array(), array(), '', FALSE);
		$repositoryMock->expects($this->once())
			->method('findByUid')
			->with($userId)
			->will($this->returnValue($userMock));
		$userMock->setPassword($expected);
		$repositoryMock->expects($this->once())
			->method('update')
			->with($userMock);
		$this->fixture->injectUserRepository($repositoryMock);

		$passwordMock = $this->getMock('Tx_SfRegister_Domain_Model_Password');
		$passwordMock->expects($this->once())
			->method('getPassword')
			->will($this->returnValue($expected));
		$this->fixture->saveAction($passwordMock);
	}
}

?>