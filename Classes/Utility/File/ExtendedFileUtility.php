<?php
namespace Evoweb\SfRegister\Utility\File;
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
 * Extend of ExtenedFileUtility to have a public method for func_move
 */
class ExtendedFileUtility extends \TYPO3\CMS\Core\Utility\File\ExtendedFileUtility {
	/**
	 * @param array $commands
	 * @return \TYPO3\CMS\Core\Resource\File
	 */
	public function funcMove(array $commands) {
		return $this->func_move($commands);
	}
}

?>