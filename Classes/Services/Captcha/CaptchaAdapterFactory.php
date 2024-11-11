<?php

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

namespace Evoweb\SfRegister\Services\Captcha;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Factory to build a captcha
 */
class CaptchaAdapterFactory
{
    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    public function __construct(ConfigurationManager $configurationManager)
    {
        try {
            $this->settings = $configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'SfRegister',
                'Form'
            );
        } catch (\Exception) {
        }
    }

    public function getCaptchaAdapter(string $type): AbstractAdapter
    {
        $settings = [];

        if (array_key_exists($type, $this->settings['captcha'] ?? [])) {
            $settings = is_array($this->settings['captcha'][$type] ?? null) ? $this->settings['captcha'][$type] : [];

            $type = is_array($this->settings['captcha'][$type])
                ? $this->settings['captcha'][$type]['_typoScriptNodeValue']
                : $this->settings['captcha'][$type];
        } elseif (!str_contains($type, '_')) {
            $type = 'Evoweb\\SfRegister\\Services\\Captcha\\' . ucfirst(strtolower($type)) . 'Adapter';
        }

        /** @var class-string<object> $type */
        /** @var AbstractAdapter $captchaAdapter */
        $captchaAdapter = GeneralUtility::makeInstance($type);
        $captchaAdapter->setSettings($settings);

        return $captchaAdapter;
    }
}
