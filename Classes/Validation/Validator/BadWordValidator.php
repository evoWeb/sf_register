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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class BadWordValidator extends AbstractValidator implements InjectableInterface
{
    protected ?ConfigurationManager $configurationManager = null;

    /**
     * @var array
     */
    protected array $settings = [];

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
    }

    /**
     * If the given password is valid in kind of not on the bad list
     *
     * @param string $value The value
     */
    public function isValid($value)
    {
        $badWordItems = GeneralUtility::trimExplode(',', $this->settings['badWordList']);

        if (in_array(strtolower($value), $badWordItems)) {
            $this->addError(
                $this->translateErrorMessage('error_badword', 'SfRegister', $this->options),
                1301599720
            );
        }
    }
}
