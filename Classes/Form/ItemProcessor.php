<?php

namespace Evoweb\SfRegister\Form;

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

class ItemProcessor
{
    public function getConfiguredFields(array &$parameters)
    {
        $items =& $parameters['items'];

        $configuredFields = $this->getConfiguredFieldsFromTsConfig();
        foreach ($configuredFields as $fieldName => $configuration) {
            $fieldName = rtrim($fieldName, '.');
            $label = $this->getLabel($fieldName, $configuration);
            $items[] = [$label, $fieldName];
        }
    }

    protected function getLabel(string $fieldName, array $configuration): string
    {
        return isset($configuration['backendLabel']) ?
            $configuration['backendLabel'] :
            'LLL:EXT:sf_register/Resources/Private/Language/locallang_be.xlf:fe_users.' . $fieldName;
    }

    protected function getConfiguredFieldsFromTsConfig(): array
    {
        $tsConfig = $this->getBackendUserAuthentication()->getTSConfig();
        $pluginConfiguration = $tsConfig['plugin.']['tx_sfregister.'] ?? [];
        return $pluginConfiguration['settings.']['fields.']['configuration.'] ?? [];
    }

    protected function getBackendUserAuthentication(): \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
