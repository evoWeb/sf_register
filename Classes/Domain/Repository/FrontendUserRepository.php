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
 * A repository for feusers
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
{
    /**
     * Finds an object matching the given identifier.
     *
     * @param mixed $identifier The identifier of the object to find
     * @param bool $ignoreHidden Whether to ignore hidden state
     *
     * @return NULL|\Evoweb\SfRegister\Interfaces\FrontendUserInterface
     */
    public function findByIdentifier($identifier, $ignoreHidden = false)
    {
        if ($ignoreHidden) {
            return parent::findByIdentifier($identifier);
        }

        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $querySettings->setRespectSysLanguage(false);
        $querySettings->setIgnoreEnableFields(true);
        $querySettings->setEnableFieldsToBeIgnored(array('disabled'));

        $object = $query->matching($query->equals('uid', $identifier))
            ->execute()
            ->getFirst();

        return $object;
    }

    /**
     * Find user by mailhash
     *
     * @param string $mailhash
     *
     * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    public function findByMailhash($mailhash)
    {
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(true);
        $querySettings->setIgnoreEnableFields(true);

        $user = $query->matching($query->equals('mailhash', $mailhash))
            ->execute()
            ->getFirst();

        return $user;
    }

    /**
     * Count users in storagefolder which have a field that contains the value
     *
     * @param string $field
     * @param string $value
     * @param boolean $respectStoragePage
     *
     * @return integer
     */
    public function countByField($field, $value, $respectStoragePage = true)
    {
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage($respectStoragePage);
        $querySettings->setIgnoreEnableFields(true);

        return $query->matching($query->equals($field, $value))
            ->setLimit(1)
            ->execute()
            ->count();
    }

    /**
     * Count users installationwide which have a field that contains the value
     *
     * @param string $field
     * @param string $value
     *
     * @return integer
     */
    public function countByFieldGlobal($field, $value)
    {
        return $this->countByField($field, $value, false);
    }
}
