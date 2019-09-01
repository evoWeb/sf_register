<?php
namespace Evoweb\SfRegister\Domain\Repository;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * A repository for static info tables country zones
 */
class StaticCountryZoneRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'zn_name_local' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    ];

    public function findAllByParentUid(int $parent): \Doctrine\DBAL\Driver\Statement
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

    public function findAllByIso2(string $iso2): \Doctrine\DBAL\Driver\Statement
    {
        $queryBuilder = $this->getQueryBuilderForTable('static_country_zones');
        $queryBuilder->select('*')
            ->from('static_country_zones')
            ->where($queryBuilder->expr()->eq(
                'zn_country_iso_2',
                $queryBuilder->createNamedParameter($iso2, \PDO::PARAM_STR)
            ))
            ->orderBy('zn_name_local');

        return $queryBuilder->execute();
    }

    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getQueryBuilderForTable($table);
    }
}
