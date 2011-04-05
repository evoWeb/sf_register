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
 * Service to handle file upload and deletion
 */
class Tx_SfRegister_Services_File implements t3lib_Singleton {
	/**
	 * @var string
	 */
	protected $fieldname;

	/**
	 * @var Tx_Extbase_MVC_Request
	 */
	protected $request;

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var string
	 */
	protected $allowedFileExtensions = '';

	/**
	 * @var string
	 */
	protected $tempFolder = 'typo3temp/sf_register';

	/**
	 * @var string
	 */
	protected $uploadFolder = '';

	/**
	 * @var integer
	 */
	protected $maxFilesize = 0;

	/**
	 * Constructor
	 *
	 * @param string $fieldname name of the parameter the file belongs to in the user model
	 * @return void
	 */
	public function __construct($fieldname) {
		$this->fieldname = $fieldname;

		t3lib_div::loadTCA('fe_users');
		$this->allowedFileExtensions = $GLOBALS['TCA']['fe_users']['columns'][$this->fieldname]['config']['allowed'];
		$this->uploadFolder = $GLOBALS['TCA']['fe_users']['columns'][$this->fieldname]['config']['uploadfolder'];
		$this->maxFilesize = $GLOBALS['TCA']['fe_users']['columns'][$this->fieldname]['config']['max_size'] * 1024;
	}

	/**
	 * Setter for controller
	 *
	 * @param Tx_Extbase_MVC_Request $request
	 * @return void
	 */
	public function setRequest(Tx_Extbase_MVC_Request $request) {
		$this->request = $request;
	}

	/**
	 * Returns an array of errors which occurred during the last isValid() call.
	 *
	 * @return array An array of Tx_Extbase_Validation_Error objects or an empty array if no errors occurred.
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Creates a new validation error object and adds it to $this->errors
	 *
	 * @param string $message The error message
	 * @param integer $code The error code (a unix timestamp)
	 * @return void
	 */
	protected function addError($message, $code) {
		$this->errors[] = t3lib_div::makeInstance('Tx_Extbase_Validation_Error', $message, $code);
	}

	/**
	 * Get the namespace of the uploaded file
	 *
	 * @return string
	 */
	protected function getNamespace() {
		return strtolower('tx_' . $this->request->getControllerExtensionName() . '_' . $this->request->getPluginName());
	}

	/**
	 * Get file info of uploaded file
	 *
	 * @return array
	 */
	protected function getUploadedFileInfo() {
		$uploadData = $_FILES[$this->getNamespace()];
		$fileData = array();

		if (is_array($uploadData) && count($uploadData) > 0) {
			$filename = str_replace(chr(0), '', $uploadData['name'][$this->fieldname]);
			$type = $uploadData['type'][$this->fieldname];
			$tmpName = $uploadData['tmp_name'][$this->fieldname];
			$error = $uploadData['error'][$this->fieldname];
			$size = $uploadData['size'][$this->fieldname];

			if ($filename !== NULL && $filename !== '' && t3lib_div::validPathStr($filename)) {
				$fileData = array(
					'filename' => $filename,
					'type' => $type,
					'tmp_name' => $tmpName,
					'error' => $error,
					'size' => $size,
				);
			}
		}

		return $fileData;
	}


	/**
	 * Validation of image uploads
	 *
	 * @return boolean
	 */
	public function isValid() {
		$result = TRUE;

		$fileData = $this->getUploadedFileInfo();
		$filePathinfo = pathinfo($fileData['filename']);

		$result = $this->isAllowedFilesize($fileData['size']) && $result ? TRUE : FALSE;
		$result = $this->isAllowedFileExtension($filePathinfo['extension']) && $result ? TRUE : FALSE;

		return $result;
	}

	/**
	 * Check if the file size is in allowed limit
	 *
	 * @param integer $filesize
	 * @return boolean
	 */
	protected function isAllowedFilesize($filesize) {
		$result = TRUE;

		if ($filesize > $this->maxFilesize) {
			$this->addError(
				Tx_Extbase_Utility_Localization::translate('error.' . $this->fieldname . '.filesize', 'SfRegister'),
				1296591064
			);
			$result = FALSE;
		}

		return $result;
	}

