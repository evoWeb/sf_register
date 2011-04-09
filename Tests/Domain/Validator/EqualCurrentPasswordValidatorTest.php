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

class Tx_SfRegister_Domain_Model_EqualCurrentPasswordValidatorTest extends Tx_Extbase_BaseTestCase {
	/**
	 * @var Tx_SfRegister_Domain_Validator_EqualCurrentPasswordValidator
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

		$this->fixture = $this->getAccessibleMock('Tx_SfRegister_Domain_Validator_EqualCurrentPasswordValidator', array('dummy'));
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->fixture, $this->testingFramework);
	}

	/**
	 * @test
	 */
	public function settingsContainsValidTyposcriptSettings() {
		$this->assertArrayHasKey(
			'badWordList',
			$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']
		);
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
	public function loggedinUserFoundInDb() {
		if (isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword'])) {
			unset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']['encryptPassword']);
		}
		$this->fixture->_set('settings', $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_sfregister.']['settings.']);

		$expected = 'myFancyPassword';

		$userId = $this->testingFramework->createAndLoginFrontEndUser('', array('password' => $expected));

		$userMock = $this->getMock('Tx_SfRegister_Domain_Model_FrontendUser');
		$userMock->expects($this->once())
			->method('getPassword')
			->will($this->returnValue($expected));

		$repositoryMock = $this->getMock('Tx_SfRegister_Domain_Repository_FrontendUserRepository', array(), array(), '', FALSE);
		$repositoryMock->expects($this->once())
			->method('findByUid')
			->with($userId)
			->will($this->returnValue($userMock));
		$this->fixture->injectUserRepository($repositoryMock);

		$this->assertTrue(
			$this->fixture->isValid($expected)
		);
	}
}

?>
 
