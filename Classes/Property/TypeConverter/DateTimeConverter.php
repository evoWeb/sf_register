<?php

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

namespace Evoweb\SfRegister\Property\TypeConverter;

use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Property\Exception\TypeConverterException;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter as BaseDateTimeConverter;

class DateTimeConverter extends BaseDateTimeConverter
{
    public const CONFIGURATION_USER_DATA = '1';

    /**
     * Converts $source to a \DateTime using the configured dateFormat
     *
     * @param array<string, mixed> $source
     * @param array<string, mixed> $convertedChildProperties
     * @throws TypeConverterException
     * @throws \DateMalformedStringException
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ): null|\DateTime|Error {
        $userData = $configuration->getConfigurationValue(self::class, self::CONFIGURATION_USER_DATA);
        if (
            is_array($userData)
            && !empty($userData)
            && strlen($userData['dateOfBirthDay'] ?? '') > 0
            && strlen($userData['dateOfBirthMonth'] ?? '') > 0
            && strlen($userData['dateOfBirthYear'] ?? '') > 0
        ) {
            $date = new \DateTime(implode(
                '-',
                [$userData['dateOfBirthYear'], $userData['dateOfBirthMonth'], $userData['dateOfBirthDay']]
            ));
        } else {
            $date = parent::convertFrom($source, $targetType, $convertedChildProperties, $configuration);
        }

        return $date;
    }
}
