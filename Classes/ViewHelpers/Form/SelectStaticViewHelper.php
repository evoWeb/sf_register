<?php
namespace Evoweb\SfRegister\ViewHelpers\Form;
/***************************************************************
 * Copyright notice
 *
 * (c) 2011-13 Sebastian Fischer <typo3@evoweb.de>
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
 * Base class for static
 * viewhelpers to beable to use options not set by options
 * argument but by initialization method
 */
class SelectStaticViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper {
	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * Render the option tags.
	 *
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return array an associative array of options
	 */
	protected function getOptionsold() {
		if (!is_array($this->options) && !($this->options instanceof \Traversable)) {
			return array();
		}

		$options = array();
		$optionsArgument = $this->options;
		foreach ($optionsArgument as $key => $value) {
			if (is_object($value)) {

				if ($this->hasArgument('optionValueField')) {
					$key = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($value, $this->arguments['optionValueField']);
					if (is_object($key)) {
						if (method_exists($key, '__toString')) {
							$key = (string)$key;
						} else {
							throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(
								'Identifying value for object of class "' . get_class($value) . '" was an object.', 1247827428
							);
						}
					}
				} elseif ($this->persistenceManager->getIdentifierByObject($value) !== NULL) {
					$key = $this->persistenceManager->getIdentifierByObject($value);
				} elseif (method_exists($value, '__toString')) {
					$key = (string)$value;
				} else {
					throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(
						'No identifying value for object of class "' . get_class($value) . '" found.', 1247826696
					);
				}

				if ($this->hasArgument('optionLabelField')) {
					$value = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($value, $this->arguments['optionLabelField']);
					if (is_object($value)) {
						if (method_exists($value, '__toString')) {
							$value = (string)$value;
						} else {
							throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(
								'Label value for object of class "' . get_class($value) . '" was an object without a __toString() method.', 1247827553
							);
						}
					}
				} elseif (method_exists($value, '__toString')) {
					$value = (string)$value;
				} elseif ($this->persistenceManager->getIdentifierByObject($value) !== NULL) {
					$value = $this->persistenceManager->getIdentifierByObject($value);
				}
			}
			$options[$key] = $value;
		}
		if ($this->arguments['sortByOptionLabel']) {
			asort($options);
		}
		return $options;
	}

	/**
	 * Render the option tags.
	 *
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return array an associative array of options
	 * @todo testen
	 */
	protected function getOptions() {
		if (!is_array($this->arguments['options']) && !$this->arguments['options'] instanceof \Traversable) {
			return array();
		}
		$options = array();
		$optionsArgument = $this->arguments['options'];
		foreach ($optionsArgument as $key => $value) {
			if (is_object($value)) {
				if ($this->hasArgument('optionValueField')) {
					$key = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($value, $this->arguments['optionValueField']);
					if (is_object($key)) {
						if (method_exists($key, '__toString')) {
							$key = (string) $key;
						} else {
							throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(
								'Identifying value for object of class "' . get_class($value) . '" was an object.', 1247827428
							);
						}
					}
				// @todo use $this->persistenceManager->isNewObject() once it is implemented
				} elseif ($this->persistenceManager->getIdentifierByObject($value) !== NULL) {
					$key = $this->persistenceManager->getIdentifierByObject($value);
				} elseif (method_exists($value, '__toString')) {
					$key = (string) $value;
				} else {
					throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(
						'No identifying value for object of class "' . get_class($value) . '" found.', 1247826696
					);
				}
				if ($this->hasArgument('optionLabelField')) {
					$value = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::getPropertyPath($value, $this->arguments['optionLabelField']);
					if (is_object($value)) {
						if (method_exists($value, '__toString')) {
							$value = (string) $value;
						} else {
							throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(
								'Label value for object of class "' . get_class($value) . '" was an object without a __toString() method.', 1247827553
							);
						}
					}
				} elseif (method_exists($value, '__toString')) {
					$value = (string) $value;
				// @todo use $this->persistenceManager->isNewObject() once it is implemented
				} elseif ($this->persistenceManager->getIdentifierByObject($value) !== NULL) {
					$value = $this->persistenceManager->getIdentifierByObject($value);
				}
			}
			$options[$key] = $value;
		}
		if ($this->arguments['sortByOptionLabel']) {
			asort($options, SORT_LOCALE_STRING);
		}
		return $options;
	}
}
