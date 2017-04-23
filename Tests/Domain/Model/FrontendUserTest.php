<?php
namespace Evoweb\SfRegister\Tests\Domain\Model;

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

/**
 * Class FrontendUserTest
 */
class FrontendUserTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected $fixture;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->fixture = new \Evoweb\SfRegister\Domain\Model\FrontendUser();
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->fixture);
    }

    /**
     * @test
     * @return void
     */
    public function disableDefaultToFalseOnInitialize()
    {
        $this->assertFalse(
            $this->fixture->getDisable()
        );
    }

    /**
     * @return array
     */
    public function notEmptyDataProvider()
    {
        return array(
            'integerGreaterZero' => array(1),
            'booleanTrue' => array(TRUE),
            'notEmptyString' => array('a'),
        );
    }

    /**
     * @param mixed $input
     * @test
     * @return void
     * @dataProvider notEmptyDataProvider
     */
    public function disableReturnsTrueIfSetNotEmpty($input)
    {
        $this->fixture->setDisable($input);

        $this->assertTrue(
            $this->fixture->getDisable()
        );
    }

    /**
     * @test
     * @return void
     */
    public function mailhashOnInitializeIsNull()
    {
        $this->assertNull(
            $this->fixture->getMailhash()
        );
    }

    /**
     * @test
     * @return void
     */
    public function mailhashValueGetsTrimmedOnSet()
    {
        $expected = 'Test ';

        $this->fixture->setMailhash($expected);

        $this->assertSame(
            trim($expected),
            $this->fixture->getMailhash()
        );
    }

    /**
     * @test
     * @return void
     */
    public function imageContainsEmptyStringOnInitialize()
    {
        $this->assertSame(
            '',
            $this->fixture->getImage()
        );
    }

    /**
     * @test
     * @return void
     */
    public function imageReturnsStringSetBySetImage()
    {
        $expected = 'teststring';

        $this->fixture->setImage($expected);

        $this->assertSame(
            $expected,
            $this->fixture->getImage()
        );
    }

    /**
     * @test
     * @return void
     */
    public function getImagelistReturnsArray()
    {
        $this->assertInternalType(
            'array',
            $this->fixture->getImageList()
        );
    }

    /**
     * @test
     * @return void
     */
    public function setImagelistSetsArrayAsListInImage()
    {
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
     * @return void
     */
    public function imageAsImageListAddFilenameToImage()
    {
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
     * @return void
     */
    public function imageAsImageListRemoveFilenameFromImage()
    {
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
     * @return void
     */
    public function gtcDefaultToFalseOnInitialize()
    {
        $this->assertFalse(
            $this->fixture->getDisable()
        );
    }

    /**
     * @param mixed $input
     * @test
     * @return void
     * @dataProvider notEmptyDataProvider
     */
    public function gtcReturnsTrueIfSetNotEmpty($input)
    {
        $this->fixture->setDisable($input);

        $this->assertTrue(
            $this->fixture->getDisable()
        );
    }

    /**
     * @test
     * @return void
     */
    public function mobilphoneOnInitializeIsNull()
    {
        $this->assertNull(
            $this->fixture->getMobilephone()
        );
    }

    /**
     * @test
     * @return void
     */
    public function getMobilephoneReturnsStringSetBySetMobilphone()
    {
        $expected = 'teststring';

        $this->fixture->setMobilephone($expected);

        $this->assertSame(
            $expected,
            $this->fixture->getMobilephone()
        );
    }
}
