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

namespace Evoweb\SfRegister\Updates;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

#[UpgradeWizard('sfRegisterCountryMigration')]
class UserCountryMigration implements UpgradeWizardInterface, ChattyInterface
{
    private const TABLE_NAME = 'fe_users';

    protected OutputInterface $output;

    public function __construct()
    {
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return 'Migrates users countries from static countries uid to country provider alphaIso2';
    }

    public function getDescription(): string
    {
        return 'Before version 13 sf_register used countries from static_info_tables. As the core now provides
            countries itself, the usage of static_info_tables is dropped and all users need to be updated
            to retain their selected country.';
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    public function updateNecessary(): bool
    {
        try {
            $necessary = $this->getRecordsToUpdateCount() > 0;
        } catch (Exception) {
            $necessary = 0;
        }
        return $necessary;
    }

    public function executeUpdate(): bool
    {
        $records = $this->getRecordsToUpdate();
        try {
            foreach ($records->fetchAssociative() as $record) {
                $this->updateRecordWithNewCountryValue(
                    $record['uid'],
                    Country::tryFrom((int)$record['static_info_country'])->name
                );
            }
        } catch (Exception $exception) {
            $this->output->write('Querying for users throws an exception: ' . $exception->getMessage());
        }

        return true;
    }

    /**
     * @throws Exception
     */
    protected function getRecordsToUpdateCount(): int
    {
        $queryBuilder = $this->getPreparedQueryBuilder();
        $expression = $queryBuilder->expr();
        return $queryBuilder
            ->count('uid')
            ->from(self::TABLE_NAME)
            ->where(
                $expression->or(
                    $expression->like('static_info_country', $queryBuilder->quote('1%')),
                    $expression->like('static_info_country', $queryBuilder->quote('2%')),
                    $expression->like('static_info_country', $queryBuilder->quote('3%')),
                    $expression->like('static_info_country', $queryBuilder->quote('4%')),
                    $expression->like('static_info_country', $queryBuilder->quote('5%')),
                    $expression->like('static_info_country', $queryBuilder->quote('6%')),
                    $expression->like('static_info_country', $queryBuilder->quote('7%')),
                    $expression->like('static_info_country', $queryBuilder->quote('8%')),
                    $expression->like('static_info_country', $queryBuilder->quote('9%')),
                )
            )
            ->executeQuery()
            ->fetchOne();
    }

    protected function getRecordsToUpdate(): Result
    {
        $queryBuilder = $this->getPreparedQueryBuilder();
        $expression = $queryBuilder->expr();
        return $queryBuilder
            ->select('uid', 'static_info_country')
            ->from(self::TABLE_NAME)
            ->where(
                $expression->or(
                    $expression->like('static_info_country', $queryBuilder->quote('1%')),
                    $expression->like('static_info_country', $queryBuilder->quote('2%')),
                    $expression->like('static_info_country', $queryBuilder->quote('3%')),
                    $expression->like('static_info_country', $queryBuilder->quote('4%')),
                    $expression->like('static_info_country', $queryBuilder->quote('5%')),
                    $expression->like('static_info_country', $queryBuilder->quote('6%')),
                    $expression->like('static_info_country', $queryBuilder->quote('7%')),
                    $expression->like('static_info_country', $queryBuilder->quote('8%')),
                    $expression->like('static_info_country', $queryBuilder->quote('9%')),
                )
            )
            ->executeQuery();
    }

    protected function updateRecordWithNewCountryValue(int $uid, string $countryValue): void
    {
        $queryBuilder = $this->getPreparedQueryBuilder();
        $queryBuilder->update(self::TABLE_NAME)
            ->set('static_info_country', $countryValue)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                )
            )
            ->executeStatement();
    }

    protected function getPreparedQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this
            ->getConnectionPool()
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder
            ->getRestrictions()
            ->removeAll();

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
