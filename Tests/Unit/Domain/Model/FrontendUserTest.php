<?php

namespace Evoweb\SfRegister\Tests\Unit\Domain\Model;

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

class FrontendUserTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected $subject;

    public function setUp(): void
    {
        $this->subject = new \Evoweb\SfRegister\Domain\Model\FrontendUser();
    }

    /**
     * @test
     */
    public function disableDefaultToFalseOnInitialize()
    {
        $this->assertFalse($this->subject->getDisable());
    }

    public function notEmptyDataProvider(): array
    {
        return [
            'integerGreaterZero' => [1],
            'booleanTrue' => [true],
            'notEmptyString' => ['a'],
        ];
    }

    /**
     * @param mixed $input
     * @test
     * @dataProvider notEmptyDataProvider
     */
    public function disableReturnsTrueIfSetNotEmpty($input)
    {
        $this->subject->setDisable($input);

        $this->assertTrue($this->subject->getDisable());
    }

    /**
     * @test
     */
    public function imageContainsEmptyObjectStorageOnInitialize()
    {
        $this->assertInstanceOf(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class, $this->subject->getImage());
        $this->assertEquals(0, $this->subject->getImage()->count());
    }

    /**
     * @test
     */
    public function imageReturnsStringSetBySetImage()
    {
        $expected = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

        $this->subject->setImage($expected);

        $this->assertSame($expected, $this->subject->getImage());
    }

    /**
     * @test
     */
    public function imageAsImageListAddFilenameToImage()
    {
        $this->markTestSkipped('needs to be changed to ObjectStorage');
        $expected1 = 'foo.gif';
        $expected2 = 'bar.jpg';

        // @todo fix test
        $this->subject->addImage($expected1);
        $this->subject->addImage($expected2);

        $this->assertSame(implode(',', [$expected1, $expected2]), $this->subject->getImage());
    }

    /**
     * @test
     */
    public function imageAsImageListRemoveFilenameFromImage()
    {
        $this->markTestSkipped('needs to be changed to ObjectStorage');
        $expected1 = 'foo.gif';
        $expected2 = 'bar.jpg';

        // @todo fix test
        $this->subject->setImage(implode(',', [$expected1, $expected2]));
        $this->subject->removeImage();

        $this->assertSame($expected2, $this->subject->getImage());
    }

    /**
     * @test
     */
    public function gtcDefaultToFalseOnInitialize()
    {
        $this->assertFalse($this->subject->getDisable());
    }

    /**
     * @param mixed $input
     * @test
     * @dataProvider notEmptyDataProvider
     */
    public function gtcReturnsTrueIfSetNotEmpty($input)
    {
        $this->subject->setDisable($input);

        $this->assertTrue($this->subject->getDisable());
    }

    /**
     * @test
     */
    public function mobilephoneOnInitializeIsEmpty()
    {
        $this->assertEquals('', $this->subject->getMobilephone());
    }

    /**
     * @test
     */
    public function getMobilephoneReturnsStringSetBySetMobilephone()
    {
        $expected = 'teststring';

        $this->subject->setMobilephone($expected);

        $this->assertSame($expected, $this->subject->getMobilephone());
    }
}
