<?php

namespace Evoweb\SfRegister\ViewHelpers\Form;

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

use Evoweb\SfRegister\Services\Captcha\CaptchaAdapterFactory;
use TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper;

/**
 * View helper to output a captcha in a form
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.captcha type="jmrecaptcha"/>
 * </code>
 */
class CaptchaViewHelper extends AbstractFormFieldViewHelper
{
    protected ?CaptchaAdapterFactory $captchaAdapterFactory = null;

    public function injectCaptchaAdapterFactory(CaptchaAdapterFactory $captchaAdapterFactory)
    {
        $this->captchaAdapterFactory = $captchaAdapterFactory;
    }

    public function initializeArguments(): void
    {
        $this->registerUniversalTagAttributes();
        $this->registerArgument('type', 'string', 'Captcha type', true);
    }

    /**
     * @return string|array
     */
    public function render()
    {
        $type = $this->arguments['type'];
        return $this->captchaAdapterFactory->getCaptchaAdapter($type)->render();
    }
}
