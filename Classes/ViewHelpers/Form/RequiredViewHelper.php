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

namespace Evoweb\SfRegister\ViewHelpers\Form;

use Evoweb\SfRegister\Validation\Validator\RequiredValidator;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
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
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var RenderingContext|null
     */
    protected ?RenderingContextInterface $renderingContext = null;

    public function __construct(protected ConfigurationManager $configurationManager) {}

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('fieldName', 'string', 'Name of the field to render', true);
    }

    public function render(): ?string
    {
        if ($this->classVerdict($this->arguments)) {
            $result = $this->renderThenChild() ?? null;
            return is_string($result) || is_null($result) ? $result : null;
        }
        $result = $this->renderElseChild() ?? null;
        return is_string($result) || is_null($result) ? $result : null;
    }

    /**
     * @return array<string, mixed>
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
        } catch (\Exception) {
            $settings = [];
        }
        return $settings;
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public function classVerdict(array $arguments): bool
    {
        $settings = $this->getSettings();

        $controllerName = $this->renderingContext?->getControllerName() ?? '';
        $mode = str_replace('feuser', '', strtolower($controllerName));
        $validation = is_array($settings['validation'] ?? null) ? $settings['validation'] : [];
        $controllerSettings = is_array($validation[$mode] ?? null) ? $validation[$mode] : [];

        $fieldName = $arguments['fieldName'];
        $fieldSettings = is_string($fieldName) ? ($controllerSettings[$fieldName] ?? false) : false;

        $result = false;
        if (
            $fieldSettings === RequiredValidator::class
            || $fieldSettings === '"' . RequiredValidator::class . '"'
            || (
                is_array($fieldSettings)
                && (
                    in_array(RequiredValidator::class, $fieldSettings)
                    || in_array('"' . RequiredValidator::class . '"', $fieldSettings)
                )
            )
        ) {
            $result = true;
        }

        return $result;
    }
}
