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

use Evoweb\SfRegister\Services\Session;
use SJBR\SrFreecap\PiBaseApi;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
 *
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
 *                'Sorry, we cannot auto reload a new image. Submit the form and a new image will be loaded.'
 *            );
 *            return false;
 *        ">click here</a>.
 *    </span>
 * </p>
 * </output>
 */
class SrFreecapAdapter extends AbstractAdapter
{
    protected ?object $captchaService = null;

    /**
     * Keys to be used as variables output
     */
    protected array $keys = [
        'image',
        'notice',
        'cantRead',
        'accessible',
    ];

    public function __construct(protected Session $session)
    {
        if (ExtensionManagementUtility::isLoaded('sr_freecap')) {
            $this->captchaService = GeneralUtility::makeInstance(PiBaseApi::class);
        }
    }

    public function render(): array|string
    {
        $this->session->remove('captchaWasValid');

        if ($this->captchaService !== null) {
            $values = array_values($this->captchaService->makeCaptcha());
            $output = array_combine($this->keys, $values);
        } else {
            $output = LocalizationUtility::translate(
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

        if ($this->captchaService !== null && $this->session->get('captchaWasValid') !== true) {
            if (!$this->captchaService->checkWord($value)) {
                $validCaptcha = false;
                $this->addError(
                    LocalizationUtility::translate(
                        'error_captcha_notcorrect',
                        'SfRegister'
                    ),
                    1306910429
                );
            }

            $this->session->set('captchaWasValid', $validCaptcha);
        }

        return $validCaptcha;
    }
}
