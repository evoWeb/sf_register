<?php
namespace Evoweb\SfRegister\ViewHelpers\Form;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
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
 * View helper to render a select box with values
 * in given steps from start to end value
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.required fieldName="'username"/>
 * </code>
 */
class RequiredViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $frameworkConfiguration = [];

    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
        $this->frameworkConfiguration = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
    }

    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerArgument('fieldName', 'string', 'Name of the field to render', true);
    }

    public function render(): string
    {
        $fieldName = $this->arguments['fieldName'];
        if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) < 10000000) {
            // @todo remove once TYPO3 9.5.x support is dropped
            $mode = str_replace(
                'feuser',
                '',
                strtolower(key($this->frameworkConfiguration['controllerConfiguration']))
            );
        } else {
            $mode = str_replace(
                ['evoweb\sfregister\controller\feuser', 'controller'],
                '',
                strtolower(key($this->frameworkConfiguration['controllerConfiguration']))
            );
        }
        $modeSettings = $this->settings['validation'][$mode];
        $fieldSettings = isset($modeSettings[$fieldName]) ? $modeSettings[$fieldName] : false;

        $result = '';
        if ((
                is_array($fieldSettings)
                && (
                    in_array(\Evoweb\SfRegister\Validation\Validator\RequiredValidator::class, $fieldSettings)
                    || in_array('"Evoweb.SfRegister:Required"', $fieldSettings)
                )
            )
            || (
                is_string($fieldSettings)
                && (
                    $fieldSettings === \Evoweb\SfRegister\Validation\Validator\RequiredValidator::class
                    || $fieldSettings === '"Evoweb.SfRegister:Required"'
                )
            )
        ) {
            $result = $this->renderChildren();
        }

        return $result;
    }
}
