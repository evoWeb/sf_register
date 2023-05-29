<?php

namespace Evoweb\SfRegister\Tests\Functional\Domain\Repository;

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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendUserRepositoryTest extends AbstractTestBase
{
    protected FrontendUserRepository $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users.csv');

        $this->subject = GeneralUtility::makeInstance(FrontendUserRepository::class);
    }

    public function tearDown(): void
    {
        unset($this->subject);
        parent::tearDown();
    }

    #[Test]
    public function findByUid(): void
    {
        /** @var FrontendUser $user */
        $user = $this->subject->findByUid(1);

        self::assertInstanceOf(FrontendUser::class, $user);
        self::assertEquals('loginuser', $user->getUsername());
    }
}
