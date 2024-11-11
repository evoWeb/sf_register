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

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

class FrontendUserConverter extends AbstractTypeConverter
{
    public function __construct(protected FrontendUserRepository $frontendUserRepository) {}

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     * The return value can be one of three types:
     * - an arbitrary object, or a simple type (which has been created while mapping)
     *   This is the normal case.
     * - NULL, indicating that this object should *not* be mapped
     *   (i.e. a "File Upload" Converter could return NULL if no file has been
     *   uploaded, and a silent failure should occur.)
     * - An instance of \TYPO3\CMS\Extbase\Error\Error
     *   This will be a user-visible error message later on.
     * Furthermore, it should throw an Exception if an unexpected failure
     * (like a security error) occurred or a configuration issue happened.
     *
     * @param array<string, mixed> $convertedChildProperties
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        ?PropertyMappingConfigurationInterface $configuration = null
    ): ?object {
        return $this->frontendUserRepository->findByUid($source);
    }
}
