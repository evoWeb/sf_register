<?php

namespace Evoweb\SfRegister\Domain\Repository;

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

use Doctrine\DBAL\Result;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository for static info tables country zones
 */
class StaticCountryZoneRepository extends Repository
{
    protected $defaultOrderings = [
        'zn_name_local' => QueryInterface::ORDER_ASCENDING
    ];

    public function findAllByParentUid(int $parent): Result
    {
        $queryBuilder = $this->getQueryBuilderForTable('static_country_zones');
        $queryBuilder->select('zones.*')
            ->from('static_country_zones', 'zones')
            ->where($queryBuilder->expr()->eq(
                'countries.uid',
                $queryBuilder->createNamedParameter($parent, \PDO::PARAM_INT)
            ))
            ->innerJoin(
                'zones',
                'static_countries',
                'countries',
                'zones.zn_country_iso_2 = countries.cn_iso_2'
            )
            ->orderBy('zones.zn_name_local');

        return $queryBuilder->execute();
    }

    public function findAllByIso2(string $iso2): Result
    {
        $queryBuilder = $this->getQueryBuilderForTable('static_country_zones');
        $queryBuilder->select('*')
            ->from('static_country_zones')
            ->where($queryBuilder->expr()->eq(
                'zn_country_iso_2',
                $queryBuilder->createNamedParameter($iso2)
            ))
            ->orderBy('zn_name_local');

        return $queryBuilder->execute();
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $pool->getQueryBuilderForTable($table);
    }
}
