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

use Evoweb\SfRegister\Services\Session;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class JmRecaptchaAdapter extends AbstractAdapter
{
    protected ?object $captcha = null;

    public function __construct()
    {
        if (ExtensionManagementUtility::isLoaded('jm_recaptcha')) {
            /** @noinspection */
            require_once(ExtensionManagementUtility::extPath('jm_recaptcha')
                . 'class.tx_jmrecaptcha.php');
            $this->captcha = GeneralUtility::makeInstance('tx_jmrecaptcha');
        }
    }

    /**
     * @return array|string
     */
    public function render()
    {
        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class);
        $session->remove('captchaWasValidPreviously');

        if ($this->captcha !== null) {
            $output = $this->captcha->getReCaptcha($this->settings['error']);
        } else {
            $output = LocalizationUtility::translate(
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

        /** @var Session $session */
        $session = GeneralUtility::makeInstance(Session::class);
        $captchaWasValidPreviously = $session->get('captchaWasValidPreviously');
        if ($this->captcha !== null && $captchaWasValidPreviously !== true) {
            $_POST['recaptcha_response_field'] = $value;
            $status = $this->captcha->validateReCaptcha();

            if ($status == false || $status['error'] !== null) {
                $validCaptcha = false;
                $this->addError(
                    LocalizationUtility::translate(
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
