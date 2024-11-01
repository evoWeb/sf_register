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

namespace Evoweb\SfRegister\ViewHelpers;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RecordsViewHelper extends AbstractViewHelper
{
    public function __construct(protected ConnectionPool $connectionPool)
    {
    }

    /**
     * ViewHelper returns HTML, thus we need to disable output escaping
     *
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', 'the table for the record icon', true);
        $this->registerArgument('uids', 'string', 'list of uids', true);
    }

    public function render(): array
    {
        $table = $this->arguments['table'];
        $uids = is_array($this->arguments['uids'])
            ? $this->arguments['uids']
            : GeneralUtility::intExplode(',', $this->arguments['uids']);

        return $this->getRecordsFromTable($table, $uids);
    }

    protected function getRecordsFromTable(string $table, array $uids): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder
            ->getRestrictions()
                ->removeAll()
                    ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $result = $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($uids, ArrayParameterType::INTEGER)
                )
            )
            ->orderBy('uid')
            ->executeQuery();
        try {
            return $result->fetchAllAssociative();
        } catch (\Exception|Exception $exception) {
            throw new \RuntimeException(
                'Database query failed. Error was: ' . $exception->getPrevious()->getMessage(),
                1511950673
            );
        }
    }
}
