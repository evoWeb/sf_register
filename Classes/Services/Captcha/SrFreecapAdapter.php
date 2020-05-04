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

/**
 * = Examples =
 * <code title="Single alias">
 * <f:alias map="{x: '{register:form.captcha(type: \'freecap\')}'}">
 *    <f:format.html>{x.image}</f:format.html>
 *    <f:format.html>{x.notice}</f:format.html>
 *    <f:format.html>{x.cantRead}</f:format.html>
 *    <f:format.html>{x.accessible}</f:format.html>
 * </f:alias>
 * </code>
 * <output>
 * <p class="bodytext">
 *    <img class="tx-srfreecap-pi2-image" id="tx_srfreecap_pi2_captcha_image_50a3f"
 *        src="http://dev45.dev.mobil/index.php?eID=sr_freecap_captcha&amp;id=185"
 *        alt="CAPTCHA image for SPAM prevention "/>
 * </p>
 * <p class="bodytext">Please enter here the word as displayed in the picture.
 *        This is to prevent spamming.</p>
 * <p class="bodytext">
 *    <span class="tx-srfreecap-pi2-cant-read">If you can't read the word,
 *        <a href="#" onclick="
 *            this.blur();
 *            newFreeCap(
 *                '50a3f',
 *                'Sorry, we cannot autoreload a new image. ' +
 *                    'Submit the form and a new image will be loaded.'
 *            );
 *            return false;
 *        ">click here</a>.
 *    </span>
 * </p>
 * </output>
 */
class SrFreecapAdapter extends AbstractAdapter
{
    /**
     * @var \tx_srfreecap_pi2
     */
    protected $captcha;

    /**
     * Keys to be used as variables output
     *
     * @var array
     */
    protected $keys = [
        'image',
        'notice',
        'cantRead',
        'accessible',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('sr_freecap')) {
            /** @noinspection PhpIncludeInspection */
            require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sr_freecap') .
                'pi2/class.tx_srfreecap_pi2.php');
            $this->captcha = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_srfreecap_pi2');
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
            $values = array_values($this->captcha->makeCaptcha());
            $output = array_combine($this->keys, $values);
        } else {
            $output = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'error_captcha_notinstalled',
                'SfRegister',
                ['sr_freecap']
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
            if (!$this->captcha->checkWord($value)) {
                $validCaptcha = false;
                $this->addError(
                    \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                        'error_captcha_notcorrect',
                        'SfRegister'
                    ),
                    1306910429
                );
            }
        }

        $session->set('captchaWasValidPreviously', $validCaptcha);

        return $validCaptcha;
    }
}
