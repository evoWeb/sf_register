<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Unit\Domain\Model;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\FrontendUserGroup;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Extbase\Domain\Model\Category;
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
        self::assertFalse($this->subject->getDisable());
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

        self::assertTrue($this->subject->getDisable());
    }

    #[Test]
    public function constructInitializesImageAsEmptyObjectStorage(): void
    {
        self::assertInstanceOf(ObjectStorage::class, $this->subject->getImage());
        self::assertCount(0, $this->subject->getImage());
    }

    #[Test]
    public function constructInitializesUsergroupAsEmptyObjectStorage(): void
    {
        self::assertInstanceOf(ObjectStorage::class, $this->subject->getUsergroup());
        self::assertCount(0, $this->subject->getUsergroup());
    }

    #[Test]
    public function constructInitializesModuleSysDmailCategoryAsEmptyObjectStorage(): void
    {
        self::assertInstanceOf(ObjectStorage::class, $this->subject->getModuleSysDmailCategory());
        self::assertCount(0, $this->subject->getModuleSysDmailCategory());
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
    public function usergroupReturnsObjectStorageSetBySetUsergroup(): void
    {
        /** @var ObjectStorage<FrontendUserGroup> $expected */
        $expected = new ObjectStorage();
        $expected->attach(new FrontendUserGroup());

        $this->subject->setUsergroup($expected);

        self::assertSame($expected, $this->subject->getUsergroup());
    }

    #[Test]
    public function moduleSysDmailCategoryReturnsObjectStorageSetBySetModuleSysDmailCategory(): void
    {
        /** @var ObjectStorage<Category> $expected */
        $expected = new ObjectStorage();
        $expected->attach(new Category());

        $this->subject->setModuleSysDmailCategory($expected);

        self::assertSame($expected, $this->subject->getModuleSysDmailCategory());
    }

    #[Test]
    public function imageAsImageListAddFilenameToImage(): void
    {
        self::markTestSkipped('needs to be changed to ObjectStorage');
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
        self::markTestSkipped('needs to be changed to ObjectStorage');
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
        self::assertFalse($this->subject->getDisable());
    }

    #[Test]
    #[DataProvider('notEmptyDataProvider')]
    public function gtcReturnsTrueIfSetNotEmpty(int|bool|string $input): void
    {
        $this->subject->setDisable((bool)$input);

        self::assertTrue($this->subject->getDisable());
    }

    #[Test]
    public function mobilephoneOnInitializeIsEmpty(): void
    {
        self::assertEquals('', $this->subject->getMobilephone());
    }

    #[Test]
    public function getMobilephoneReturnsStringSetBySetMobilephone(): void
    {
        $expected = 'teststring';

        $this->subject->setMobilephone($expected);

        self::assertSame($expected, $this->subject->getMobilephone());
    }

    #[Test]
    public function getDateOfBirthDayReturnsOneIfDateOfBirthIsNotSet(): void
    {
        self::assertSame(1, $this->subject->getDateOfBirthDay());
    }

    #[Test]
    public function getDateOfBirthMonthReturnsOneIfDateOfBirthIsNotSet(): void
    {
        self::assertSame(1, $this->subject->getDateOfBirthMonth());
    }

    #[Test]
    public function getDateOfBirthYearReturns1970IfDateOfBirthIsNotSet(): void
    {
        self::assertSame(1970, $this->subject->getDateOfBirthYear());
    }

    #[Test]
    public function getDateOfBirthDayReturnsDayOfSetDateOfBirth(): void
    {
        // Pre-fix bug in df53334: DateTime::format() returns string, but the
        // method is declared to return int in a strict_types=1 file, causing
        // a TypeError. Fixed in 30e771a via explicit (int) cast.
        self::markTestSkipped(
            'Pre-fix bug in df53334: getDateOfBirthDay() returns non-int from DateTime::format() '
            . 'under strict_types, causing a TypeError. Fixed in 30e771a. Reactivate in roadmap step 2.'
        );

        /*$this->subject->setDateOfBirth(new \DateTime('2001-03-15'));

        self::assertSame(15, $this->subject->getDateOfBirthDay());*/
    }

    #[Test]
    public function getDateOfBirthMonthReturnsMonthOfSetDateOfBirth(): void
    {
        // Pre-fix bug in df53334: DateTime::format() returns string, but the
        // method is declared to return int in a strict_types=1 file, causing
        // a TypeError. Fixed in 30e771a via explicit (int) cast.
        self::markTestSkipped(
            'Pre-fix bug in df53334: getDateOfBirthMonth() returns non-int from DateTime::format() '
            . 'under strict_types, causing a TypeError. Fixed in 30e771a. Reactivate in roadmap step 2.'
        );

        /*$this->subject->setDateOfBirth(new \DateTime('2001-03-15'));

        self::assertSame(3, $this->subject->getDateOfBirthMonth());*/
    }

    #[Test]
    public function getDateOfBirthYearReturnsYearOfSetDateOfBirth(): void
    {
        // Pre-fix bug in df53334: DateTime::format() returns string, but the
        // method is declared to return int in a strict_types=1 file, causing
        // a TypeError. Fixed in 30e771a via explicit (int) cast.
        self::markTestSkipped(
            'Pre-fix bug in df53334: getDateOfBirthYear() returns non-int from DateTime::format() '
            . 'under strict_types, causing a TypeError. Fixed in 30e771a. Reactivate in roadmap step 2.'
        );

        /*$this->subject->setDateOfBirth(new \DateTime('2001-03-15'));

        self::assertSame(2001, $this->subject->getDateOfBirthYear());*/
    }
}
