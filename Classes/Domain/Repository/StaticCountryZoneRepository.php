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

namespace Evoweb\SfRegister\Domain\Repository;

use Doctrine\DBAL\Result;
use Evoweb\SfRegister\Domain\Model\StaticCountryZone;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository for static info tables country zones
 *
 * @extends Repository<StaticCountryZone>
 */
class StaticCountryZoneRepository extends Repository
{
    public function __construct(protected ConnectionPool $connectionPool)
    {
        parent::__construct();
    }

    public function findAllByParentUid(int $parent): Result
    {
        $queryBuilder = $this->getQueryBuilderForTable('static_country_zones');
        $queryBuilder->select('zones.*')
            ->from('static_country_zones', 'zones')
            ->innerJoin(
                'zones',
                'static_countries',
                'countries',
                $queryBuilder->expr()->eq(
                    'zones.zn_country_iso_2',
                    $queryBuilder->quoteIdentifier('countries.cn_iso_2'),
                ),
            )
            ->where($queryBuilder->expr()->eq(
                'countries.uid',
                $queryBuilder->createNamedParameter($parent, Connection::PARAM_INT),
            ))
            ->orderBy('zones.zn_name_local');

        return $queryBuilder->executeQuery();
    }

    public function findAllByIso2(string $iso2): Result
    {
        $queryBuilder = $this->getQueryBuilderForTable('static_country_zones');
        $queryBuilder->select('*')
            ->from('static_country_zones')
            ->where($queryBuilder->expr()->eq(
                'zn_country_iso_2',
                $queryBuilder->createNamedParameter($iso2),
            ))
            ->orderBy('zn_name_local');

        return $queryBuilder->executeQuery();
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable($table);
    }
}
