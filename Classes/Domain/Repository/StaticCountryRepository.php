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

namespace Evoweb\SfRegister\Domain\Repository;

use Evoweb\SfRegister\Domain\Model\StaticCountry;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository for static info tables country
 *
 * @extends Repository<StaticCountry>
 */
class StaticCountryRepository extends Repository
{
    /**
     * @return QueryResult<StaticCountry>
     */
    public function findAll(): QueryResult
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        /** @var QueryResult<StaticCountry> $result */
        $result = $query->execute();
        return $result;
    }

    /**
     * @param array<int, string> $cnIso2
     * @return QueryResult<StaticCountry>
     */
    public function findByCnIso2(array $cnIso2): QueryResult
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        try {
            $query->matching($query->in('cn_iso_2', $cnIso2));
        } catch (\Exception) {
        }

        /** @var QueryResult<StaticCountry> $result */
        $result = $query->execute();
        return $result;
    }
}
