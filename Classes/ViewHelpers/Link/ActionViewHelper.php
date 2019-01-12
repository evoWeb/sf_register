<?php
namespace Evoweb\SfRegister\ViewHelpers\Link;

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
        if ($this->arguments['action'] !== null
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
