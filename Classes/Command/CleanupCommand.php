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

namespace Evoweb\SfRegister\Command;

use Doctrine\DBAL\Exception as DbalException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\FileOperationErrorException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFileAccessPermissionsException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CleanupCommand extends Command
{
    public function __construct(protected ResourceFactory $resourceFactory)
    {
        parent::__construct();
    }

    /**
     * Configure the command by defining arguments
     */
    protected function configure(): void
    {
        $this
            ->addArgument(
                'inactiveGroups',
                InputArgument::REQUIRED,
                'Comma separated groups that users are assigned to'
                    . ' that mark temporary accounts. (Pre confirmation groups)'
            )
            ->addArgument(
                'days',
                InputArgument::OPTIONAL,
                'Days the user did not confirmed the registration',
                14
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $result = self::FAILURE;

        $inactiveGroups = GeneralUtility::intExplode(',', $input->getArgument('inactiveGroups'));
        if (empty($inactiveGroups)) {
            $io->comment('List of group marking inactive users may not be empty to prevent unwanted behaviour!');
        } else {
            $days = (int)$input->getArgument('days');

            try {
                foreach ($inactiveGroups as $inactiveGroup) {
                    $users = $this->findInOutdatedTemporaryUsers($inactiveGroup, $days);
                    foreach ($users as $user) {
                        $this->removeUser($user);
                        $references = $this->fetchReference($user);
                        $this->removeReference($user);
                        $this->removeImage($references);
                    }
                }

                $io->comment('Cleaned up all outdated temporary accounts.');
                $result = self::SUCCESS;
            } catch (\Exception $exception) {
                $io->comment($exception->getMessage());
            }
        }

        return $result;
    }

    /**
     * @throws DbalException
     */
    protected function findInOutdatedTemporaryUsers(int $inactiveUserGroup, int $days): array
    {
        $table = 'fe_users';
        $queryBuilder = $this->getQueryBuilderForTable($table);

        $result = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->inSet(
                    'usergroup',
                    $queryBuilder->createNamedParameter($inactiveUserGroup, Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->lte(
                    'crdate',
                    $queryBuilder->createNamedParameter(time() - (3600 * 24 * $days), Connection::PARAM_INT)
                )
            )
            ->executeQuery();

        return $result->fetchAllAssociative();
    }

    protected function removeUser(array $user): void
    {
        $table = 'fe_users';
        $this->getQueryBuilderForTable($table)
            ->getConnection()
            ->delete($table, [
                'uid' => $user['uid'],
            ]);
    }

    /**
     * @throws DbalException
     */
    protected function fetchReference(array $user): array
    {
        $table = 'sys_file_reference';
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $result = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'tablenames',
                    $queryBuilder->createNamedParameter('fe_users')
                ),
                $queryBuilder->expr()->eq(
                    'fieldname',
                    $queryBuilder->createNamedParameter('image')
                ),
                $queryBuilder->expr()->eq(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($user['uid'], Connection::PARAM_INT)
                )
            )
            ->executeQuery();

        return $result->fetchAllAssociative();
    }

    protected function removeReference(array $user): void
    {
        $table = 'sys_file_reference';
        $this->getQueryBuilderForTable($table)
            ->getConnection()
            ->delete($table, [
                'uid_foreign' => $user['uid'],
                'tablenames' => $user['fe_users'],
                'fieldname' => $user['image'],
            ]);
    }

    /**
     * @throws InsufficientFileAccessPermissionsException
     * @throws FileDoesNotExistException
     * @throws FileOperationErrorException
     */
    protected function removeImage(array $references): void
    {
        foreach ($references as $reference) {
            $file = $this->resourceFactory->getFileObject($reference['uid_local']);
            $file->getStorage()->deleteFile($file);
        }
    }

    protected function getQueryBuilderForTable(string $table): QueryBuilder
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $pool->getQueryBuilderForTable($table);
    }
}
