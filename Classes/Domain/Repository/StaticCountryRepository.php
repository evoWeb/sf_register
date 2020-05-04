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

/**
 * A repository for static info tables country
 */
class StaticCountryRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function findAll(): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        return $query->execute();
    }

    public function findByCnIso2(array $cnIso2): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        $query->matching($query->in('cn_iso_2', $cnIso2));

        return $query->execute();
    }
}
