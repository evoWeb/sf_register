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
 * A static language
 */
class Tx_SfRegister_Domain_Model_StaticLanguage extends Tx_Extbase_DomainObject_AbstractEntity {
	/**
	 * @var string
	 */
	protected $lgIso2;

	/**
	 * @var string
	 */
	protected $lgNameLocal;

	/**
	 * @var string
	 */
	protected $lgNameEn;

	/**
	 * @var string
	 */
	protected $lgNameDe;

	/**
	 * @return string
	 */
	public function getLgIso2() {
		return $this->lgIso2;
	}

	/**
	 * @return string
	 */
	public function getLgNameLocal() {
		return $this->lgNameLocal;
	}

	/**
	 * @return string
	 */
	public function getLgNameEn() {
		return $this->lgNameEn;
	}

	/**
	 * @return string
	 */
	public function getLgNameDe() {
		return $this->lgNameDe;
	}
}

?>