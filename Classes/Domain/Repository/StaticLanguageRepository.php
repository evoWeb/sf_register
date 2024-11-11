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

use Evoweb\SfRegister\Domain\Model\StaticLanguage;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository for static info tables language
 *
 * @extends Repository<StaticLanguage>
 */
class StaticLanguageRepository extends Repository
{
    /**
     * @return QueryResult<StaticLanguage>
     */
    public function findAll(): QueryResult
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        /** @var QueryResult<StaticLanguage> $result */
        $result = $query->execute();
        return $result;
    }

    /**
     * @param array<int, string> $lgCollateLocale
     * @return QueryResult<StaticLanguage>
     */
    public function findByLgCollateLocale(array $lgCollateLocale): QueryResult
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        try {
            $query->matching($query->in('lg_collate_locale', $lgCollateLocale));
        } catch (\Exception) {
        }

        /** @var QueryResult<StaticLanguage> $result */
        $result = $query->execute();
        return $result;
    }
}
