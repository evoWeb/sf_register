<?php
namespace Evoweb\SfRegister\Validation\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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

use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * A password validator
 *
 * @scope singleton
 */
class BadWordValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator implements ValidatorInterface
{
    /**
     * Configuration Manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Inject of the configuration manager
     *
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * If the given password is valid in kind of not on the bad list
     *
     * @param string $value The value
     *
     * @return boolean
     */
    public function isValid($value)
    {
        $result = true;

        $badWordItems = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['badWordList']);

        if (in_array(strtolower($value), $badWordItems)) {
            $this->addError(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'error_badword',
                    'SfRegister',
                    $this->options
                ),
                1301599720
            );
            $result = false;
        }

        return $result;
    }
}
