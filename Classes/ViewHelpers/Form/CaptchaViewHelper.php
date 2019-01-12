<?php
namespace Evoweb\SfRegister\ViewHelpers\Form;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
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
 * View helper to output a captcha in a form
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.captcha type="jmrecaptcha"/>
 * </code>
 */
class CaptchaViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    /**
     * @var \Evoweb\SfRegister\Services\Captcha\CaptchaAdapterFactory
     */
    protected $captchaAdapterFactory;

    public function injectCaptchaAdapterFactory(
        \Evoweb\SfRegister\Services\Captcha\CaptchaAdapterFactory $captchaAdapterFactory
    ) {
        $this->captchaAdapterFactory = $captchaAdapterFactory;
    }

    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
        $this->registerArgument('type', 'string', 'Captcha type', true);
    }

    public function render(): string
    {
        $type = $this->arguments['type'];
        return $this->captchaAdapterFactory->getCaptchaAdapter($type)->render();
    }
}
