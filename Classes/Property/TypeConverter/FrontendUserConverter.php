<?php
namespace Evoweb\SfRegister\Property\TypeConverter;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class FrontendUserTypeConverter
 *
 * @package Evoweb\SfRegister\Property
 */
class FrontendUserConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter {

	/**
	 * @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * @var array<string>
	 */
	protected $sourceTypes = array('integer', 'string');

	/**
	 * @var string
	 */
	protected $targetType = 'Evoweb\\SfRegister\\Domain\\Model\\FrontendUser';

	/**
	 * Actually convert from $source to $targetType, taking into account the fully
	 * built $convertedChildProperties and $configuration.
	 *
	 * The return value can be one of three types:
	 * - an arbitrary object, or a simple type (which has been created while mapping)
	 *   This is the normal case.
	 * - NULL, indicating that this object should *not* be mapped
	 *   (i.e. a "File Upload" Converter could return NULL if no file has been
	 *   uploaded, and a silent failure should occur.
	 * - An instance of \TYPO3\CMS\Extbase\Error\Error
	 *   This will be a user-visible error message later on.
	 *
	 * Furthermore, it should throw an Exception if an unexpected failure
	 * (like a security error) occurred or a configuration issue happened.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
	 * @return mixed|\TYPO3\CMS\Extbase\Error\Error target type, or an error object
	 * @throws \TYPO3\CMS\Extbase\Property\Exception\TypeConverterException thrown in case a developer error occurred
	 */
	public function convertFrom(
		$source,
		$targetType,
		array $convertedChildProperties = array(),
		\TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = NULL
	) {
		return $this->frontendUserRepository->findByUid($source);
	}
}