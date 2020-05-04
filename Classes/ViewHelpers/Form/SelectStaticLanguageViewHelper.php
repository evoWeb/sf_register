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

/**
 * View helper to render a select box with values of static info tables country zones
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.selectStaticLanguage name="language" allowedLanguages="{0: 'de_DE', 1: 'fr_FR'}"/>
 * </code>
 */
class SelectStaticLanguageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    /**
     * @var \Evoweb\SfRegister\Domain\Repository\StaticLanguageRepository
     */
    protected $languageRepository;

    public function injectLanguageRepository(
        \Evoweb\SfRegister\Domain\Repository\StaticLanguageRepository $languageRepository
    ) {
        $this->languageRepository = $languageRepository;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->overrideArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', false, true);
        $this->registerArgument(
            'allowedLanguages',
            'array',
            'Array with languages allowed to be displayed.',
            false,
            []
        );
        $this->overrideArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'lgIso2'
        );
        $this->overrideArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'lgNameEn'
        );
    }

    public function initialize()
    {
        parent::initialize();

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
            if ($this->hasArgument('allowedLanguages') && count($this->arguments['allowedLanguages'])) {
                $options = $this->languageRepository->findByLgCollateLocale($this->arguments['allowedLanguages']);
            } else {
                $options = $this->languageRepository->findAll();
            }
            $options = $options->toArray();

            if ($this->hasArgument('disabled')) {
                $value = $this->getSelectedValue();
                $value = is_array($value) ? $value : [$value];

                $options = array_filter($options, function ($option) use ($value) {
                    /** @var \Evoweb\SfRegister\Domain\Model\StaticLanguage $option */
                    return in_array($option->getLgIso2(), $value);
                });
            }

            $this->arguments['options'] = $options;
        }
    }
}
