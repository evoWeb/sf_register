<?php

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

namespace Evoweb\SfRegister\Tests\Functional\Domain\Repository;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Backend;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

class FrontendUserRepositoryTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_groups.csv');
        $this->importCSVDataSet(__DIR__ . '/../../../Fixtures/fe_users.csv');
    }

    #[Test]
    public function findByUid(): void
    {
        $serverRequestFactory = new ServerRequestFactory();
        $serverRequest = $serverRequestFactory->createServerRequest('GET', '/');
        $serverRequest = $serverRequest->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configurationManager->setRequest($serverRequest);
        $dataMapFactory = GeneralUtility::makeInstance(DataMapFactory::class);
        $container = GeneralUtility::makeInstance(ContainerInterface::class);

        $queryFactory = new QueryFactory(
            $configurationManager,
            $dataMapFactory,
            $container
        );
        $backend = GeneralUtility::makeInstance(Backend::class);
        $persistenceSession = GeneralUtility::makeInstance(Session::class);

        $persistenceManager = new PersistenceManager(
            $queryFactory,
            $backend,
            $persistenceSession
        );

        /** @var FrontendUserRepository $subject */
        $subject = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $subject->injectPersistenceManager($persistenceManager);

        /** @var FrontendUser $user */
        $user = $subject->findByUid(1);

        $this->assertInstanceOf(FrontendUser::class, $user);
        $this->assertEquals('testuser', $user->getUsername());
    }
}
