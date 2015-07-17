<?php
namespace Evoweb\SfRegister\ViewHelpers\Form;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Viewhelper to output a captcha in a form
 * <code title="Usage">
 * {namespace register=\\Evoweb\\SfRegister\\ViewHelpers}
 * <register:form.captcha type="jmrecaptcha"/>
 * </code>
 */
class CaptchaViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    /**
     * Factory to create a captcha that is used to render the output
     *
     * @var \Evoweb\SfRegister\Services\Captcha\CaptchaAdapterFactory
     * @inject
     */
    protected $captchaAdapterFactory;


    /**
     * Initialize arguments.
     *
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerUniversalTagAttributes();
    }

    /**
     * Render the captcha block
     *
     * @param string $type Type of captcha to use
     *
     * @return string
     */
    public function render($type)
    {
        return $this->captchaAdapterFactory->getCaptchaAdapter($type)->render();
    }
}
