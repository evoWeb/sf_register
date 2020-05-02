<?php

namespace Evoweb\SfRegister\Form\FormDataProvider;

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

use TYPO3\CMS\Backend\Form\FormDataProvider\AbstractItemProvider;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class FormFields extends AbstractItemProvider implements FormDataProviderInterface
{
    /**
     * Resolve select items
     *
     * @param array $result
     * @return array
     * @throws \UnexpectedValueException
     */
    public function addData(array $result)
    {
        foreach ($result['processedTca']['columns'] as $fieldName => $fieldConfig) {
            if (!isset($fieldConfig['config']['sfRegisterForm'])) {
                continue;
            }

            $result['processedTca']['columns'][$fieldName] = $this->getAvailableFields($fieldConfig);

            $currentDatabaseValuesArray = $this->processDatabaseFieldValue($result['databaseRow'], $fieldName);
            if (empty($currentDatabaseValuesArray)) {
                $result['databaseRow'][$fieldName] = $this->getSelectedFields($fieldConfig['config']['sfRegisterForm']);
            }
        }

        return $result;
    }

    protected function getAvailableFields(array $fieldConfig): array
    {
        $items = [];
        $configuredFields = $this->getAvailableFieldsFromTsConfig();
        foreach ($configuredFields as $fieldName => $configuration) {
            $fieldName = rtrim($fieldName, '.');
            $label = $this->getLabel($fieldName, $configuration);
            $items[] = [$label, $fieldName];
        }
        $fieldConfig['config']['items'] = $items;

        return $fieldConfig;
    }

    protected function getSelectedFields($formType): array
    {
        return $this->getDefaultSelectedFieldsFromTsConfig()[$formType . '.'] ?? [];
    }

    protected function getAvailableFieldsFromTsConfig(): array
    {
        $tsConfig = $this->getBackendUserAuthentication()->getTSConfig();
        $pluginConfiguration = $tsConfig['plugin.']['tx_sfregister.'] ?? [];
        return $pluginConfiguration['settings.']['fields.']['configuration.'] ?? [];
    }

    protected function getDefaultSelectedFieldsFromTsConfig(): array
    {
        $tsConfig = $this->getBackendUserAuthentication()->getTSConfig();
        $pluginConfiguration = $tsConfig['plugin.']['tx_sfregister.'] ?? [];
        return $pluginConfiguration['settings.']['fields.']['defaultSelected.'] ?? [];
    }

    protected function getLabel(string $fieldName, array $configuration): string
    {
        $labelPath = isset($configuration['backendLabel']) ?
            $configuration['backendLabel'] :
            'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.' . $fieldName;
        return $this->getLanguageService()->sL($labelPath);
    }

    protected function getBackendUserAuthentication(): \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
