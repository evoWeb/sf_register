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
	protected $lgNameDa;

	/**
	 * @var string
	 */
	protected $lgNameDe;

	/**
	 * @var string
	 */
	protected $lgNameEs;

	/**
	 * @var string
	 */
	protected $lgNameFr;

	/**
	 * @var string
	 */
	protected $lgNameGl;

	/**
	 * @var string
	 */
	protected $lgNameIt;

	/**
	 * @var string
	 */
	protected $lgNameJa;

	/**
	 * @var string
	 */
	protected $lgNameKm;

	/**
	 * @var string
	 */
	protected $lgNameNl;

	/**
	 * @var string
	 */
	protected $lgNameNo;

	/**
	 * @var string
	 */
	protected $lgNameRu;

	/**
	 * @var string
	 */
	protected $lgNameSv;

	/**
	 * @var string
	 */
	protected $lgNameUa;

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
	public function getLgNameDa() {
		return $this->lgNameDa;
	}

	/**
	 * @return string
	 */
	public function getLgNameDe() {
		return $this->lgNameDe;
	}

	/**
	 * @return string
	 */
	public function getLgNameEs() {
		return $this->lgNameEs;
	}

	/**
	 * @return string
	 */
	public function getLgNameFr() {
		return $this->lgNameFr;
	}

	/**
	 * @return string
	 */
	public function getLgNameGl() {
		return $this->lgNameGl;
	}

	/**
	 * @return string
	 */
	public function getLgNameIt() {
		return $this->lgNameIt;
	}

	/**
	 * @return string
	 */
	public function getLgNameJa() {
		return $this->lgNameJa;
	}

	/**
	 * @return string
	 */
	public function getLgNameKm() {
		return $this->lgNameKm;
	}

	/**
	 * @return string
	 */
	public function getLgNameNl() {
		return $this->lgNameNl;
	}

	/**
	 * @return string
	 */
	public function getLgNameNo() {
		return $this->lgNameNo;
	}

	/**
	 * @return string
	 */
	public function getLgNameRu() {
		return $this->lgNameRu;
	}

	/**
	 * @return string
	 */
	public function getLgNameSv() {
		return $this->lgNameSv;
	}

	/**
	 * @return string
	 */
	public function getLgNameUa() {
		return $this->lgNameUa;
	}
}

?>