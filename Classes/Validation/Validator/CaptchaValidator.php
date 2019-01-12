<?php
namespace Evoweb\SfRegister\Validation\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
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

use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

class CaptchaValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator implements ValidatorInterface
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
     *
     * @return bool
     */
    public function isValid($value): bool
    {
        $result = true;

        $captchaAdapter = $this->captchaAdapterFactory->getCaptchaAdapter($this->options['type']);
        if (!$captchaAdapter->isValid($value)) {
            $result = false;
            foreach ($captchaAdapter->getErrors() as $error) {
                $this->result->addError($error);
            }
        }

        return $result;
    }
}
