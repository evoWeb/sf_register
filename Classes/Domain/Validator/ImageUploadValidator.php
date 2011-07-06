<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Validator to check if the uploaded image could be handled
 *
 * @scope singleton
 */
class Tx_SfRegister_Domain_Validator_ImageUploadValidator extends Tx_Extbase_Validation_Validator_AbstractValidator {
	/**
	 * Service to handle files
	 *
	 * @var Tx_SfRegister_Services_File
	 */
	protected $fileService;

	/**
	 * Inject of the file service
	 *
	 * @param Tx_SfRegister_Services_File $fileService
	 * @return void
	 */
	public function injectFileService(Tx_SfRegister_Services_File $fileService) {
		$this->fileService = $fileService;
	}

	/**
	 * If the given value is set
	 *
	 * @param boolean $value The value
	 * @return boolean
	 */
	public function isValid($value) {
		$result = TRUE;

		if (!$this->fileService->isValid()) {
			$this->errors = $this->fileService->getErrors();
			$result = FALSE;
		}

		return $result;
	}
}

?>