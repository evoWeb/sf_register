<?php
namespace Evoweb\SfRegister\ViewHelpers;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
 * (c) 2011-15 Justin Kromlinger
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
 * Viewhelper to output the configured language
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:languageKey type="languages"/>
 *  {register:languageKey(type: 'countries')}
 * </code>
 */
class LanguageKeyViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * Initialize arguments.
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'type',
            'string',
            'Purpose of this viewhelper. If it shoud check for certain static info tables or not'
        );
    }

    /**
     * @return string
     */
    public function render()
    {
        $languageCode = '';
        if (TYPO3_MODE === 'FE') {
            if (isset($this->getTypoScriptFrontendController()->lang)) {
                $languageCode = strtolower($this->getTypoScriptFrontendController()->lang);
            }
        } elseif (strlen($this->getBackendUserAuthentication()->uc['lang']) > 0) {
            $languageCode = $this->getBackendUserAuthentication()->uc['lang'];
        }

        if ($languageCode && $this->hasArgument('type') && ($type = $this->getConfiguredType())) {
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

        return strtoupper($languageCode) ?: 'EN';
    }

    /**
     * @return string
     */
    protected function getConfiguredType(): string
    {
        $type = $this->arguments['type'];

        return in_array($type, ['countries', 'languages', 'zones']) ? $type : '';
    }

    /**
     * @param string $tableName
     * @param string $columnName
     *
     * @return bool
     */
    protected function hasTableColumn($tableName, $columnName): bool
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


    /**
     * @param string $tableName
     *
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    protected function getConnection($tableName): \TYPO3\CMS\Core\Database\Connection
    {
        return \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Database\ConnectionPool::class
        )->getConnectionForTable($tableName);
    }

    /**
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }
}
