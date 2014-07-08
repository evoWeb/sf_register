<?php
namespace Evoweb\SfRegister\Services;
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
 * Service to handle file upload and deletion
 */
class File implements \TYPO3\CMS\Core\SingletonInterface {
	/**
	 * Object manager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 * @inject
	 */
	protected $objectManager;

	/**
	 * Fieldname
	 *
	 * @var string
	 */
	protected $fieldname;

	/**
	 * Configuration manager
	 *
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
	 */
	protected $configurationManager;

	/**
	 * Settings
	 *
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Namespace
	 *
	 * @var  string
	 */
	protected $namespace = '';

	/**
	 * Errors
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Allowed file extensions
	 *
	 * @var string
	 */
	protected $allowedFileExtensions = '';

	/**
	 * Temporary folder
	 *
	 * @var string
	 */
	protected $tempFolder = 'typo3temp/sf_register';

	/**
	 * Upload folder
	 *
	 * @var string
	 */
	protected $uploadFolder = '';

	/**
	 * Maximal filesize
	 *
	 * @var integer
	 */
	protected $maxFilesize = 0;


	/**
	 * Injection of configuration manager
	 *
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
		);

		if (isset($this->settings['filefieldname']) && !empty($this->settings['filefieldname'])) {
			$this->setFieldname($this->settings['filefieldname']);
		}
	}


	/**
	 * Getter for temporary folder
	 *
	 * @return string
	 */
	public function getTempFolder() {
		return $this->tempFolder;
	}

	/**
	 * Getter for upload folder
	 *
	 * @return string
	 */
	public function getUploadFolder() {
		return $this->uploadFolder;
	}

	/**
	 * @param string $fieldname
	 * @return void
	 */
	public function setFieldname($fieldname) {
		$this->fieldname = $fieldname;

		$fieldConfiguration = $GLOBALS['TCA']['fe_users']['columns'][$this->fieldname]['config'];
		$this->allowedFileExtensions = $fieldConfiguration['allowed'];
		$this->uploadFolder = $fieldConfiguration['uploadfolder'];
		$this->maxFilesize = $fieldConfiguration['max_size'] * 1024;
	}

	/**
	 * Returns an array of errors which occurred during the last isValid() call.
	 *
	 * @return array An array of \TYPO3\CMS\Extbase\Validation\Error objects
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
		$this->errors[] = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Validation\\Error', $message, $code);
	}

	/**
	 * Get the namespace of the uploaded file
	 *
	 * @return string
	 */
	protected function getNamespace() {
		if ($this->namespace === '') {
			$frameworkSettings = $this->configurationManager->getConfiguration(
				\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
			);
			$this->namespace = strtolower('tx_' . $frameworkSettings['extensionName'] . '_' . $frameworkSettings['pluginName']);
		}

		return $this->namespace;
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

			if ($filename !== NULL && $filename !== '' && \TYPO3\CMS\Core\Utility\GeneralUtility::validPathStr($filename)) {
				if ($this->settings['useEncryptedFilename']) {
					$filenameParts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('.', $filename);
					$extension = array_pop($filenameParts);
					$filename = md5(mktime() . mt_rand() . $filename . $tmpName .
						$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']) . '.' . $extension;
				}

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
				\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_' . $this->fieldname . '_filesize', 'SfRegister'),
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

		if (
			$fileExtension !== NULL &&
			!\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->allowedFileExtensions, strtolower($fileExtension))
		) {
			$this->addError(
				\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('error_' . $this->fieldname . '_extension', 'SfRegister'),
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
			/** @var $basicFileFunctions \TYPO3\CMS\Core\Utility\File\BasicFileUtility */
			$basicFileFunctions = $this->objectManager->get('TYPO3\\CMS\\Core\\Utility\\File\\BasicFileUtility');

			$filename = $basicFileFunctions->cleanFileName($fileData['filename']);
			$uploadFolder = \TYPO3\CMS\Core\Utility\PathUtility::getCanonicalPath(PATH_site . $this->tempFolder);

			$this->createUploadFolderIfNotExist($uploadFolder);

			$uniqueFilename = $basicFileFunctions->getUniqueName($filename, $uploadFolder);

			if (\TYPO3\CMS\Core\Utility\GeneralUtility::upload_copy_move($fileData['tmp_name'], $uniqueFilename)) {
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
			\TYPO3\CMS\Core\Utility\GeneralUtility::mkdir($uploadFolder);
		}
	}

	/**
	 * Move an temporary uploaded file to the upload folder
	 *
	 * @param string &$filename
	 * @return void
	 */
	public function moveFileFromTempFolderToUploadFolder(&$filename) {
		if ($filename === '') {
			return;
		}

		$this->createUploadFolderIfNotExist($this->uploadFolder);

		$fileExtensions = (array) $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions'];
		$fileExtensions['webspace']['allow'] = $this->allowedFileExtensions;

		$fileCommands = array(0 => array(
			'data' => $this->tempFolder . '/' . $filename,
			'target' => $this->uploadFolder,
			'altName' => TRUE
		));

		/** @var $extFileFunctions \Evoweb\SfRegister\Utility\File\ExtendedFileUtility */
		$extFileFunctions = $this->objectManager->get('Evoweb\\SfRegister\\Utility\\File\\ExtendedFileUtility');

		if (version_compare(TYPO3_branch, '6.2', '<')) {
			$extFileFunctions->init(array(), $fileExtensions);
			$extFileFunctions->init_actionPerms(1);
			$extFileFunctions->start($fileCommands);

			$result = $extFileFunctions->processData();
			$filename = $result['move'][0];
		} else {
			$extFileFunctions->setActionPermissions(array(
				'addFile' => TRUE,
				'moveFile' => TRUE,
				'copyFile' => TRUE,
				'renameFile' => TRUE,
				'readFile' => TRUE,
			));
			$extFileFunctions->start($fileCommands);

			$result = $extFileFunctions->processData();
			/** @var \TYPO3\CMS\Core\Resource\File $file */
			$file = $result['move'][0];
			$filename = $file->getIdentifier();
		}
	}

	/**
	 * @param  $filename
	 * @return string
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
	protected function removeFile($filename, $filepath) {
		$imageNameAndPath = PATH_site . $filepath . '/' . $filename;

		if (@file_exists($imageNameAndPath)) {
			unlink($imageNameAndPath);
		}

		return $filename;
	}

	/**
	 * @param  $filename
	 * @return string
	 */
	protected function getFilepath($filename) {
		$filenameParts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('/', $filename, TRUE);

		$result = implode('/', array_slice($filenameParts, 0, -1));
		if (!in_array($result, array($this->tempFolder, $this->uploadFolder))) {
			$result = '';
		}

		return $result;
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	protected function getFilename($filename) {
		$filenameParts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('/', $filename, TRUE);

		return array_pop($filenameParts);
	}
}
