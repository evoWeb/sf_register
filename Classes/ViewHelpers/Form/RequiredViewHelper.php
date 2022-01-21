<?php

declare(strict_types=1);

namespace Evoweb\SfRegister\ViewHelpers\Form;

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

use Evoweb\SfRegister\Validation\Validator\RequiredValidator;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * View helper to render a select box with values
 * in given steps from start to end value
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.required fieldName="'username"/>
 * </code>
 */
class RequiredViewHelper extends AbstractFormFieldViewHelper
{
    protected array $settings = [];

    protected array $frameworkConfiguration = [];

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
        $this->frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
    }

    public function initializeArguments(): void
    {
        $this->registerUniversalTagAttributes();
        $this->registerArgument('fieldName', 'string', 'Name of the field to render', true);
    }

    public function render(): string
    {
        $fieldName = $this->arguments['fieldName'];
        $mode = str_replace(
            ['evoweb\\sfregister\\controller\\feuser', 'controller'],
            '',
            strtolower(key($this->frameworkConfiguration['controllerConfiguration']))
        );
        $modeSettings = $this->settings['validation'][$mode];
        $fieldSettings = isset($modeSettings[$fieldName]) ? $modeSettings[$fieldName] : false;

        $result = '';
        if (
            (
                is_array($fieldSettings)
                && (
                    in_array(RequiredValidator::class, $fieldSettings)
                    || in_array('"Evoweb.SfRegister:Required"', $fieldSettings)
                )
            )
            || (
                is_string($fieldSettings)
                && (
                    $fieldSettings === RequiredValidator::class
                    || $fieldSettings === '"Evoweb.SfRegister:Required"'
                )
            )
        ) {
            $result = $this->renderChildren();
        }

        return $result;
    }
}
