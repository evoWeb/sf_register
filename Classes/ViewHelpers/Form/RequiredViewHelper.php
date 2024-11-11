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

namespace Evoweb\SfRegister\ViewHelpers\Form;

use Evoweb\SfRegister\Validation\Validator\RequiredValidator;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * View helper to render content based if a field is configured as required
 *
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.required fieldName="username"><f:then>*</f:then></register:form.required>
 * </code>
 */
class RequiredViewHelper extends AbstractConditionViewHelper
{
    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * @var array<string, mixed>
     */
    protected array $frameworkConfiguration = [];

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    public function __construct(protected ConfigurationManager $configurationManager)
    {
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('fieldName', 'string', 'Name of the field to render', true);
    }

    public function render()
    {
        if ($this->classVerdict($this->arguments)) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function getSettings(): array
    {
        try {
            /** @var array<string, mixed> $settings */
            $settings = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'SfRegister',
                'Form'
            );
            /** @var array<string, mixed> $frameworkConfiguration */
            $frameworkConfiguration = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
            );
        } catch (\Exception) {
            $settings = [];
            $frameworkConfiguration = [];
        }
        return [$settings, $frameworkConfiguration];
    }

    public function classVerdict(array $arguments): bool
    {
        [$settings, $frameworkConfiguration] = $this->getSettings();

        $mode = str_replace(
            ['evoweb\\sfregister\\controller\\feuser', 'controller'],
            '',
            strtolower(key($frameworkConfiguration['controllerConfiguration'] ?? ''))
        );
        $controllerSettings = $settings['validation'][$mode] ?? [];

        $fieldName = $arguments['fieldName'];
        $fieldSettings = $controllerSettings[$fieldName] ?? false;

        $result = false;
        if (
            $fieldSettings === RequiredValidator::class
            || $fieldSettings === '"Evoweb.SfRegister:Required"'
            || (
                is_array($fieldSettings)
                && (
                    in_array(RequiredValidator::class, $fieldSettings)
                    || in_array('"Evoweb.SfRegister:Required"', $fieldSettings)
                )
            )
        ) {
            $result = true;
        }

        return $result;
    }
}
