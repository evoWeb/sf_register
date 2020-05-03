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
 * View helper to render a select box with values of static info tables countries
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.selectStaticCountries name="country" optionLabelField="cnShortDe"/>
 * </code>
 * <code title="Optional label field">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.selectStaticCountries name="country" optionLabelField="cnShortDe"/>
 * </code>
 */
class SelectStaticCountriesViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    /**
     * @var \Evoweb\SfRegister\Domain\Repository\StaticCountryRepository
     */
    protected $countryRepository;

    public function injectCountryRepository(
        \Evoweb\SfRegister\Domain\Repository\StaticCountryRepository $countryRepository
    ) {
        $this->countryRepository = $countryRepository;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->overrideArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', false, true);
        $this->registerArgument(
            'allowedCountries',
            'array',
            'Array with countries allowed to be displayed.',
            false,
            []
        );
        $this->overrideArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'cnIso2'
        );
        $this->overrideArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'cnOfficialNameEn'
        );
    }

    public function initialize()
    {
        parent::initialize();

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')) {
            if ($this->hasArgument('allowedCountries') && count($this->arguments['allowedCountries'])) {
                $options = $this->countryRepository->findByCnIso2($this->arguments['allowedCountries']);
            } else {
                $options = $this->countryRepository->findAll();
            }
            $options = $options->toArray();

            if ($this->hasArgument('disabled')) {
                $value = $this->getSelectedValue();
                $value = is_array($value) ? $value : [$value];

                $options = array_filter($options, function ($option) use ($value) {
                    /** @var \Evoweb\SfRegister\Domain\Model\StaticCountry $option */
                    return in_array($option->getCnIso2(), $value);
                });
            }

            $this->arguments['options'] = $options;
        }
    }
}
