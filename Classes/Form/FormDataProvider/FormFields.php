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
        foreach ($result['processedTca']['columns'] as $fieldName => $fieldConfig) {
            if (!isset($fieldConfig['config']['sfRegisterForm'])) {
                continue;
            }

            $result['processedTca']['columns'][$fieldName] = $this->getAvailableFields($fieldConfig);

            $currentDatabaseValuesArray = $this->processDatabaseFieldValue($result['databaseRow'], $fieldName);
            if (empty($currentDatabaseValuesArray) && !($fieldConfig['config']['doNotPreSelect'] ?? false)) {
                $result['databaseRow'][$fieldName] = $this->getSelectedFields($fieldConfig['config']['sfRegisterForm']);
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return array<string, mixed>
     */
    protected function getAvailableFields(array $fieldConfig): array
    {
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
        $tsConfig = $this->getBackendUserAuthentication()->getTSConfig();
        $pluginConfiguration = $tsConfig['plugin.']['tx_sfregister.'] ?? [];
        return $pluginConfiguration['settings.']['fields.']['configuration.'] ?? [];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function getDefaultSelectedFieldsFromTsConfig(): array
    {
        $tsConfig = $this->getBackendUserAuthentication()->getTSConfig();
        $pluginConfiguration = $tsConfig['plugin.']['tx_sfregister.'] ?? [];
        return $pluginConfiguration['settings.']['fields.']['defaultSelected.'] ?? [];
    }

    /**
     * @param array<string, mixed>|string $configuration
     */
    protected function getLabel(string $fieldName, array|string $configuration): string
    {
        $labelPath = $configuration['backendLabel']
            ?? 'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.' . $fieldName;
        return $this->getLanguageService()->sL($labelPath);
    }

    protected function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
