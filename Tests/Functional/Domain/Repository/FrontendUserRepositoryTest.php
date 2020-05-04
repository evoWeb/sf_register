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

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FrontendUserRepositoryTest extends \Evoweb\SfRegister\Tests\Functional\FunctionalTestCase
{
    /**
     * @var FrontendUserRepository
     */
    protected $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importDataSet(__DIR__ . '/../../Fixtures/pages.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/sys_template.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/fe_groups.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/fe_users.xml');

        /** @var FrontendUserRepository subject */
        $this->subject = GeneralUtility::makeInstance(FrontendUserRepository::class);
    }

    public function tearDown(): void
    {
        unset($this->subject);
    }

    /**
     * @test
     */
    public function findByUid()
    {
        /** @var \Evoweb\SfRegister\Domain\Model\FrontendUser $user */
        $user = $this->subject->findByUid(1);

        $this->assertInstanceOf(\Evoweb\SfRegister\Domain\Model\FrontendUser::class, $user);
        $this->assertEquals('loginuser', $user->getUsername());
    }
}
