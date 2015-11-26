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
 * Class JmRecaptchaAdapter
 *
 * @package Evoweb\SfRegister\Services\Captcha
 */
class JmRecaptchaAdapter extends AbstractAdapter
{
    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * Captcha object
     *
     * @var tx_jmrecaptcha
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
     * Rendering the output of the captcha
     *
     * @return string
     */
    public function render()
    {
        $this->objectManager->get(\Evoweb\SfRegister\Services\Session::class)
            ->remove('captchaWasValidPreviously');

        if ($this->captcha !== null) {
            /** @noinspection PhpUndefinedMethodInspection */
            $output = $this->captcha->getReCaptcha($this->settings['error']);
        } else {
            $output = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'error_captcha.notinstalled',
                'SfRegister',
                array('jm_recaptcha')
            );
        }

        return $output;
    }

    /**
     * Validate the captcha value from the request and output an error if not valid
     *
     * @param string $value
     *
     * @return bool
     */
    public function isValid($value)
    {
        $validCaptcha = true;

        $session = $this->objectManager->get(\Evoweb\SfRegister\Services\Session::class);
        $captchaWasValidPreviously = $session->get('captchaWasValidPreviously');
        if ($this->captcha !== null && $captchaWasValidPreviously !== true) {
            $_POST['recaptcha_response_field'] = $value;
            /** @noinspection PhpUndefinedMethodInspection */
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
