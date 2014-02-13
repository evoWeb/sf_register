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
 * Service to handle the hook calling
 */
class Tx_SfRegister_Services_Hook {
	/**
	 * Extension key
	 *
	 * @var string
	 */
	protected static $extension = 'sf_register';

	/**
	 * Process registered hooks
	 *
	 * @param string $hookName
	 * @return mixed
	 */
	public function process($hookName) {
		$result = func_get_arg(1);

		$hooks = self::getHooks(get_called_class());
		if (is_array($hooks[$hookName]) && count($hooks[$hookName])) {
			$userObjectReferences = $hooks[$hookName];
			$arguments = array_slice(func_get_args(), 2);

			foreach ($userObjectReferences as $userObjectReference) {
				$hookObj =& t3lib_div::getUserObj($userObjectReference);
				$result = $hookObj->{$hookName}($result, $arguments);
			}
		}

		return $result;
	}

	/**
	 * Get all registered hooks based on class or global scope
	 *
	 * @param string $className
	 * @return array
	 */
	protected function getHooks($className) {
		if ($className !== FALSE) {
			$hooks = (array) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][self::$extension][$className];
		} else {
			$hooks = (array) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][self::$extension];
		}

		return $hooks;
	}
}

?>