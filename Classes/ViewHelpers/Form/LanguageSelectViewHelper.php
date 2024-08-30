<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Evoweb\SfRegister\ViewHelpers\Form;

use TYPO3\CMS\Core\Site\SiteLanguagePresets;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Renders a :html:`<select>` tag with all available languages as options.
 *
 * Examples
 * ========
 *
 * Basic usage
 * -----------
 *
 * ::
 *
 *    <f:form.languageSelect name="language" value="{defaultLanguage}" />
 *
 * Output::
 *
 *    <select name="language">
 *      <option value="de_DE">German</option>
 *      <option value="en_US">English</option>
 *      ....
 *    </select>
 *
 * Prioritize languages
 * --------------------
 *
 * Define a list of languages which should be listed as first options in the
 * form element::
 *
 *    <f:form.languageSelect
 *      name="language"
 *      value="de_DE"
 *      prioritizedLanguages="{0: 'de_DE', 1: 'en_US', 2: 'es_ES'}"
 *    />
 *
 *  Additionally, Austria is pre-selected.
 *
 * Display another language
 * ------------------------
 *
 * A combination of optionLabelField and alternativeLanguage is possible. For
 * instance, if you want to show the localized official names but not in your
 * default language but in English. You can achieve this by using the following
 * combination::
 *
 *    <f:form.languageSelect
 *      name="language"
 *      optionLabelField="title"
 *      alternativeLanguage="en"
 *      sortByOptionLabel="true"
 *    />
 *
 * Bind an object
 * --------------
 *
 * You can also use the "property" attribute if you have bound an object to the form.
 * See :ref:`<f:form> <typo3-fluid-form>` for more documentation.
 */
class LanguageSelectViewHelper extends AbstractFormFieldViewHelper
{
    protected const LABEL_FILE = 'EXT:core/Resources/Private/Language/Iso/countries.xlf';

    /**
     * @var string
     */
    protected $tagName = 'select';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('excludeLanguages', 'array', 'Array with language codes that should not be shown.', false, []);
        $this->registerArgument('onlyLanguages', 'array', 'If set, only the language codes in the list are rendered.', false, []);
        $this->registerArgument('optionLabelField', 'string', 'If specified, will call the appropriate getter on each object to determine the label. Use "title", "navigationTitle", or "locale"', false, 'title');
        $this->registerArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', false, false);
        $this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this ViewHelper', false, 'f3-form-error');
        $this->registerArgument('prependOptionLabel', 'string', 'If specified, will provide an option at first position with the specified label.');
        $this->registerArgument('prependOptionValue', 'string', 'If specified, will provide an option at first position with the specified value.');
        $this->registerArgument('multiple', 'boolean', 'If set multiple options may be selected.', false, false);
        $this->registerArgument('required', 'boolean', 'If set no empty value is allowed.', false, false);
        $this->registerArgument('prioritizedLanguages', 'array', 'A list of language codes which should be listed on top of the list.', false, []);
        $this->registerArgument('alternativeLanguage', 'string', 'If specified, the country list will be shown in the given language.');
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
        $this->addAdditionalIdentityPropertiesIfNeeded();
        $this->setErrorClassAttribute();
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->setRespectSubmittedDataValue(true);

        $this->tag->addAttribute('name', $name);

        $validLanguages = $this->getLanguageList();
        $options = $this->createOptions($validLanguages);
        $selectedValue = $this->getValueAttribute();

        $tagContent = $this->renderPrependOptionTag();
        foreach ($options as $value => $label) {
            $tagContent .= $this->renderOptionTag($value, $label, $value === $selectedValue);
        }

        $this->tag->forceClosingTag(true);
        $this->tag->setContent($tagContent);
        return $this->tag->render();
    }

    /**
     * @param array[] $languages
     * @return array<string, string>
     */
    protected function createOptions(array $languages): array
    {
        $options = [];
        foreach ($languages as $code => $language) {
            $options[$code] = match ($this->arguments['optionLabelField']) {
                'locale' => $this->translate($language['locale']),
                'navigationTitle' => $language['navigationTitle'],
                'title' => $language['title'],
                default => throw new Exception(
                    'Argument "optionLabelField" of <f:form.languageSelect> must either be set to "title",
                    "navigationTitle", or "locale".',
                    1674076708
                ),
            };
        }
        if ($this->arguments['sortByOptionLabel']) {
            asort($options, SORT_LOCALE_STRING);
        } else {
            ksort($options, SORT_NATURAL);
        }
        if (($this->arguments['prioritizedLanguages'] ?? []) !== []) {
            $finalOptions = [];
            foreach ($this->arguments['prioritizedLanguages'] as $languageCode) {
                if (isset($options[$languageCode])) {
                    $label = $options[$languageCode];
                    $finalOptions[$languageCode] = $label;
                    unset($options[$languageCode]);
                }
            }
            foreach ($options as $languageCode => $label) {
                $finalOptions[$languageCode] = $label;
            }
            $options = $finalOptions;
        }
        return $options;
    }

    protected function translate(string $label): string
    {
        if (!str_starts_with($label, 'LLL')) {
            $label = 'LLL:' . self::LABEL_FILE . ':' . $label . '.name';
        }
        if ($this->arguments['alternativeLanguage']) {
            return (string)LocalizationUtility::translate($label, languageKey: $this->arguments['alternativeLanguage']);
        }
        return (string)LocalizationUtility::translate($label);
    }

    /**
     * Render prepended option tag
     */
    protected function renderPrependOptionTag(): string
    {
        if ($this->hasArgument('prependOptionLabel')) {
            $value = $this->hasArgument('prependOptionValue') ? $this->arguments['prependOptionValue'] : '';
            $label = $this->arguments['prependOptionLabel'];
            return $this->renderOptionTag((string)$value, (string)$label, false) . LF;
        }
        return '';
    }

    /**
     * Render one option tag
     *
     * @param string $value value attribute of the option tag (will be escaped)
     * @param string $label content of the option tag (will be escaped)
     * @param bool $isSelected specifies whether to add selected attribute
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

    /**
     * @return array[]
     */
    protected function getLanguageList(): array
    {
        $languages = (new SiteLanguagePresets())->getAll();
        if (!empty($this->arguments['excludeLanguages'] ?? [])) {
            $possibleLanguages = [];
            foreach ($languages as $language) {
                if (!in_array($language['locale'], $this->arguments['excludeLanguages'])) {
                    $possibleLanguages[$language['locale']] = $language;
                }
            }
            $languages = $possibleLanguages;
        }
        if (!empty($this->arguments['onlyLanguages'] ?? [])) {
            $possibleLanguages = [];
            foreach ($languages as $language) {
                if (in_array($language['locale'], $this->arguments['onlyLanguages'])) {
                    $possibleLanguages[$language['locale']] = $language;
                }
            }
            $languages = $possibleLanguages;
        }
        return $languages;
    }
}
