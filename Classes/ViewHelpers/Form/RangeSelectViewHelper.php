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

/**
 * View helper to render a select box with values
 * in given steps from start to end value
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.rangeSelect property="day" start="1" end="31"/>
 * </code>
 */
class RangeSelectViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper
{
    /**
     * Value to start range with
     *
     * @var int
     */
    protected $start = 0;

    /**
     * Value to end range with
     *
     * @var int
     */
    protected $end = PHP_INT_MAX;

    /**
     * Step to increase value of each option
     *
     * @var int
     */
    protected $step = 1;

    /**
     * In case of a value lower then 10 and digits
     * defined as 2 the label get prepended with a 0
     *
     * @var int
     */
    protected $digits = 2;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'start',
            'int',
            'start',
            true
        );
        $this->registerArgument(
            'end',
            'int',
            'end',
            true
        );
        $this->registerArgument(
            'step',
            'int',
            'step',
            false,
            1
        );
        $this->registerArgument(
            'digits',
            'int',
            'digits',
            false,
            2
        );
    }

    public function render(): string
    {
        $this->start = $this->arguments['start'];
        $this->end = $this->arguments['end'];
        $this->step = (int)$this->arguments['step'];
        $this->digits = (int)$this->arguments['digits'];

        return parent::render();
    }

    protected function getOptions(): array
    {
        $options = [];

        for ($current = $this->start; $current <= $this->end;) {
            $options[$current] = sprintf('%0' . $this->digits . 's', $current);

            $current += $this->step;
            if ($current > $this->end) {
                break;
            }
        }

        return $options;
    }
}
