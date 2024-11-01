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

namespace Evoweb\SfRegister\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ExplodeViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'String to be exploded', false, '');
        $this->registerArgument('delimiter', 'string', 'Character to explode by', false, ',');
    }

    public function render(): array
    {
        $string = $this->arguments['string'] !== '' ? $this->arguments['string'] : $this->renderChildren();
        $delimiter = $this->arguments['delimiter'];

        return GeneralUtility::trimExplode($delimiter, $string);
    }
}