	/**
	 * Check if the file has an allowed file extension
	 *
	 * @param string $fileExtension
	 * @return boolean
	 */
	protected function isAllowedFileExtension($fileExtension) {
		$result = TRUE;

		if ($fileExtension !== NULL && !t3lib_div::inList($this->allowedFileExtensions, strtolower($fileExtension))) {
			$this->addError(
				Tx_Extbase_Utility_Localization::translate('error.' . $this->fieldname . '.extension', 'SfRegister'),
				1296591064
			);
			$result = FALSE;
		}

		return $result;
	}


	/**
	 * Move an temporary uploaded file to the upload folder
	 *
	 * @return string
	 */
	public function moveTempFileToTempFolder() {
		$result = '';
		$fileData = $this->getUploadedFileInfo();

		if (count($fileData)) {
			$basicFileFunctions = t3lib_div::makeInstance('t3lib_basicFileFunctions');

			$filename = $basicFileFunctions->cleanFileName($fileData['filename']);
			$uploadFolder = $basicFileFunctions->cleanDirectoryName(PATH_site . $this->tempFolder);
			$uniqueFilename = $basicFileFunctions->getUniqueName($filename, $uploadFolder);

			$this->createUploadFolderIfNotExist($uploadFolder);

			if (t3lib_div::upload_copy_move($fileData['tmp_name'], $uniqueFilename)) {
				$result = basename($uniqueFilename);
			}
		}

		return $result;
	}

	/**
	 * @param  $uploadFolder
	 * @return void
	 */
	protected function createUploadFolderIfNotExist($uploadFolder) {
		if (!is_dir($uploadFolder)) {
			t3lib_div::mkdir($uploadFolder);
		}
	}

	/**
	 * Move an temporary uploaded file to the upload folder
	 *
	 * @return string
	 */
	public function moveFileFromTempFolderToUploadFolder($filename) {
		$result = '';

		if ($filename) {
			$allowedFolders = array(1 => array('path' => $this->tempFolder), 2 => array('path' => $this->uploadFolder));

			$fileExtensions = (array) $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions'];
			$fileExtensions['webspace']['allow'] = $this->allowedFileExtensions;

			$extFileFunctions = t3lib_div::makeInstance('t3lib_extFileFunctions');
			$extFileFunctions->init($allowedFolders, $fileExtensions);
			$extFileFunctions->init_actionPerms(1);

			$cmds = array(
				'data' => $this->tempFolder . '/' . $filename,
				'target' => $this->uploadFolder,
				'altName' => TRUE
			);

			$result = $extFileFunctions->func_move($cmds);
			$resultParts = t3lib_div::trimExplode('/', $result);
			$result = array_pop($resultParts);
		}

		return $result;
	}

	/**
	 * @param  $filename
	 * @return void
	 */
	public function removeTemporaryFile($filename) {
		return $this->removeFile($filename, $this->tempFolder);
	}

	/**
	 * @param  $filename
	 * @return string
	 */
	public function removeUploadedImage($filename) {
		return $this->removeFile($filename, $this->uploadFolder);
	}

	/**
	 * Return image from upload folder
	 *
	 * @param string $filename name of the file to remove
	 * @param string $filepath path where the image is stored
	 * @return string
	 */
	public function removeFile($filename, $filepath) {
		$imageNameAndPath = PATH_site . $filepath . '/' . $filename;

		if (@file_exists($imageNameAndPath)) {
			unlink($imageNameAndPath);
		}

		return $filename;
	}

	/**
	 * @param  $filename
	 * @return void
	 */
	protected function getFilepath($filename) {
		$filenameParts = t3lib_div::trimExplode('/', $filename, TRUE);

		$result = implode('/', array_slice($filenameParts, 0, -1));
		if (!in_array($result, array($this->tempFolder, $this->uploadFolder))) {
			$result = '';
		}

		return $result;
	}

	/**
	 * @param  $filename
	 * @return void
	 */
	protected function getFilename($filename) {
		$filenameParts = t3lib_div::trimExplode('/', $filename, TRUE);

		return array_pop($filenameParts);
	}
}

?>