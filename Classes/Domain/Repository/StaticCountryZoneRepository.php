<?php
namespace Evoweb\SfRegister\Domain\Repository;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * A repository for static info tables country zones
 *
 * @author Sebastian Fischer <typo3@evoweb.de>
 */
class StaticCountryZoneRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Find all countries despecting the storage page
     *
     * @param string $iso2
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult
     */
    public function findAllByIso2($iso2)
    {
        /**
         * Query
         *
         * @var \TYPO3\CMS\Extbase\Persistence\Generic\Query $query
         */
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->statement(
            'SELECT * FROM static_country_zones WHERE zn_country_iso_2 = "?" AND deleted = 0',
            array($iso2)
        );

        return $query->execute();
    }
}
