<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Form\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class FormFields extends AbstractItemProvider implements FormDataProviderInterface
{
    /**
     * Resolve select items
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public function addData(array $result): array
    {
        if (!isset($result['databaseRow']) || !is_array($result['databaseRow'])) {
            $result['databaseRow'] = [];
        }

        $processedTca = is_array($result['processedTca'] ?? null) ? $result['processedTca'] : [];
        $columns = is_array($processedTca['columns'] ?? null) ? $processedTca['columns'] : [];

        foreach ($columns as $fieldName => $fieldConfig) {
            $fieldName = (string)$fieldName;
            if (!is_array($fieldConfig)) {
                continue;
            }
            $config = $fieldConfig['config'] ?? null;
            if (!is_array($config) || !isset($config['sfRegisterForm'])) {
                continue;
            }

            /** @var array<string, mixed> $fieldConfig */
            $columns[$fieldName] = $this->getAvailableFields($fieldConfig);

            $currentDatabaseValuesArray = $this->processDatabaseFieldValue($result['databaseRow'], $fieldName);
            if (empty($currentDatabaseValuesArray) && !($config['doNotPreSelect'] ?? false)) {
                $sfRegisterForm = $config['sfRegisterForm'];
                $result['databaseRow'][$fieldName] = $this->getSelectedFields(
                    is_string($sfRegisterForm) ? $sfRegisterForm : ''
                );
            }
        }

        $processedTca['columns'] = $columns;
        $result['processedTca'] = $processedTca;

        return $result;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return array<string, mixed>
     */
    protected function getAvailableFields(array $fieldConfig): array
    {
        if (!isset($fieldConfig['config']) || !is_array($fieldConfig['config'])) {
            $fieldConfig['config'] = [];
        }
        $items = [];
        $configuredFields = $this->getAvailableFieldsFromTsConfig();
        foreach ($configuredFields as $fieldName => $configuration) {
            if ($configuration) {
                $fieldName = rtrim($fieldName, '.');
                $label = $this->getLabel($fieldName, $configuration);
                $items[] = ['label' => $label, 'value' => $fieldName];
            }
        }
        $fieldConfig['config']['items'] = $items;

        return $fieldConfig;
    }

    /**
     * @return array<int, string>
     */
    protected function getSelectedFields(string $formType): array
    {
        return $this->getDefaultSelectedFieldsFromTsConfig()[$formType . '.'] ?? [];
    }

    /**
     * @return array<string, string>
     */
    protected function getAvailableFieldsFromTsConfig(): array
    {
        $tsConfig = $this->getBackendUserAuthentication()?->getTSConfig() ?? [];
        $pluginConfiguration = $tsConfig['plugin.']['tx_sfregister.'] ?? [];
        return $pluginConfiguration['settings.']['fields.']['configuration.'] ?? [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function getDefaultSelectedFieldsFromTsConfig(): array
    {
        $tsConfig = $this->getBackendUserAuthentication()?->getTSConfig() ?? [];
        $pluginConfiguration = $tsConfig['plugin.']['tx_sfregister.'] ?? [];
        return $pluginConfiguration['settings.']['fields.']['defaultSelected.'] ?? [];
    }

    /**
     * @param array<string, mixed>|string $configuration
     */
    protected function getLabel(string $fieldName, array|string $configuration): string
    {
        $labelPath = $configuration['backendLabel'] ?? 'sf_register.be:fe_users.' . $fieldName;
        $labelPath = is_string($labelPath) ? $labelPath : 'sf_register.be:fe_users.' . $fieldName;
        return $this->getLanguageService()->sL($labelPath);
    }

    protected function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] instanceof BackendUserAuthentication ? $GLOBALS['BE_USER'] : null;
    }
}
