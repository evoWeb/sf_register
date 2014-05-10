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

class ClassLoader extends \TYPO3\CMS\Core\Core\ClassLoader {
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
	static protected $namespace = 'Evoweb\\SfRegister\\Domain\\Model\\';

	/**
	 * @return boolean
	 */
	static public function registerAutoloader() {
		return spl_autoload_register(__CLASS__ . '::autoload', TRUE, TRUE);
	}

	/**
	 * Loads php files containing classes or interfaces part of the classes directory of an extension.
	 *
	 * @param string $className: Name of the class/interface to load
	 * @return void
	 */
	static public function autoload($className) {
		$className = ltrim($className, '\\');
		if (strpos($className, self::$namespace) !== FALSE) {
			if (
				isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][self::$extensionKey])
				&& isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][self::$extensionKey]['entities'])
				&& is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][self::$extensionKey]['entities'])
			) {
					// Lookup the class in the array of register entities and check its presence in the class cache
				$entities = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][self::$extensionKey]['entities'];
				foreach ($entities as $entity => $entityExtensions) {
					if ($className === self::$namespace . $entity) {
						$entryIdentifier = 'DomainModel' . $entity;

						$classCache = self::initializeCache(SFREGISTERCACHEIDENTIFIER);
						if (!$classCache->has($entryIdentifier)) {
							/** @var ClassCacheManager $classCacheManager */
							$classCacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Evoweb\\SfRegister\\Utility\\ClassCacheManager');
							$classCacheManager->reBuild();
						}

						$classCache->requireOnce($entryIdentifier);
						break;
					}
				}
			}
		}
	}

	/**
	 * @param string $cacheIdentifier
	 * @return \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend
	 */
	static public function initializeCache($cacheIdentifier) {
		try {
			/** @var \TYPO3\CMS\Core\Cache\CacheManager $cacheManager */
			$cacheManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
			$cacheInstance = $cacheManager->getCache($cacheIdentifier);
		} catch (\TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException $e) {
			/** @var \TYPO3\CMS\Core\Cache\CacheFactory $typo3CacheFactory */
			$typo3CacheFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheFactory');
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

?>