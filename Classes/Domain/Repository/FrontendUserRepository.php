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

use Evoweb\SfRegister\Domain\Model\FrontendUserInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository for frontend user models
 */
class FrontendUserRepository extends Repository
{
    public function findByUidIgnoringDisabledField(int $uid)
    {
        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $querySettings->setIgnoreEnableFields(true);
        $querySettings->setEnableFieldsToBeIgnored(['disabled']);

        $result = $query
            ->matching(
                $query->equals('uid', $uid)
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
     * @return ?FrontendUserInterface
     */
    public function findByEmail(string $email, bool $ignoreHidden = false): ?FrontendUserInterface
    {
        $query = $this->createQuery();

        if (!$ignoreHidden) {
            $querySettings = $query->getQuerySettings();
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setEnableFieldsToBeIgnored(['disabled']);
        }

        $result = $query->matching($query->logicalOr(
            $query->equals('email', $email),
            $query->equals('username', $email)
        ))
        ->execute();

        return $result->getFirst();
    }

    /**
     * Count users in storage folder which have a field that contains the value
     *
     * @param string $field
     * @param string $value
     * @param bool $respectStoragePage
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
