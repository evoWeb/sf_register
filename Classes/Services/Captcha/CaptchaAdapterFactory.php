<?php
namespace Evoweb\SfRegister\Services\Captcha;

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

/**
 * Factory to build a captcha
 */
class CaptchaAdapterFactory
{
    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * Configuration manager
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
     * Inject configuration manager
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
     * Get an adapter for an captcha of given type
     *
     * @param string $type
     *
     * @return \Evoweb\SfRegister\Services\Captcha\AbstractAdapter
     */
    public function getCaptchaAdapter($type)
    {
        $settings = [];

        if (array_key_exists($type, $this->settings['captcha'])) {
            $settings = is_array($this->settings['captcha'][$type]) ? $this->settings['captcha'][$type] : [];

            $type = is_array($this->settings['captcha'][$type]) ?
                $this->settings['captcha'][$type]['_typoScriptNodeValue'] :
                $this->settings['captcha'][$type];
        } elseif (strpos($type, '_') === false) {
            $type = 'Evoweb\\SfRegister\\Services\\Captcha\\' . ucfirst(strtolower($type)) . 'Adapter';
        }

        /** @var $captchaAdapter \Evoweb\SfRegister\Services\Captcha\AbstractAdapter */
        $captchaAdapter = $this->objectManager->get($type);
        $captchaAdapter->setSettings($settings);

        return $captchaAdapter;
    }
}
