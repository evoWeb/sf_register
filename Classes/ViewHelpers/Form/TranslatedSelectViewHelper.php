<?php
namespace Evoweb\SfRegister\ViewHelpers\Form;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017 Sebastian Fischer <typo3@evoweb.de>
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

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * View helper to render a selectbox with labels that need to be translated
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.translatedSelect name="language" options="{0: 'label_key1', 1: 'label_key2'}"/>
 * </code>
 */
class TranslatedSelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerTagAttribute('extensionName', 'string', 'Extension from which labels should be taken');
    }

    /**
     * Render one option tag
     *
     * @param string $value value attribute of the option tag (will be escaped)
     * @param string $label content of the option tag (will be escaped)
     * @param bool $isSelected specifies whether or not to add selected attribute
     *
     * @return string the rendered option tag
     */
    protected function renderOptionTag($value, $label, $isSelected): string
    {
        $extensionName = $this->hasArgument('extensionName') ? $this->arguments['extensionName'] : null;
        $request = $this->getRequest();
        $extensionName = $extensionName === null ? $request->getControllerExtensionName() : $extensionName;

        $label = htmlspecialchars(LocalizationUtility::translate($label, $extensionName));
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($isSelected) {
            $output .= ' selected="selected"';
        }
        $output .= '>' . $label . '</option>';
        return $output;
    }
}
