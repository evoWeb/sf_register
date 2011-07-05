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
	 * ISO 639-1 A2 Language code
	 *
	 * @var string
	 */
	protected $lgIso2;

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $lgNameLocal;

	/**
	 * Name (EN)
	 *
	 * @var string
	 */
	protected $lgNameEn;

	/**
	 * Name (DA)
	 *
	 * @var string
	 */
	protected $lgNameDa;

	/**
	 * Name (DE)
	 *
	 * @var string
	 */
	protected $lgNameDe;

	/**
	 * Name (ES)
	 *
	 * @var string
	 */
	protected $lgNameEs;

	/**
	 * Name (FR)
	 *
	 * @var string
	 */
	protected $lgNameFr;

	/**
	 * Name (GL)
	 *
	 * @var string
	 */
	protected $lgNameGl;

	/**
	 * Name (IT)
	 *
	 * @var string
	 */
	protected $lgNameIt;

	/**
	 * Name (JA)
	 *
	 * @var string
	 */
	protected $lgNameJa;

	/**
	 * Name (KM)
	 *
	 * @var string
	 */
	protected $lgNameKm;

	/**
	 * Name (NL)
	 *
	 * @var string
	 */
	protected $lgNameNl;

	/**
	 * Name (NO)
	 *
	 * @var string
	 */
	protected $lgNameNo;

	/**
	 * Name (RU)
	 *
	 * @var string
	 */
	protected $lgNameRu;

	/**
	 * Name (SV)
	 *
	 * @var string
	 */
	protected $lgNameSv;

	/**
	 * Name (UA)
	 * 
	 * @var string
	 */
	protected $lgNameUa;

	/**
	 * Getter for ISO 639-1 A2 Language code
	 *
	 * @return string
	 */
	public function getLgIso2() {
		return $this->lgIso2;
	}

	/**
	 * Getter for Name
	 *
	 * @return string
	 */
	public function getLgNameLocal() {
		return $this->lgNameLocal;
	}

	/**
	 * Getter for Name (EN)
	 *
	 * @return string
	 */
	public function getLgNameEn() {
		return $this->lgNameEn;
	}

	/**
	 * Getter for Name (DA)
	 *
	 * @return string
	 */
	public function getLgNameDa() {
		return $this->lgNameDa;
	}

	/**
	 * Getter for Name (DE)
	 *
	 * @return string
	 */
	public function getLgNameDe() {
		return $this->lgNameDe;
	}

	/**
	 * Getter for Name (ES)
	 *
	 * @return string
	 */
	public function getLgNameEs() {
		return $this->lgNameEs;
	}

	/**
	 * Getter for Name (FR)
	 *
	 * @return string
	 */
	public function getLgNameFr() {
		return $this->lgNameFr;
	}

	/**
	 * Getter for Name (GL)
	 *
	 * @return string
	 */
	public function getLgNameGl() {
		return $this->lgNameGl;
	}

	/**
	 * Getter for Name (IT)
	 *
	 * @return string
	 */
	public function getLgNameIt() {
		return $this->lgNameIt;
	}

	/**
	 * Getter for Name (JA)
	 *
	 * @return string
	 */
	public function getLgNameJa() {
		return $this->lgNameJa;
	}

	/**
	 * Getter for Name (KM)
	 *
	 * @return string
	 */
	public function getLgNameKm() {
		return $this->lgNameKm;
	}

	/**
	 * Getter for Name (NL)
	 *
	 * @return string
	 */
	public function getLgNameNl() {
		return $this->lgNameNl;
	}

	/**
	 * Getter for Name (NO)
	 *
	 * @return string
	 */
	public function getLgNameNo() {
		return $this->lgNameNo;
	}

	/**
	 * Getter for Name (RU)
	 *
	 * @return string
	 */
	public function getLgNameRu() {
		return $this->lgNameRu;
	}

	/**
	 * Getter for Name (SV)
	 *
	 * @return string
	 */
	public function getLgNameSv() {
		return $this->lgNameSv;
	}

	/**
	 * Getter for Name (UA)
	 *
	 * @return string
	 */
	public function getLgNameUa() {
		return $this->lgNameUa;
	}
}

?>