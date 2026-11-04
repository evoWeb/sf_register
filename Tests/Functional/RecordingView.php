<?php

namespace Evoweb\SfRegister\Tests\Functional;

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

use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * A view double that records every assigned variable instead of rendering a template, so a
 * functional test can assert what a controller action assigned without needing a resolvable
 * Fluid template for it.
 */
class RecordingView implements ViewInterface
{
    public array $variables = [];

    /**
     * The parameters stay untyped because the interface of the lowest supported Fluid
     * declares them without types and a stricter declaration would not be compatible.
     */
    public function assign($key, $value): self
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function assignMultiple(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->assign($key, $value);
        }
        return $this;
    }

    public function render(): string
    {
        return '';
    }

    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false): string
    {
        return '';
    }

    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false): string
    {
        return '';
    }
}
