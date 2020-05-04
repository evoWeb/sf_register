<?php

namespace Evoweb\SfRegister\ViewHelpers;

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

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

class RecordsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * ViewHelper returns HTML, thus we need to disable output escaping
     *
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initializes the arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('table', 'string', 'the table for the record icon', true);
        $this->registerArgument('uids', 'string', 'list of uids', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $table = $arguments['table'];
        $uids = is_array($arguments['uids']) ? $arguments['uids'] : GeneralUtility::intExplode(',', $arguments['uids']);

        return self::getRecordsFromTable($table, $uids);
    }

    protected static function getRecordsFromTable($table, $uids): array
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        try {
            return $queryBuilder
                ->select('*')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->in(
                        'uid',
                        $queryBuilder->createNamedParameter($uids, Connection::PARAM_INT_ARRAY)
                    )
                )
                ->orderBy('uid')
                ->execute()
                ->fetchAll();
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Database query failed. Error was: ' . $e->getPrevious()->getMessage(),
                1511950673
            );
        }
    }
}
