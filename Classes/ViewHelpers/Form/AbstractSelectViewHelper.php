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

use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\InvalidArgumentValueException;
use TYPO3Fluid\Fluid\Core\ViewHelper\MissingArgumentException;

class AbstractSelectViewHelper extends AbstractFormFieldViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'select';

    protected mixed $selectedValue;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
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
            'sortByOptionLabel',
            'boolean',
            'If true, List will be sorted by label.',
            false,
            false
        );
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
    }

    public function render(): string
    {
        if ($this->arguments['required']) {
            $this->tag->addAttribute('required', 'required');
        }
        $name = $this->getName();
        if ($this->arguments['multiple']) {
            $this->tag->addAttribute('multiple', 'multiple');
            $name .= '[]';
        }
        $this->tag->addAttribute('name', $name);
        // @extensionScannerIgnoreLine
        $options = $this->getOptions();

        $viewHelperVariableContainer = $this->renderingContext?->getViewHelperVariableContainer();
        if ($viewHelperVariableContainer === null) {
            return '';
        }

        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();
        $content = '';

        // register field name for token generation.
        $this->registerFieldNameForFormTokenGeneration($name);
        // in case it is a multi-select, we need to register the field name
        // as often as there are elements in the box
        if ($this->arguments['multiple']) {
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
        $childContent = is_string($childContent) ? $childContent : '';
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

    private function renderPrependOptionTag(): string
    {
        $output = '';
        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            $value = is_string($value) ? $value : '';
            $label = $this->arguments['prependOptionLabel'];
            $label = is_string($label) ? $label : '';
            $output .= $this->renderOptionTag($value, $label, false) . LF;
        }
        return $output;
    }

    /**
     * @param array<int|string, string> $options
     */
    private function renderOptionTags(array $options): string
    {
        $output = '';
        foreach ($options as $value => $label) {
            $isSelected = $this->isSelected($value);
            $output .= $this->renderOptionTag((string)$value, (string)$label, $isSelected) . LF;
        }
        return $output;
    }

    /**
     * @return array<int|string, string>
     */
    private function getOptions(): array
    {
        if (!is_array($this->arguments['options']) && !$this->arguments['options'] instanceof \Traversable) {
            return [];
        }
        $options = [];
        $optionsArgument = $this->arguments['options'];
        foreach ($optionsArgument as $key => $value) {
            if (!is_object($value) && !is_array($value)) {
                $options[$key] = $value;
                continue;
            }
            if (is_array($value)) {
                if (!$this->hasArgument('optionValueField')) {
                    throw new MissingArgumentException('Missing parameter "optionValueField" in SelectViewHelper for array value options.', 1682693720);
                }
                if (!$this->hasArgument('optionLabelField')) {
                    throw new MissingArgumentException('Missing parameter "optionLabelField" in SelectViewHelper for array value options.', 1682693721);
                }
                $optionValueField = is_string($this->arguments['optionValueField'])
                    ? $this->arguments['optionValueField'] : '';
                $optionLabelField = is_string($this->arguments['optionLabelField'])
                    ? $this->arguments['optionLabelField'] : '';
                $key = ObjectAccess::getPropertyPath($value, $optionValueField);
                $key = is_int($key) || is_string($key) ? $key : '';
                $value = ObjectAccess::getPropertyPath($value, $optionLabelField);
                $options[$key] = $value;
                continue;
            }
            if ($this->hasArgument('optionValueField')) {
                $optionValueField = is_string($this->arguments['optionValueField'])
                    ? $this->arguments['optionValueField'] : '';
                $key = ObjectAccess::getPropertyPath($value, $optionValueField);
                if (is_object($key)) {
                    if (method_exists($key, '__toString')) {
                        $key = (string)$key;
                    } else {
                        throw new InvalidArgumentValueException('Identifying value for object of class "' . get_debug_type($value) . '" was an object.', 1247827428);
                    }
                }
            } elseif (!$this->persistenceManager->isNewObject($value)) {
                $key = $this->persistenceManager->getIdentifierByObject($value);
            } elseif (method_exists($value, '__toString')) {
                $key = (string)$value;
            } else {
                throw new InvalidArgumentValueException('No identifying value for object of class "' . get_class($value) . '" found.', 1247826696);
            }
            if ($this->hasArgument('optionLabelField')) {
                $optionLabelField = is_string($this->arguments['optionLabelField'])
                    ? $this->arguments['optionLabelField'] : '';
                $value = ObjectAccess::getPropertyPath($value, $optionLabelField);
                if (is_object($value)) {
                    if (method_exists($value, '__toString')) {
                        $value = (string)$value;
                    } else {
                        throw new InvalidArgumentValueException('Label value for object of class "' . get_class($value) . '" was an object without a __toString() method.', 1247827553);
                    }
                }
            } elseif (method_exists($value, '__toString')) {
                $value = (string)$value;
            } elseif (!$this->persistenceManager->isNewObject($value)) {
                $value = $this->persistenceManager->getIdentifierByObject($value);
            }
            $key = is_int($key) || is_string($key) ? $key : '';
            $options[$key] = $value;
        }
        if ($this->arguments['sortByOptionLabel']) {
            asort($options, SORT_LOCALE_STRING);
        }
        return $options;
    }

    protected function isSelected(mixed $value): bool
    {
        $selectedValue = $this->getSelectedValue();
        if (
            $value === $selectedValue
            || (
                is_scalar($value)
                && (string)$value === $selectedValue
            )
        ) {
            return true;
        }
        if ($this->hasArgument('multiple')) {
            // selectAllByDefault means "selected if none was set before": it must only kick in
            // when no value is bound, so the empty($selectedValue) guard is load-bearing.
            if (empty($selectedValue) && $this->arguments['selectAllByDefault'] === true) {
                return true;
            }
            if (is_array($selectedValue) && in_array($value, $selectedValue)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array<int, string>|string
     */
    private function getSelectedValue(): array|string
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

    private function getOptionValueScalar(mixed $valueElement): string
    {
        if (is_object($valueElement)) {
            if ($this->hasArgument('optionValueField')) {
                $optionValueField = is_string($this->arguments['optionValueField'])
                    ? $this->arguments['optionValueField'] : '';
                $result = ObjectAccess::getPropertyPath($valueElement, $optionValueField);
                return is_string($result) ? $result : '';
            }
            if (!$this->persistenceManager->isNewObject($valueElement)) {
                if ($valueElement instanceof DomainObjectInterface) {
                    // We prefer to use the `getUid()` method because this returns the properly overlaid identifier (defaultLanguageRecordUid).
                    // Otherwise, an identifier would contain '[defaultLanguageRecordUid]_[localizedRecordUid]'. This in turn
                    // will not properly trigger the select option "is selected" comparison.
                    // @see AbstractFormFieldViewHelper->convertToPlainValue()
                    return $valueElement->getUid() ? $this->persistenceManager->getIdentifierByObject($valueElement) ?? '' : '';
                }
                return $this->persistenceManager->getIdentifierByObject($valueElement) ?? '';
            }
            if ($valueElement instanceof \BackedEnum) {
                return (string)$valueElement->value;
            }
            if ($valueElement instanceof \UnitEnum) {
                return $valueElement->name;
            }
        }
        return is_scalar($valueElement) ? (string)$valueElement : '';
    }

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
