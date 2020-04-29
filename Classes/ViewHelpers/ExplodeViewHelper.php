<?php

namespace Evoweb\SfRegister\ViewHelpers;

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
