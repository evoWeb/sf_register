<?php

namespace Evoweb\SfRegister\ViewHelpers;

/*
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
 * (c) 2011-2015 Justin Kromlinger
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
 */

/**
 * View helper to output the configured language
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:languageKey type="languages"/>
 *  {register:languageKey(type: 'countries')}
 * </code>
 */
class LanguageKeyViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument(
            'type',
            'string',
            'Purpose of this view helper. If it should check for certain static info tables or not'
        );
    }

    public function render(): string
    {
        $languageCode = '';
        if (TYPO3_MODE === 'FE') {
            $languageCode = $this->getTypoScriptFrontendController()->config['config']['language'] ?: 'default';
        } elseif ($this->getBackendUserAuthentication()->uc['lang'] != '') {
            $languageCode = $this->getBackendUserAuthentication()->uc['lang'];
        }

        $type = $this->getConfiguredType();

        if ($languageCode != '' && $type != '') {
            if ($type == 'countries') {
                $languageCode = $this->hasTableColumn('static_countries', 'cn_short_' . $languageCode) ?
                    $languageCode :
                    '';
            } elseif ($type == 'zones') {
                $languageCode = $this->hasTableColumn('static_country_zones', 'zn_name_' . $languageCode) ?
                    $languageCode :
                    '';
            } elseif ($type == 'languages') {
                $languageCode = $this->hasTableColumn('static_languages', 'lg_name_' . $languageCode) ?
                    $languageCode :
                    '';
            }
        }

        return ucfirst(strtolower($languageCode) ?: 'en');
    }

    protected function getConfiguredType(): string
    {
        $type = isset($this->arguments['type']) ? $this->arguments['type'] : '';

        return in_array($type, ['countries', 'languages', 'zones']) ? $type : '';
    }

    protected function hasTableColumn(string $tableName, string $columnName): bool
    {
        $columns = $this
            ->getConnection($tableName)
            ->getSchemaManager()
            ->listTableColumns($tableName);

        $result = false;
        foreach ($columns as $column) {
            if ($column->getName() == $columnName) {
                $result = true;
            }
        }

        return $result;
    }


    protected function getConnection(string $tableName): \TYPO3\CMS\Core\Database\Connection
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getConnectionForTable($tableName);
    }

    protected function getTypoScriptFrontendController(): \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    protected function getBackendUserAuthentication(): \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
