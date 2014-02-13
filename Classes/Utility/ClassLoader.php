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

/** @noinspection PhpIncludeInspection */
require_once(t3lib_extmgm::extPath('extbase') . 'Classes/Utility/ClassLoader.php');

class Tx_SfRegister_Utility_ClassLoader extends Tx_Extbase_Utility_ClassLoader {
	/**
	 * Extension key
	 *
	 * @var string
	 */
	static protected $extensionKey = 'sf_register';

	/**
	 * Name space of the Domain Model of SfRegister
	 *
	 * @var string
	 */
	static protected $namespace = 'Tx_SfRegister_Domain_Model_';

	/**
	 * @return boolean
	 */
	static public function registerAutoloader() {
		return spl_autoload_register(__CLASS__ . '::autoload', TRUE, TRUE);
	}

	/**
	 * Loads php files containing classes or interfaces found in the classes directory of
	 * an extension.
	 *
	 * @param string $className: Name of the class/interface to load
	 * @return void
	 */
	static public function autoload($className) {
		$className = ltrim($className, '\\');
		if (strpos($className, static::$namespace) !== FALSE) {
				// Lookup the class in the array of register entities and check its presence in the class cache
			$entities = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][static::$extensionKey]['entities'];
			foreach ($entities as $entity => $entityExtensions) {
				if ($className === static::$namespace . $entity) {
					$entryIdentifier = 'DomainModel' . $entity;

					/** @var t3lib_cache_frontend_PhpFrontend $classCache */
					$classCache = self::initializeCache(SFREGISTERCACHEIDENTIFIER);

					if (!$classCache->has($entryIdentifier)) {
						/** @var Tx_SfRegister_Utility_ClassCacheManager $classCacheManager */
						$classCacheManager = t3lib_div::makeInstance('Tx_SfRegister_Utility_ClassCacheManager');
						$classCacheManager->reBuild();
					}

					$classCache->requireOnce($entryIdentifier);
					break;
				}
			}
		}
	}

	/**
	 * @param string $cacheIdentifier
	 * @return t3lib_cache_frontend_Frontend
	 */
	static public function initializeCache($cacheIdentifier) {
		try {
			/** @var t3lib_cache_Manager $cacheManager */
			$cacheManager = $GLOBALS['typo3CacheManager'];
			$cacheInstance = $cacheManager->getCache($cacheIdentifier);
		} catch (t3lib_cache_exception_NoSuchCache $e) {
			/** @var t3lib_cache_Factory $typo3CacheFactory */
			$typo3CacheFactory = $GLOBALS['typo3CacheFactory'];
			$cacheInstance = $typo3CacheFactory->create(
				$cacheIdentifier,
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['frontend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['backend'],
				$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$cacheIdentifier]['options']
			);
		}

		return $cacheInstance;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/sf_register/Classes/Utility/ClassLoader.php']) {
	/** @noinspection PhpIncludeInspection */
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/sf_register/Classes/Utility/ClassLoader.php']);
}

?>