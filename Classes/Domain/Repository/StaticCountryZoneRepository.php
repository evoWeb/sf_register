<?php
namespace Evoweb\SfRegister\Domain\Repository;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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
 *
 * @author Sebastian Fischer <typo3@evoweb.de>
 */
class StaticCountryZoneRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * Find all countries disrespecting the storage page
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
        $query->getQuerySettings()
            ->setRespectStoragePage(false);

        $query->matching($query->equals('zn_country_iso_2', $iso2));

        return $query->execute();
    }
}
