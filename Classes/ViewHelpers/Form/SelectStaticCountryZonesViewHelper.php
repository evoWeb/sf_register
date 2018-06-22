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
 * View helper to render a select box with values of static info tables country zones
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.selectStaticCountryZones name="zone" parent="US"/>
 * </code>
 */
class SelectStaticCountryZonesViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    /**
     * @var \Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository
     */
    protected $countryZonesRepository;

    public function injectCountryZonesRepository(
        \Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository $countryZonesRepository
    ) {
        $this->countryZonesRepository = $countryZonesRepository;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('parent', 'string', 'Parent of this zone');
        $this->overrideArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', false, true);
        $this->overrideArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'uid'
        );
        $this->overrideArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'zn_name_local'
        );
    }

    public function initialize()
    {
        parent::initialize();

        if ($this->hasArgument('parent') && $this->arguments['parent'] != ''
            && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('static_info_tables')
        ) {
            $options = $this->countryZonesRepository->findAllByIso2($this->arguments['parent']);
            $options = $options->fetchAll();

            if ($this->hasArgument('disabled')) {
                $value = $this->getSelectedValue();
                $value = is_array($value) ? $value : [$value];

                $options = array_filter($options, function ($option) use ($value) {
                    /** @var \Evoweb\SfRegister\Domain\Model\StaticCountryZone $option */
                    return in_array($option['uid'], $value);
                });
            }

            $this->arguments['options'] = $options;
        }
    }
}
