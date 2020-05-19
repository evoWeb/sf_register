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
     * @return NULL|\Evoweb\SfRegister\Interfaces\FrontendUserInterface|object
     */
    public function findByIdentifier($identifier, bool $ignoreHidden = false)
    {
        trigger_error('Method ' . __METHOD__ . ' will be removed in sf_register 10.0', E_USER_DEPRECATED);
        $query = $this->createQuery();

        if (!$ignoreHidden) {
            $querySettings = $query->getQuerySettings();
            $querySettings->setRespectStoragePage(false);
            $querySettings->setRespectSysLanguage(false);
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setEnableFieldsToBeIgnored(['disabled']);
        }

        $result = $query->matching(
            $query->equals('uid', $identifier)
        )
        ->execute();

        return $result->getFirst();
    }

    /**
     * Finds an object matching the given identifier.
     *
     * @param string $email The Email address of the object to find
     * @param bool $ignoreHidden Whether to ignore hidden state
     *
     * @return NULL|\Evoweb\SfRegister\Interfaces\FrontendUserInterface|object
     */
    public function findByEmail(string $email, bool $ignoreHidden = false)
    {
        $query = $this->createQuery();

        if (!$ignoreHidden) {
            $querySettings = $query->getQuerySettings();
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setEnableFieldsToBeIgnored(['disabled']);
        }

        $result = $query->matching($query->logicalOr([
            $query->equals('email', $email),
            $query->equals('username', $email)
        ]))
        ->execute();

        return $result->getFirst();
    }

    /**
     * Count users in storage folder which have a field that contains the value
     *
     * @param string $field
     * @param string $value
     * @param boolean $respectStoragePage
     *
     * @return int
     */
    public function countByField(string $field, string $value, bool $respectStoragePage = true): int
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

    public function countByFieldGlobal(string $field, string $value): int
    {
        return $this->countByField($field, $value, false);
    }
}
