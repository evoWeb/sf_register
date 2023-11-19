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

namespace Evoweb\SfRegister\ViewHelpers\Form;

/**
 * View helper to render a select box with values
 * in given steps from start to end value
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.rangeSelect property="day" start="1" end="31"/>
 * </code>
 */
class RangeSelectViewHelper extends AbstractSelectViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'optionValueField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the value.'
        );
        $this->registerArgument(
            'optionLabelField',
            'string',
            'If specified, will call the appropriate getter on each object to determine the label.'
        );
        $this->registerArgument(
            'start',
            'int',
            'Value to start range with (default 1)',
            false,
            1
        );
        $this->registerArgument(
            'end',
            'int',
            'Value to end range with (default 20)',
            false,
            20
        );
        $this->registerArgument(
            'step',
            'int',
            'Step to increase value of each option (default 1)',
            false,
            1
        );
        $this->registerArgument(
            'digits',
            'int',
            'Length of number string, for example 01, ..., 09, 10, 11 (default 2)',
            false,
            2
        );
    }

    public function initialize()
    {
        parent::initialize();

        $start = (int)$this->arguments['start'];
        $end = (int)$this->arguments['end'];
        $step = (int)$this->arguments['step'];
        $digits = (int)$this->arguments['digits'];

        $this->arguments['options'] = array_map(
            static fn($number) => sprintf('%0' . $digits . 's', $number),
            range($start, $end, $step)
        );
    }
}
