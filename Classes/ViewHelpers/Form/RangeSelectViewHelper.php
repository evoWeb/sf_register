<?php

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

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * View helper to render a select box with values
 * in given steps from start to end value
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.rangeSelect property="day" start="1" end="31"/>
 * </code>
 */
class RangeSelectViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'select';

    /**
     * @var mixed
     */
    protected $selectedValue;

    /**
     * Value to start range with
     *
     * @var int
     */
    protected int $start = 0;

    /**
     * Value to end range with
     *
     * @var int
     */
    protected int $end = PHP_INT_MAX;

    /**
     * Step to increase value of each option
     *
     * @var int
     */
    protected int $step = 1;

    /**
     * In case of a value lower then 10 and digits
     * defined as 2 the label get prepended with a 0
     *
     * @var int
     */
    protected int $digits = 2;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('size', 'string', 'Size of input field');
        $this->registerTagAttribute(
            'disabled',
            'string',
            'Specifies that the input element should be disabled when the page loads'
        );
        $this->registerArgument(
            'options',
            'array',
            'Associative array with internal IDs as key, and the values are displayed in the select box.
             Can be combined with or replaced by child f:form.select.* nodes.'
        );
        $this->registerArgument(
            'optionsAfterContent',
            'boolean',
            'If true, places auto-generated option tags after those rendered in the tag content. If false,
             automatic options come first.',
            false,
            false
        );
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
        $this->registerArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', false, false);
        $this->registerArgument(
            'selectAllByDefault',
            'boolean',
            'If specified options are selected if none was set before.',
            false,
            false
        );
        $this->registerArgument(
            'errorClass',
            'string',
            'CSS class to set if there are errors for this ViewHelper',
            false,
            'f3-form-error'
        );
        $this->registerArgument(
            'prependOptionLabel',
            'string',
            'If specified, will provide an option at first position with the specified label.'
        );
        $this->registerArgument(
            'prependOptionValue',
            'string',
            'If specified, will provide an option at first position with the specified value.'
        );
        $this->registerArgument('multiple', 'boolean', 'If set multiple options may be selected.', false, false);
        $this->registerArgument('required', 'boolean', 'If set no empty value is allowed.', false, false);
        $this->registerArgument(
            'start',
            'int',
            'start',
            true
        );
        $this->registerArgument(
            'end',
            'int',
            'end',
            true
        );
        $this->registerArgument(
            'step',
            'int',
            'step',
            false,
            1
        );
        $this->registerArgument(
            'digits',
            'int',
            'digits',
            false,
            2
        );
    }

    public function render(): string
    {
        $this->start = (int)$this->arguments['start'];
        $this->end = (int)$this->arguments['end'];
        $this->step = (int)$this->arguments['step'];
        $this->digits = (int)$this->arguments['digits'];

        if (isset($this->arguments['required']) && $this->arguments['required']) {
            $this->tag->addAttribute('required', 'required');
        }
        $name = $this->getName();
        if (isset($this->arguments['multiple']) && $this->arguments['multiple']) {
            $this->tag->addAttribute('multiple', 'multiple');
            $name .= '[]';
        }
        $this->tag->addAttribute('name', $name);
        $options = $this->getOptions();

        $viewHelperVariableContainer = $this->renderingContext->getViewHelperVariableContainer();

        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();
        $content = '';

        // register field name for token generation.
        $this->registerFieldNameForFormTokenGeneration($name);
        // in case it is a multi-select, we need to register the field name
        // as often as there are elements in the box
        if (isset($this->arguments['multiple']) && $this->arguments['multiple']) {
            $content .= $this->renderHiddenFieldForEmptyValue();
            // Register the field name additional times as required by the total number of
            // options. Since we already registered it once above, we start the counter at 1
            // instead of 0.
            $optionsCount = count($options);
            for ($i = 1; $i < $optionsCount; $i++) {
                $this->registerFieldNameForFormTokenGeneration($name);
            }
            // save the parent field name so that any child f:form.select.option
            // tag will know to call registerFieldNameForFormTokenGeneration
            // this is the reason why "self::class" is used instead of static::class (no LSB)
            $viewHelperVariableContainer->addOrUpdate(
                self::class,
                'registerFieldNameForFormTokenGeneration',
                $name
            );
        }

        $viewHelperVariableContainer->addOrUpdate(self::class, 'selectedValue', $this->getSelectedValue());
        $prependContent = $this->renderPrependOptionTag();
        $tagContent = $this->renderOptionTags($options);
        $childContent = $this->renderChildren();
        $viewHelperVariableContainer->remove(self::class, 'selectedValue');
        $viewHelperVariableContainer->remove(self::class, 'registerFieldNameForFormTokenGeneration');
        if (isset($this->arguments['optionsAfterContent']) && $this->arguments['optionsAfterContent']) {
            $tagContent = $childContent . $tagContent;
        } else {
            $tagContent .= $childContent;
        }
        $tagContent = $prependContent . $tagContent;

        $this->tag->forceClosingTag(true);
        $this->tag->setContent($tagContent);
        $content .= $this->tag->render();
        return $content;
    }

    /**
     * Render prepended option tag
     *
     * @return string rendered prepended empty option
     */
    protected function renderPrependOptionTag(): string
    {
        $output = '';
        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            $label = $this->arguments['prependOptionLabel'];
            $output .= $this->renderOptionTag((string)$value, (string)$label, false) . LF;
        }
        return $output;
    }

    /**
     * Render the option tags.
     *
     * @param array $options the options for the form.
     * @return string rendered tags.
     */
    protected function renderOptionTags(array $options): string
    {
        $output = '';
        foreach ($options as $value => $label) {
            $isSelected = $this->isSelected($value);
            $output .= $this->renderOptionTag((string)$value, (string)$label, $isSelected) . LF;
        }
        return $output;
    }

    protected function getOptions(): array
    {
        $options = [];

        for ($current = $this->start; $current <= $this->end;) {
            $options[$current] = sprintf('%0' . $this->digits . 's', $current);

            $current += $this->step;
            if ($current > $this->end) {
                break;
            }
        }

        return $options;
    }

    /**
     * Render the option tags.
     *
     * @param mixed $value Value to check for
     * @return bool TRUE if the value should be marked a s selected; FALSE otherwise
     */
    protected function isSelected($value): bool
    {
        $selectedValue = $this->getSelectedValue();
        if ($value === $selectedValue || (string)$value === $selectedValue) {
            return true;
        }
        if ($this->hasArgument('multiple')) {
            if ($selectedValue === null && $this->arguments['selectAllByDefault'] === true) {
                return true;
            }
            if (is_array($selectedValue) && in_array($value, $selectedValue)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieves the selected value(s)
     *
     * @return mixed value string or an array of strings
     */
    protected function getSelectedValue()
    {
        $this->setRespectSubmittedDataValue(true);
        $value = $this->getValueAttribute();
        if (!is_array($value) && !$value instanceof \Traversable) {
            return $this->getOptionValueScalar($value);
        }
        $selectedValues = [];
        foreach ($value as $selectedValueElement) {
            $selectedValues[] = $this->getOptionValueScalar($selectedValueElement);
        }
        return $selectedValues;
    }

    /**
     * Get the option value for an object
     *
     * @param mixed $valueElement
     * @return string
     */
    protected function getOptionValueScalar($valueElement)
    {
        if (is_object($valueElement)) {
            if ($this->hasArgument('optionValueField')) {
                return ObjectAccess::getPropertyPath($valueElement, $this->arguments['optionValueField']);
            }
            // @todo use $this->persistenceManager->isNewObject() once it is implemented
            if ($this->persistenceManager->getIdentifierByObject($valueElement) !== null) {
                return $this->persistenceManager->getIdentifierByObject($valueElement);
            }
            return (string)$valueElement;
        }
        return $valueElement;
    }

    /**
     * Render one option tag
     *
     * @param string $value value attribute of the option tag (will be escaped)
     * @param string $label content of the option tag (will be escaped)
     * @param bool $isSelected specifies whether or not to add selected attribute
     * @return string the rendered option tag
     */
    protected function renderOptionTag(string $value, string $label, bool $isSelected): string
    {
        $output = '<option value="' . htmlspecialchars($value) . '"';
        if ($isSelected) {
            $output .= ' selected="selected"';
        }
        $output .= '>' . htmlspecialchars($label) . '</option>';
        return $output;
    }
}
