<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Sebastian Fischer <typo3@evoweb.de>
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

class Tx_SfRegister_Utility_ClassCacheManager {
	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected $extensionKey = 'sf_register';

	/**
	 * @var t3lib_cache_frontend_PhpFrontend
	 */
	protected $cacheInstance;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->cacheInstance = Tx_SfRegister_Utility_ClassLoader::initializeCache(SFREGISTERCACHEIDENTIFIER);
	}

	/**
	 * Rebuild the class cache
	 *
	 * @param array $parameters
	 * @return void
	 */
	public function reBuild(array $parameters = array()) {
		if (
			empty($parameters)
			|| (
				!empty($parameters['cacheCmd'])
				&& t3lib_div::inList('all,temp_cached', $parameters['cacheCmd'])
				&& isset($GLOBALS['BE_USER'])
			)
		) {
			$this->clear();
			$this->recreateCacheFolder();
			$this->build();
		}
	}

	/**
	 * Clear the class cache
	 *
	 * @return void
	 */
	public function clear() {
		$this->cacheInstance->flush();
		/** @var t3lib_beUserAuth $backendUser */
		$backendUser = $GLOBALS['BE_USER'];
		if (isset($backendUser)) {
			$backendUser->writelog(3, 1, 0, 0, '[SfRegister]: User %s has cleared the class cache', array($backendUser->user['username']));
		}
	}

	/**
	 * @return void
	 */
	public function recreateCacheFolder() {
		/** @var t3lib_cache_backend_FileBackend $cacheBackend */
		$cacheBackend = $this->cacheInstance->getBackend();
		$cacheDirectory = $cacheBackend->getCacheDirectory();
		if (!file_exists($cacheDirectory)) {
			$cacheBackend->setCacheDirectory(dirname($cacheDirectory));
		}
	}

	/**
	 * Builds and caches the proxy files
	 *
	 * @return void
	 * @throws Exception
	 */
	public function build() {
		$entities = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey]['entities'];
		foreach ($entities as $entity => $extensionsConfiguration) {
			$key = 'Domain/Model/' . $entity;

				// Get the file from static_info_tables itself, this needs to be loaded as first
			$path = t3lib_extMgm::extPath($this->extensionKey) . 'Classes/' . $key . '.php';
			if (!is_file($path)) {
				throw new Exception('given file "' . $path . '" does not exist');
			}
			$code = $this->parseSingleFile($path, FALSE);

				// Get the files from all other extensions that are extending this domain model class
			if (is_array($extensionsConfiguration) && count($extensionsConfiguration) > 0) {
				$extensionsWithThisClass = array_keys($entities[$entity]);
				foreach ($extensionsWithThisClass as $extension) {
					$path = t3lib_extMgm::extPath($extension) . 'Classes/' . $key . '.php';
					if (is_file($path)) {
						$code .= $this->parseSingleFile($path);
					}
				}
			}

				// Close the class definition and the php tag
			$code = $this->closeClassDefinition($code);

				// The file is added to the class cache
			$entryIdentifier = str_replace('/', '', $key);
			try {
				$this->cacheInstance->set($entryIdentifier, $code);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
	}

	/**
	 * Parse a single file and does some magic
	 * - Remove the php tags
	 * - Remove the class definition (if set)
	 *
	 * @param string $filePath path of the file
	 * @param boolean $removeClassDefinition If class definition should be removed
	 * @return string path of the saved file
	 * @throws Exception
	 * @throws InvalidArgumentException
	 */
	public function parseSingleFile($filePath, $removeClassDefinition = TRUE) {
		if (!is_file($filePath)) {
			throw new InvalidArgumentException(sprintf('File "%s" could not be found', $filePath));
		}
		$code = t3lib_div::getUrl($filePath);
		return $this->changeCode($code, $filePath, $removeClassDefinition);
	}

	/**
	 * @param string $code
	 * @param string $filePath
	 * @param boolean $removeClassDefinition
	 * @param boolean $renderPartialInfo
	 * @return string
	 * @throws Exception
	 */
	protected function changeCode($code, $filePath, $removeClassDefinition = TRUE, $renderPartialInfo = TRUE) {
		if (empty($code)) {
			throw new InvalidArgumentException(sprintf('File "%s" could not be fetched or is empty', $filePath));
		}
		$code = trim($code);
		$code = str_replace(array('<?php', '?>'), '', $code);
		$code = trim($code);

			// Remove everything before 'class ', including namespaces,
			// comments and require-statements.
		if ($removeClassDefinition) {
			$pos = strpos($code, 'class ');
			$pos2 = strpos($code, '{', $pos);

			$code = substr($code, $pos2 + 1);
		}

		$code = trim($code);

			// Add some information for each partial
		if ($renderPartialInfo) {
			$code = $this->getPartialInfo($filePath) . $code;
		}

			// Remove last }
		$pos = strrpos($code, '}');
		$code = substr($code, 0, $pos);
		$code = trim($code);
		return $code . LF . LF;
	}

	/**
	 * @param string $filePath
	 * @return string
	 */
	protected function getPartialInfo($filePath) {
		return '/*' . str_repeat('*', 70) . LF .
			' * this is partial from: ' . $filePath . LF . str_repeat('*', 70) . '*/' . LF . TAB;
	}

	/**
	 * @param string $code
	 * @return string
	 */
	protected function closeClassDefinition($code) {
		return $code . LF . '}';
	}
}

?>