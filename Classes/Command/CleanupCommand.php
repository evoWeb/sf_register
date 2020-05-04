<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\Command;

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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CleanupCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    public function __construct(ResourceFactory $resourceFactory = null)
    {
        $this->resourceFactory = $resourceFactory;
        parent::__construct(null);
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this
            ->setAliases(['sfregister:cleanup'])
            ->setDescription('Cleanup user that did not finish the double opt in, and remove orphan images')
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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $inactiveGroups = GeneralUtility::intExplode(',', $input->getArgument('inactiveGroups'));
        if (empty($inactiveGroups)) {
            $io->comment('List of group marking inactive users may not be empty to prevent unwanted behaviour!');
            return;
        }

        $days = (int)$input->getArgument('days');

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
    }

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
                    $queryBuilder->createNamedParameter($inactiveUserGroup, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->lte(
                    'crdate',
                    $queryBuilder->createNamedParameter(time() - (3600 * 24 * $days), \PDO::PARAM_INT)
                )
            )
            ->execute();

        return $result->fetchAll();
    }

    protected function removeUser(array $user)
    {
        $table = 'fe_users';
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $queryBuilder->getConnection()->delete($table, ['uid' => $user['uid']]);
    }

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
                    $queryBuilder->createNamedParameter($user['uid'], \PDO::PARAM_INT)
                )
            )
            ->execute();

        return $result->fetchAll();
    }

    protected function removeReference(array $user)
    {
        $table = 'sys_file_reference';
        $queryBuilder = $this->getQueryBuilderForTable($table);
        $queryBuilder->getConnection()->delete($table, [
            'uid_foreign' => $user['uid'],
            'tablenames' => $user['fe_users'],
            'fieldname' => $user['image'],
        ]);
    }

    protected function removeImage(array $references)
    {
        foreach ($references as $reference) {
            $file = $this->resourceFactory->getFileObject($reference['uid_local']);
            $file->getStorage()->deleteFile($file);
        }
    }

    protected function getQueryBuilderForTable(string $table): \TYPO3\CMS\Core\Database\Query\QueryBuilder
    {
        /** @var \TYPO3\CMS\Core\Database\ConnectionPool $pool */
        $pool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        );
        return $pool->getQueryBuilderForTable($table);
    }
}
