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

use Evoweb\SfRegister\Domain\Model\StaticCountryZone;
use Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * View helper to render a select box with values of static info tables country zones
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.selectStaticCountryZones name="zone" parent="US"/>
 * </code>
 */
class SelectStaticCountryZonesViewHelper extends AbstractSelectViewHelper
{
    protected ?StaticCountryZoneRepository $countryZonesRepository = null;

    public function injectCountryZonesRepository(StaticCountryZoneRepository $countryZonesRepository): void
    {
        $this->countryZonesRepository = $countryZonesRepository;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'uid'
        );
        $this->registerArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'zn_name_local'
        );
        $this->registerArgument('parent', 'string', 'Parent of this zone');
    }

    public function initialize()
    {
        parent::initialize();

        if (
            $this->arguments['parent'] === null
            || !ExtensionManagementUtility::isLoaded('static_info_tables')
        ) {
            return;
        }

        $options = $this->countryZonesRepository->findAllByIso2($this->arguments['parent']);
        try {
            $options = $options->fetchAllAssociative();

            if ($this->arguments['disabled']) {
                $value = (array)$this->getSelectedValue();

                /** @var StaticCountryZone $option */
                $options = array_filter($options, function ($option) use ($value) {
                    return in_array($option['uid'], $value);
                });
            }

            $this->arguments['options'] = $options;
        } catch (\Exception) {
        }
    }
}
