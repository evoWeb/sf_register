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

use Evoweb\SfRegister\Domain\Model\StaticLanguage;
use Evoweb\SfRegister\Domain\Repository\StaticLanguageRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * View helper to render a select box with values of static info tables country zones
 *
 * <code title="Usage">
 *  {namespace register=Evoweb\SfRegister\ViewHelpers}
 *  <register:form.selectStaticLanguage name="language" allowedLanguages="{0: 'de_DE', 1: 'fr_FR'}"/>
 * </code>
 */
class SelectStaticLanguageViewHelper extends AbstractSelectViewHelper
{
    protected ?StaticLanguageRepository $languageRepository = null;

    public function injectLanguageRepository(StaticLanguageRepository $languageRepository): void
    {
        $this->languageRepository = $languageRepository;
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.',
            false,
            'lgIso2'
        );
        $this->registerArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.',
            false,
            'lgNameEn'
        );
        $this->registerArgument(
            'allowedLanguages',
            'array',
            'Array with languages allowed to be displayed.',
            false,
            []
        );
    }

    public function initialize()
    {
        parent::initialize();

        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            return;
        }

        if (count($this->arguments['allowedLanguages'])) {
            $options = $this->languageRepository->findByLgCollateLocale($this->arguments['allowedLanguages']);
        } else {
            $options = $this->languageRepository->findAll();
        }
        $options = $options->toArray();

        if ($this->arguments['disabled']) {
            $value = (array)$this->getSelectedValue();

            /** @var StaticLanguage $option */
            $options = array_filter($options, function ($option) use ($value) {
                return in_array($option->getLgIso2(), $value);
            });
        }

        $this->arguments['options'] = $options;
    }
}
