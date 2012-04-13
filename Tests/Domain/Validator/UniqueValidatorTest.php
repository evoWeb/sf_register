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

class Tx_SfRegister_Domain_Model_UniqueValidatorTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_SfRegister_Domain_Validator_UniqueValidator
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
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->testingFramework);
	}

	/**
	 * @test
	 */
	public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalSearch() {
		$fieldName = 'username';
		$expected = 'myValue';

		/** @var $fixture Tx_SfRegister_Domain_Validator_UniqueValidator */
		$fixture = $this->getMock('Tx_SfRegister_Domain_Validator_UniqueValidator', NULL);
		$fixture->setFieldname($fieldName);

		/** @var $repositoryMock Tx_SfRegister_Domain_Repository_FrontendUserRepository */
		$repositoryMock = $this->getMock('Tx_SfRegister_Domain_Repository_FrontendUserRepository', array(), array(), '', FALSE);
		$repositoryMock->expects($this->once())
			->method('countByField')
			->with($fieldName, $expected)
			->will($this->returnValue(0));
		$fixture->injectUserRepository($repositoryMock);

		$this->assertTrue(
			$fixture->isValid($expected)
		);
	}

	/**
	 * @test
	 */
	public function isValidReturnsFalseIfCountOfValueInFieldReturnsHigherThenZeroForLocalSearch() {
		$fieldName = 'username';
		$expected = 'myValue';

		/** @var $fixture Tx_SfRegister_Domain_Validator_UniqueValidator */
		$fixture = $this->getMock('Tx_SfRegister_Domain_Validator_UniqueValidator', NULL);
		$fixture->setFieldname($fieldName);

		/** @var $repositoryMock Tx_SfRegister_Domain_Repository_FrontendUserRepository */
		$repositoryMock = $this->getMock('Tx_SfRegister_Domain_Repository_FrontendUserRepository', array(), array(), '', FALSE);
		$repositoryMock->expects($this->once())
			->method('countByField')
			->with($fieldName, $expected)
			->will($this->returnValue(1));
		$fixture->injectUserRepository($repositoryMock);

		$this->assertFalse(
			$fixture->isValid($expected)
		);
	}

	/**
	 * @test
	 */
	public function isValidReturnsTrueIfCountOfValueInFieldReturnsZeroForLocalAndGlobalSearch() {
		$fieldName = 'username';
		$expected = 'myValue';

		/** @var $fixture Tx_SfRegister_Domain_Validator_UniqueValidator */
		$fixture = $this->getAccessibleMock('Tx_SfRegister_Domain_Validator_UniqueValidator', array('dummy'));
		$fixture->setFieldname($fieldName);
		$fixture->setOptions(array('global' => TRUE));

		/** @var $repositoryMock Tx_SfRegister_Domain_Repository_FrontendUserRepository */
		$repositoryMock = $this->getMock('Tx_SfRegister_Domain_Repository_FrontendUserRepository', array(), array(), '', FALSE);
		$repositoryMock->expects($this->once())
			->method('countByField')
			->with($fieldName, $expected)
			->will($this->returnValue(0));
		$repositoryMock->expects($this->any())
			->method('countByFieldGlobal')
			->with($fieldName, $expected)
			->will($this->returnValue(0));
		$fixture->injectUserRepository($repositoryMock);

		$this->assertTrue(
			$fixture->isValid($expected)
		);
	}

	/**
	 * @test
	 */
	public function isValidReturnsFalseIfCountOfValueInFieldReturnsZeroForLocalAndHigherThenZeroForGlobalSearch() {
		$fieldName = 'username';
		$expected = 'myValue';

		/** @var $fixture Tx_SfRegister_Domain_Validator_UniqueValidator */
		$fixture = $this->getMock('Tx_SfRegister_Domain_Validator_UniqueValidator', NULL);
		$fixture->setFieldname($fieldName);
		$fixture->setOptions(array('global' => TRUE));

		/** @var $repositoryMock Tx_SfRegister_Domain_Repository_FrontendUserRepository */
		$repositoryMock = $this->getMock('Tx_SfRegister_Domain_Repository_FrontendUserRepository', array(), array(), '', FALSE);
		$repositoryMock->expects($this->once())
			->method('countByField')
			->with($fieldName, $expected)
			->will($this->returnValue(0));
		$repositoryMock->expects($this->any())
			->method('countByFieldGlobal')
			->with($fieldName, $expected)
			->will($this->returnValue(1));
		$fixture->injectUserRepository($repositoryMock);

		$this->assertFalse(
			$fixture->isValid($expected)
		);
	}
}

?>

