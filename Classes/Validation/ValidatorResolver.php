<?php
namespace Evoweb\SfRegister\Validation;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
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

/**
 * Validator resolver to automatically find a validator for a given subject
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{
    /**
     * Match validator names and options
     *
     * @var string
     */
    const PATTERN_MATCH_VALIDATORS = '/
			(?:^|,\s*)
			(?P<validatorName>[a-z0-9:.\\\\]+)
			\s*
			(?:\(
				(?P<validatorOptions>(?:\s*[a-z0-9]+\s*=\s*(?:
					"(?:\\\\"|[^"])*"
					|\'(?:\\\\\'|[^\'])*\'
					|(?:\s|[^,"\']*)
				)(?:\s|,)*)*)
			\))?
		/ixS';

    /**
     * Match validator options (to parse actual options)
     *
     * @var string
     */
    const PATTERN_MATCH_VALIDATOROPTIONS = '/
			\s*
			(?P<optionName>[a-z0-9]+)
			\s*=\s*
			(?P<optionValue>
				"(?:\\\\"|[^"])*"
				|\'(?:\\\\\'|[^\'])*\'
				|(?:\s|[^,"\']*)
			)
		/ixS';

    public function getParsedValidatorAnnotation(string $validateValue): array
    {
        /** @todo needs to be refactored */
        return $this->parseValidatorAnnotation($validateValue);
    }

    /**
     * Parses the validator options given in @validate annotations.
     *
     * @param string $validateValue
     * @return array
     * @internal
     */
    public function parseValidatorAnnotation($validateValue)
    {
        $matches = [];
        if ($validateValue[0] === '$') {
            $parts = explode(' ', $validateValue, 2);
            $validatorConfiguration = ['argumentName' => ltrim($parts[0], '$'), 'validators' => []];
            preg_match_all(self::PATTERN_MATCH_VALIDATORS, $parts[1], $matches, PREG_SET_ORDER);
        } else {
            $validatorConfiguration = ['validators' => []];
            preg_match_all(self::PATTERN_MATCH_VALIDATORS, $validateValue, $matches, PREG_SET_ORDER);
        }
        foreach ($matches as $match) {
            $validatorOptions = [];
            if (isset($match['validatorOptions'])) {
                $validatorOptions = $this->parseValidatorOptions($match['validatorOptions']);
            }
            $validatorConfiguration['validators'][] = [
                'validatorName' => $match['validatorName'],
                'validatorOptions' => $validatorOptions
            ];
        }
        return $validatorConfiguration;
    }

    /**
     * Parses $rawValidatorOptions not containing quoted option values.
     * $rawValidatorOptions will be an empty string afterwards (pass by ref!).
     *
     * @param string $rawValidatorOptions
     * @return array An array of optionName/optionValue pairs
     */
    protected function parseValidatorOptions($rawValidatorOptions)
    {
        $validatorOptions = [];
        $parsedValidatorOptions = [];
        preg_match_all(self::PATTERN_MATCH_VALIDATOROPTIONS, $rawValidatorOptions, $validatorOptions, PREG_SET_ORDER);
        foreach ($validatorOptions as $validatorOption) {
            $parsedValidatorOptions[trim($validatorOption['optionName'])] = trim($validatorOption['optionValue']);
        }
        array_walk($parsedValidatorOptions, [$this, 'unquoteString']);
        return $parsedValidatorOptions;
    }
}
