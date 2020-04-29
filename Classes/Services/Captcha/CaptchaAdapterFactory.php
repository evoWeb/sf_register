<?php

namespace Evoweb\SfRegister\Services\Captcha;

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

/**
 * Factory to build a captcha
 */
class CaptchaAdapterFactory
{
    /**
     * @var array
     */
    protected $settings = [];

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->settings = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
    }

    public function getCaptchaAdapter(string $type): AbstractAdapter
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

        /** @var \Evoweb\SfRegister\Services\Captcha\AbstractAdapter $captchaAdapter */
        $captchaAdapter = GeneralUtility::getContainer()->get($type);
        $captchaAdapter->setSettings($settings);

        return $captchaAdapter;
    }
}
