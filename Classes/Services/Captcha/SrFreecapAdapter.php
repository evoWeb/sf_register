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
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * Captcha object
     *
     * @var tx_srfreecap_pi2
     */
    protected $captcha = null;

    /**
     * Keys to be used as variables output
     *
     * @var array
     */
    protected $keys = array(
        'image',
        'notice',
        'cantRead',
        'accessible',
    );

    /**
     * Constuctor
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
            $values = array_values($this->captcha->makeCaptcha());
            $output = array_combine($this->keys, $values);
        } else {
            $output = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'error_captcha_notinstalled',
                'SfRegister',
                array('sr_freecap')
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
            /** @noinspection PhpUndefinedMethodInspection */
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
