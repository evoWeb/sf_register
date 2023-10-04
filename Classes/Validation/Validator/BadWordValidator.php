<?php

namespace Evoweb\SfRegister\Validation\Validator;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class BadWordValidator extends AbstractValidator implements SetOptionsInterface
{
    protected array $settings = [];

    public function __construct(ConfigurationManagerInterface $configurationManager)
    {
        $this->settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
    }

    /**
     * If the given value is not on the bad word list
     */
    public function isValid(mixed $value): void
    {
        $badWordItems = GeneralUtility::trimExplode(',', $this->settings['badWordList'] ?? '');

        if (in_array(strtolower($value), $badWordItems)) {
            $this->addError(
                $this->translateErrorMessage('error_badword', 'SfRegister', $this->options),
                1301599720
            );
        }
    }
}
