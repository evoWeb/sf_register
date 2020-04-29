<?php

namespace Evoweb\SfRegister\Validation\Validator;

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

class CaptchaValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var \Evoweb\SfRegister\Services\Captcha\CaptchaAdapterFactory
     */
    protected $captchaAdapterFactory;

    /**
     * @var array
     */
    protected $supportedOptions = [
        'type' => [
            'srfreecap',
            'Captcha adapter to be used',
            'string'
        ],
    ];

    public function injectCaptchaAdapterFactory(
        \Evoweb\SfRegister\Services\Captcha\CaptchaAdapterFactory $captchaAdapterFactory
    ) {
        $this->captchaAdapterFactory = $captchaAdapterFactory;
    }

    /**
     * If the given captcha is valid
     *
     * @param string $value
     */
    public function isValid($value)
    {
        $captchaAdapter = $this->captchaAdapterFactory->getCaptchaAdapter($this->options['type']);
        if (!$captchaAdapter->isValid($value)) {
            foreach ($captchaAdapter->getErrors() as $error) {
                $this->result->addError($error);
            }
        }
    }
}
