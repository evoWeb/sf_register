<?php

namespace Evoweb\SfRegister\Validation\Validator;

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

use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationOptionsException;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator as BaseAbstractValidator;

abstract class AbstractValidator extends BaseAbstractValidator
{
    public function setOptions(array $options = []): void
    {
        // check for options given but not supported
        if (($unsupportedOptions = array_diff_key($options, $this->supportedOptions)) !== []) {
            throw new InvalidValidationOptionsException(
                'Unsupported validation option(s) found: ' . implode(', ', array_keys($unsupportedOptions)),
                1379981890
            );
        }

        // check for required options being set
        array_walk(
            $this->supportedOptions,
            static function ($supportedOptionData, $supportedOptionName, $options) {
                if (
                    isset($supportedOptionData[3])
                    && $supportedOptionData[3] === true
                    && !array_key_exists($supportedOptionName, $options)
                ) {
                    throw new InvalidValidationOptionsException(
                        'Required validation option not set: ' . $supportedOptionName,
                        1379981891
                    );
                }
            },
            $options
        );

        // merge with default values
        $this->options = array_merge(
            array_map(
                static function ($value) {
                    return $value[0];
                },
                $this->supportedOptions
            ),
            $options
        );
    }
}
