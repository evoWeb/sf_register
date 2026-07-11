<?php

declare(strict_types=1);

/*
 * !ONLY FOR TEST PURPOSE!
 *
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\View;

use TYPO3\CMS\Core\View\ViewInterface;

/**
 * A minimal ViewInterface test double that just records assigned variables
 * instead of rendering real Fluid templates, so controller tests can assert
 * on the observable "assign()" calls without needing template fixtures.
 */
final class RecordingView implements ViewInterface
{
    /**
     * @var array<string, mixed>
     */
    public array $variables = [];

    public string $renderResult = 'rendered';

    public function assign(string $key, mixed $value): self
    {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function assignMultiple(array $values): self
    {
        $this->variables = array_merge($this->variables, $values);
        return $this;
    }

    public function render(string $templateFileName = ''): string
    {
        return $this->renderResult;
    }
}
