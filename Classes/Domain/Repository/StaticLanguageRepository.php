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
 * A repository for static info tables language
 */
class StaticLanguageRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    public function findAll(): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        return $query->execute();
    }

    public function findByLgCollateLocale(array $lgCollateLocale): \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        $query->matching($query->in('lg_collate_locale', $lgCollateLocale));

        return $query->execute();
    }
}
