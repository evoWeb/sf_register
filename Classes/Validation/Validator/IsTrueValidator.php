<?php
namespace Evoweb\SfRegister\Validation\Validator;
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
 * Validator to check if a given value is equal to true
 *
 * @scope singleton
 */
class IsTrueValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator implements \TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface {
	/**
	 * If the given value is set
	 *
	 * @param boolean $value The value
	 * @return boolean
	 */
	public function isValid($value) {
		$result = TRUE;

		if ($value !== TRUE) {
			$this->addError(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_nottrue', 'SfRegister'), 1301599670);
			$result = FALSE;
		}

		return $result;
	}
}

?>