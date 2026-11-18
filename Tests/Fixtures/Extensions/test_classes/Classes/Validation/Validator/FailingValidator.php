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

namespace EvowebTests\TestClasses\Validation\Validator;

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Fails on every validation, like a captcha that was already used up in the preview
 */
class FailingValidator extends AbstractValidator
{
    public const MESSAGE = 'Validation failed by intention';

    protected $acceptsEmptyValues = false;

    protected function isValid(mixed $value): void
    {
        $this->addError(self::MESSAGE, 1763400000);
    }
}
