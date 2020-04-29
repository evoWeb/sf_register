<?php

namespace Evoweb\SfRegister\ViewHelpers\Link;

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

/**
 * Link Action view helper that automatically
 * adds a "hash" argument on the "user" and "action" arguments
 *
 * @package Evoweb\SfRegister\Property
 */
class ActionViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Link\ActionViewHelper
{
    /**
     * Render method
     *
     * @return string Rendered link
     */
    public function render()
    {
        if (
            $this->arguments['action'] !== null
            && $this->arguments['arguments'] !== null
            && isset($this->arguments['arguments']['user'])
        ) {
            $this->arguments['arguments']['hash'] = \TYPO3\CMS\Core\Utility\GeneralUtility::hmac(
                $this->arguments['action'] . '::' . $this->arguments['arguments']['user']
            );
        }

        return parent::render();
    }
}
