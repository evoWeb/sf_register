<?php
namespace Evoweb\SfRegister\ViewHelpers\Form;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
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
 * Viewhelper to render a selectbox with values of static info tables country zones
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.selectStaticLanguage name="language" allowedLanguages="{0: 'de_DE', 1: 'fr_FR'}"/>
 * </code>
 */
class SelectStaticLanguageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    /**
     * Repository that provides the language models
     *
     * @var \Evoweb\SfRegister\Domain\Repository\StaticLanguageRepository
     * @inject
     */
    protected $languageRepository;


    /**
     * Initialize arguments. Cant be moved to parent because
     * of "private $argumentDefinitions = [];"
     *
     * @return void
     */
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

    /**
     * Override the initialize method to load all
     * available languages before rendering
     *
     * @return void
     */
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
