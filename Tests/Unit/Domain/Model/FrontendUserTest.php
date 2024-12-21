<?php

declare(strict_types=1);

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

namespace Evoweb\SfRegister\Tests\Unit\Domain\Model;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FrontendUserTest extends UnitTestCase
{
    protected FrontendUser $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new FrontendUser();
    }

    #[Test]
    public function disableDefaultToFalseOnInitialize(): void
    {
        $this->assertFalse($this->subject->getDisable());
    }

    /**
     * @return array<string, array<int|bool|string>>
     */
    public static function notEmptyDataProvider(): array
    {
        return [
            'integerGreaterZero' => [1],
            'booleanTrue' => [true],
            'notEmptyString' => ['a'],
        ];
    }

    #[Test]
    #[DataProvider('notEmptyDataProvider')]
    public function disableReturnsTrueIfSetNotEmpty(int|bool|string $input): void
    {
        $this->subject->setDisable((bool)$input);

        $this->assertTrue($this->subject->getDisable());
    }

    #[Test]
    public function imageReturnsStringSetBySetImage(): void
    {
        /** @var ObjectStorage<FileReference> $expected */
        $expected = new ObjectStorage();

        $this->subject->setImage($expected);

        self::assertSame($expected, $this->subject->getImage());
    }

    #[Test]
    public function imageAsImageListAddFilenameToImage(): void
    {
        $this->markTestSkipped('needs to be changed to ObjectStorage');
        /*$expected1 = 'foo.gif';
        $expected2 = 'bar.jpg';

        // @todo fix test
        $this->subject->addImage($expected1);
        $this->subject->addImage($expected2);

        $this->assertSame(implode(',', [$expected1, $expected2]), $this->subject->getImage());*/
    }

    #[Test]
    public function imageAsImageListRemoveFilenameFromImage(): void
    {
        $this->markTestSkipped('needs to be changed to ObjectStorage');
        /*$expected1 = 'foo.gif';
        $expected2 = 'bar.jpg';

        // @todo fix test
        $this->subject->setImage(implode(',', [$expected1, $expected2]));
        $this->subject->removeImage();

        $this->assertSame($expected2, $this->subject->getImage());*/
    }

    #[Test]
    public function gtcDefaultToFalseOnInitialize(): void
    {
        $this->assertFalse($this->subject->getDisable());
    }

    #[Test]
    #[DataProvider('notEmptyDataProvider')]
    public function gtcReturnsTrueIfSetNotEmpty(int|bool|string $input): void
    {
        $this->subject->setDisable((bool)$input);

        $this->assertTrue($this->subject->getDisable());
    }

    #[Test]
    public function mobilephoneOnInitializeIsEmpty(): void
    {
        $this->assertEquals('', $this->subject->getMobilephone());
    }

    #[Test]
    public function getMobilephoneReturnsStringSetBySetMobilephone(): void
    {
        $expected = 'teststring';

        $this->subject->setMobilephone($expected);

        $this->assertSame($expected, $this->subject->getMobilephone());
    }
}
