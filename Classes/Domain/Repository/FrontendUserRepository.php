<?php

declare(strict_types=1);

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

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Model\FrontendUserInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * A repository for frontend user models
 *
 * @extends Repository<FrontendUser>
 */
class FrontendUserRepository extends Repository
{
    public function findByUidIgnoringDisabledField(int $uid): ?FrontendUser
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

        /** @var ?FrontendUser $user */
        $user = $result->getFirst();
        return $user;
    }

    /**
     * Finds an object matching the given identifier.
     */
    public function findByEmail(string $email, bool $ignoreHidden = false): ?FrontendUserInterface
    {
        $query = $this->createQuery();

        if (!$ignoreHidden) {
            $querySettings = $query->getQuerySettings();
            $querySettings->setIgnoreEnableFields(true);
            $querySettings->setEnableFieldsToBeIgnored(['disabled']);
        }

        $result = $query->matching(
            $query->logicalOr(
                $query->equals('email', $email),
                $query->equals('username', $email)
            )
        )
        ->execute();

        return $result->getFirst();
    }

    /**
     * Count users in storage folder which have a field that contains the value
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
