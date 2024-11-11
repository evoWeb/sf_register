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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * View helper to render a selectbox with labels that need to be translated
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.translatedSelect name="language" options="{0: 'label_key1', 1: 'label_key2'}"/>
 * </code>
 */
class TranslatedSelectViewHelper extends AbstractSelectViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.'
        );
        $this->registerArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.'
        );
        $this->registerArgument('extensionName', 'string', 'Extension from which labels should be taken');
    }

    /**
     * @param array<int|string, string> $options
     * @return string
     */
    protected function renderOptionTags(array $options): string
    {
        $extensionName = $this->hasArgument('extensionName') ? $this->arguments['extensionName'] : null;
        $extensionName = $extensionName === null ? $this->getRequest()->getControllerExtensionName() : $extensionName;

        $output = '';
        foreach ($options as $value => $label) {
            $label = htmlspecialchars(LocalizationUtility::translate((string)$label, $extensionName));

            $isSelected = $this->isSelected($value);
            $output .= $this->renderOptionTag((string)$value, $label, $isSelected) . LF;
        }
        return $output;
    }
}
