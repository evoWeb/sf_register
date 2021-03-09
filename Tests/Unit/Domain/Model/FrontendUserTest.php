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
        self::assertFalse($this->subject->getDisable());
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

        self::assertTrue($this->subject->getDisable());
    }

    /**
     * @test
     */
    public function imageContainsEmptyObjectStorageOnInitialize()
    {
        self::assertInstanceOf(\TYPO3\CMS\Extbase\Persistence\ObjectStorage::class, $this->subject->getImage());
        self::assertEquals(0, $this->subject->getImage()->count());
    }

    /**
     * @test
     */
    public function imageReturnsStringSetBySetImage()
    {
        $expected = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

        $this->subject->setImage($expected);

        self::assertSame($expected, $this->subject->getImage());
    }

    /**
     * @test
     */
    public function imageAsImageListAddFilenameToImage()
    {
        self::markTestSkipped('needs to be changed to ObjectStorage');
        $expected1 = 'foo.gif';
        $expected2 = 'bar.jpg';

        // @todo fix test
        $this->subject->addImage($expected1);
        $this->subject->addImage($expected2);

        self::assertSame(implode(',', [$expected1, $expected2]), $this->subject->getImage());
    }

    /**
     * @test
     */
    public function imageAsImageListRemoveFilenameFromImage()
    {
        self::markTestSkipped('needs to be changed to ObjectStorage');
        $expected1 = 'foo.gif';
        $expected2 = 'bar.jpg';

        // @todo fix test
        $this->subject->setImage(implode(',', [$expected1, $expected2]));
        $this->subject->removeImage();

        self::assertSame($expected2, $this->subject->getImage());
    }

    /**
     * @test
     */
    public function gtcDefaultToFalseOnInitialize()
    {
        self::assertFalse($this->subject->getDisable());
    }

    /**
     * @param mixed $input
     * @test
     * @dataProvider notEmptyDataProvider
     */
    public function gtcReturnsTrueIfSetNotEmpty($input)
    {
        $this->subject->setDisable($input);

        self::assertTrue($this->subject->getDisable());
    }

    /**
     * @test
     */
    public function mobilephoneOnInitializeIsEmpty()
    {
        self::assertEquals('', $this->subject->getMobilephone());
    }

    /**
     * @test
     */
    public function getMobilephoneReturnsStringSetBySetMobilephone()
    {
        $expected = 'teststring';

        $this->subject->setMobilephone($expected);

        self::assertSame($expected, $this->subject->getMobilephone());
    }
}
