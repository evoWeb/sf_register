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

use Evoweb\SfRegister\Domain\Model\StaticCountry;
use Evoweb\SfRegister\Domain\Repository\StaticCountryRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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
class SelectStaticCountriesViewHelper extends AbstractSelectViewHelper
{
    protected ?StaticCountryRepository $countryRepository = null;

    public function injectCountryRepository(StaticCountryRepository $countryRepository): void
    {
        $this->countryRepository = $countryRepository;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'cnIso2'
        );
        $this->registerArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'cnOfficialNameEn'
        );
        $this->registerArgument(
            'allowedCountries',
            'array',
            'Array with countries allowed to be displayed.',
            false,
            []
        );
    }

    public function initialize(): void
    {
        parent::initialize();

        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            return;
        }

        if (count($this->arguments['allowedCountries'])) {
            $options = $this->countryRepository->findByCnIso2($this->arguments['allowedCountries']);
        } else {
            $options = $this->countryRepository->findAll();
        }
        $options = $options->toArray();

        if ($this->arguments['disabled']) {
            $value = (array)$this->getSelectedValue();

            /** @var StaticCountry $option */
            $options = array_filter($options, function ($option) use ($value) {
                return in_array($option->getCnIso2(), $value);
            });
        }

        $this->arguments['options'] = $options;
    }
}
