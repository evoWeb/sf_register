<?php
namespace Evoweb\SfRegister\Utility;
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

/**
 * Class ClassCacheManager
 *
 * @package Evoweb\SfRegister\Utility
 */
class ClassCacheManager {
	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected $extensionKey = 'sf_register';

	/**
	 * @var \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend
	 */
	protected $cacheInstance;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->cacheInstance = ClassLoader::initializeCache(SFREGISTERCACHEIDENTIFIER);
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
				&& \TYPO3\CMS\Core\Utility\GeneralUtility::inList('all,temp_cached', $parameters['cacheCmd'])
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

		/**
		 * @var \TYPO3\CMS\Core\Authentication\BackendUserAuthentication $backendUser
		 */
		$backendUser = $GLOBALS['BE_USER'];
		if (isset($backendUser)) {
			$backendUser->writelog(3, 1, 0, 0, '[SfRegister]: User %s has cleared the class cache', array($backendUser->user['username']));
		}
	}

	/**
	 * Recreate cache folder
	 *
	 * @return void
	 */
	public function recreateCacheFolder() {
		/** @var \TYPO3\CMS\Core\Cache\Backend\FileBackend $cacheBackend */
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
	 * @throws \Exception
	 */
	public function build() {
		if (
			isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey])
			&& isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey]['entities'])
			&& is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey]['entities'])
		) {
			$entities = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey]['entities'];
			foreach ($entities as $entity => $extensionsConfiguration) {
				$key = 'Domain/Model/' . $entity;

				// Get the file from sf_register itself, this needs to be loaded as first
				$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extensionKey) . 'Classes/' . $key . '.php';
				if (!is_file($path)) {
					throw new \Exception('given file "' . $path . '" does not exist');
				}
				$code = $this->parseSingleFile($path, FALSE);

				// Get the files from all other extensions that are extending this domain model
				if (is_array($extensionsConfiguration) && count($extensionsConfiguration) > 0) {
					$extensionsWithThisClass = array_keys($extensionsConfiguration);
					foreach ($extensionsWithThisClass as $extension) {
						$path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($extension) . 'Classes/' . $key . '.php';
						if (is_file($path)) {
							$code .= $this->parseSingleFile($path);
						}
					}
				}

					// Close the class definition
				$code = $this->closeClassDefinition($code);

					// Add the new file to the class cache
				$entryIdentifier = str_replace('/', '', $key);
				try {
					$this->cacheInstance->set($entryIdentifier, $code);
				} catch (\Exception $e) {
					throw new \Exception($e->getMessage());
				}
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
	 * @throws \InvalidArgumentException
	 */
	public function parseSingleFile($filePath, $removeClassDefinition = TRUE) {
		if (!is_file($filePath)) {
			throw new \InvalidArgumentException(sprintf('File "%s" could not be found', $filePath));
		}
		$code = \TYPO3\CMS\Core\Utility\GeneralUtility::getUrl($filePath);
		return $this->changeCode($code, $filePath, $removeClassDefinition);
	}

	/**
	 * Modifies code comming from php file and removes all unwanted parts
	 *
	 * @param string $code
	 * @param string $filePath
	 * @param boolean $removeClassDefinition
	 * @param boolean $renderPartialInfo
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	protected function changeCode($code, $filePath, $removeClassDefinition = TRUE, $renderPartialInfo = TRUE) {
		if (empty($code)) {
			throw new \InvalidArgumentException(sprintf('File "%s" could not be fetched or is empty', $filePath));
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
	 * Add information from which file the partial is taken
	 *
	 * @param string $filePath
	 * @return string
	 */
	protected function getPartialInfo($filePath) {
		return '/*' . str_repeat('*', 70) . LF . ' * this is partial from: ' . $filePath . LF . str_repeat('*', 70) . '*/' . LF . TAB;
	}

	/**
	 * Add class closing part
	 *
	 * @param string $code
	 * @return string
	 */
	protected function closeClassDefinition($code) {
		return $code . LF . '}';
	}
}
