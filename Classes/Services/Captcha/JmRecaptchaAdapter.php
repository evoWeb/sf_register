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

// @todo remove support
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JmRecaptchaAdapter extends AbstractAdapter
{
    /**
     * Captcha object
     *
     * @var \tx_jmrecaptcha
     */
    protected $captcha = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('jm_recaptcha')) {
            /** @noinspection PhpIncludeInspection */
            require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('jm_recaptcha')
                . 'class.tx_jmrecaptcha.php');
            $this->captcha = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_jmrecaptcha');
        }
    }

    /**
     * @return array|string
     */
    public function render()
    {
        /** @var \Evoweb\SfRegister\Services\Session $session */
        $session = GeneralUtility::makeInstance(\Evoweb\SfRegister\Services\Session::class);
        $session->remove('captchaWasValidPreviously');

        if ($this->captcha !== null) {
            $output = $this->captcha->getReCaptcha($this->settings['error']);
        } else {
            $output = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'error_captcha.notinstalled',
                'SfRegister',
                ['jm_recaptcha']
            );
        }

        return $output;
    }

    public function isValid(string $value): bool
    {
        $validCaptcha = true;

        /** @var \Evoweb\SfRegister\Services\Session $session */
        $session = GeneralUtility::makeInstance(\Evoweb\SfRegister\Services\Session::class);
        $captchaWasValidPreviously = $session->get('captchaWasValidPreviously');
        if ($this->captcha !== null && $captchaWasValidPreviously !== true) {
            $_POST['recaptcha_response_field'] = $value;
            $status = $this->captcha->validateReCaptcha();

            if ($status == false || $status['error'] !== null) {
                $validCaptcha = false;
                $this->addError(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'error_jmrecaptcha' . (isset($status['error']) ? '_' . $status['error'] : ''),
                        'SfRegister'
                    ),
                    1307421960
                );
            }
        }

        $session->set('captchaWasValidPreviously', $validCaptcha);

        return $validCaptcha;
    }
}
