<?php

declare(strict_types=1);

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

namespace Evoweb\SfRegister\ViewHelpers\Array;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class AddViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('array', 'array', 'Array to add value to');
        $this->registerArgument('key', 'string', 'Key to add value by', true);
        $this->registerArgument('value', 'mixed', 'Value to add');
    }

    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        $array = $this->arguments['array'] ?: [];
        $key = $this->arguments['key'];

        $array[$key] = $this->arguments['value'] ?: $this->renderChildren();

        return $array;
    }
}
