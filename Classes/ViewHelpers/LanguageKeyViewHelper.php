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

use Doctrine\DBAL\Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper to output the configured language
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:languageKey type="languages"/>
 *  {register:languageKey(type: 'countries')}
 * </code>
 */
class LanguageKeyViewHelper extends AbstractViewHelper
{
    public function __construct(protected ConnectionPool $connectionPool)
    {
    }

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'type',
            'string',
            'Purpose of this view helper. If it should check for certain static info tables or not',
        );
    }

    public function render(): string
    {
        $languageCode = $this->getLanguageCode();
        $type = $this->getConfiguredType();

        if ($languageCode !== '' && $type !== '') {
            if ($type == 'zones') {
                $languageCode = $this->hasTableColumn('static_country_zones', 'zn_name_' . $languageCode)
                    ? $languageCode
                    : '';
            } elseif ($type == 'languages') {
                $languageCode = $this->hasTableColumn('static_languages', 'lg_name_' . $languageCode)
                    ? $languageCode
                    : '';
            }
        }

        return ucfirst(strtolower($languageCode) ?: 'en');
    }

    protected function getLanguageCode(): string
    {
        $languageCode = '';
        if (ApplicationType::fromRequest($this->getRequest())->isFrontend()) {
            $language = $this->getRequest()->getAttribute('language');
            if ($language instanceof SiteLanguage && trim($language->getLocale()->getLanguageCode())) {
                $languageCode = trim($language->getLocale()->getLanguageCode());
            }
        } elseif ($this->getBackendUserAuthentication()->uc['lang'] != '') {
            $languageCode = $this->getBackendUserAuthentication()->uc['lang'];
        }
        return $languageCode;
    }

    protected function getConfiguredType(): string
    {
        $type = $this->arguments['type'] ?? '';

        return in_array($type, ['countries', 'languages', 'zones']) ? $type : '';
    }

    protected function hasTableColumn(string $tableName, string $columnName): bool
    {
        try {
            $columns = $this
                ->connectionPool
                ->getConnectionForTable($tableName)
                ->createSchemaManager()
                ->listTableColumns($tableName);
        } catch (Exception) {
            $columns = [];
        }

        $result = false;
        foreach ($columns as $column) {
            if ($column->getName() == $columnName) {
                $result = true;
            }
        }

        return $result;
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
