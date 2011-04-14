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

class Tx_SfRegister_Domain_Model_FrontendUserTest extends Tx_Extbase_Tests_Unit_BaseTestCase {
	/**
	 * @var Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new Tx_SfRegister_Domain_Model_FrontendUser();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function disableDefaultToFalseOnInitialize() {
		$this->assertFalse(
			$this->fixture->getDisable()
		);
	}

	/**
	 * @return array
	 */
	public function notEmptyDataProvider() {
		return array(
			'integerGreaterZero' => array(1),
			'booleanTrue' => array(true),
			'notEmptyString' => array('a'),
		);
	}

	/**
	 * @test
	 * @dataProvider notEmptyDataProvider
	 */
	public function disableReturnsTrueIfSetNotEmpty($input) {
		$this->fixture->setDisable($input);

		$this->assertTrue(
			$this->fixture->getDisable()
		);
	}

	/**
	 * @test
	 */
	public function mailhashOnInitializeIsNull() {
		$this->assertNull(
			$this->fixture->getMailhash()
		);
	}

	/**
	 * @test
	 */
	public function mailhashValueGetsTrimmedOnSet() {
		$expected = 'Test ';

		$this->fixture->setMailhash($expected);

		$this->assertSame(
			trim($expected),
			$this->fixture->getMailhash()
		);
	}

	/**
	 * @test
	 */
	public function imageContainsEmptyStringOnInitialize() {
		$this->assertSame(
			'',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function imageReturnsStringSetBySetImage() {
		$expected = 'teststring';

		$this->fixture->setImage($expected);

		$this->assertSame(
			$expected,
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function getImagelistReturnsArray() {
		$this->assertInternalType(
			'array',
			$this->fixture->getImageList()
		);
	}

	/**
	 * @test
	 */
	public function setImagelistSetsArrayAsListInImage() {
		$expected1 = 'foo.gif';
		$expected2 = 'bar.jpg';
		$this->fixture->setImageList(array($expected1, $expected2));

		$this->assertInternalType(
			'string',
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function imageAsImageListAddFilenameToImage() {
		$expected1 = 'foo.gif';
		$expected2 = 'bar.jpg';

		$this->fixture->addImage($expected1);
		$this->fixture->addImage($expected2);

		$this->assertSame(
			implode(',', array($expected1, $expected2)),
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function imageAsImageListRemoveFilenameFromImage() {
		$expected1 = 'foo.gif';
		$expected2 = 'bar.jpg';

		$this->fixture->setImage(implode(',', array($expected1, $expected2)));
		$this->fixture->removeImage($expected1);

		$this->assertSame(
			$expected2,
			$this->fixture->getImage()
		);
	}

	/**
	 * @test
	 */
	public function gtcDefaultToFalseOnInitialize() {
		$this->assertFalse(
			$this->fixture->getDisable()
		);
	}

	/**
	 * @test
	 * @dataProvider notEmptyDataProvider
	 */
	public function gtcReturnsTrueIfSetNotEmpty($input) {
		$this->fixture->setDisable($input);

		$this->assertTrue(
			$this->fixture->getDisable()
		);
	}

	/**
	 * @test
	 */
	public function mobilphoneOnInitializeIsNull() {
		$this->assertNull(
			$this->fixture->getMobilephone()
		);
	}

	/**
	 * @test
	 */
	public function getMobilephoneReturnsStringSetBySetMobilphone() {
		$expected = 'teststring';

		$this->fixture->setMobilephone($expected);

		$this->assertSame(
			$expected,
			$this->fixture->getMobilephone()
		);
	}
}

?>