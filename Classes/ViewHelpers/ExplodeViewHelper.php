<?php
namespace Evoweb\SfRegister\ViewHelpers;

/***************************************************************
 * Copyright notice
 *
 * (c) 2019 Sebastian Fischer <typo3@evoweb.de>
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

class ExplodeViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    use \TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('string', 'string', 'String to be exploded', false, '');
        $this->registerArgument('delimiter', 'string', 'Character to explode by', false, ',');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     *
     * @return array
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
    ) {
        $string = $arguments['string'] !== '' ? $arguments['string'] : $renderChildrenClosure();
        $delimiter = $arguments['delimiter'];

        return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode($delimiter, $string);
    }
}
