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

namespace Evoweb\SfRegister\Validation\Validator;

use Evoweb\SfRegister\Services\Captcha\CaptchaAdapterFactory;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

class CaptchaValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    protected $supportedOptions = [
        'type' => [
            'srfreecap',
            'Captcha adapter to be used',
            'string',
        ],
    ];

    public function __construct(protected CaptchaAdapterFactory $captchaAdapterFactory) {}

    /**
     * If the given captcha is valid
     */
    public function isValid(mixed $value): void
    {
        $captchaAdapter = $this->captchaAdapterFactory->getCaptchaAdapter($this->options['type']);
        if (!$captchaAdapter->isValid($value)) {
            foreach ($captchaAdapter->getErrors() as $error) {
                $this->result->addError($error);
            }
        }
    }
}
