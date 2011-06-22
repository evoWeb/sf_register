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
 * A static country zone
 */
class Tx_SfRegister_Domain_Model_StaticCountryZone extends Tx_Extbase_DomainObject_AbstractEntity {
	/**
	 * @var string
	 */
	protected $znCountryIso2;

	/**
	 * @var string
	 */
	protected $znCountryIso3;

	/**
	 * @var string
	 */
	protected $znCountryIsoNr;

	/**
	 * @var string
	 */
	protected $znCode;

	/**
	 * @var string
	 */
	protected $znNameLocal;

	/**
	 * @var string
	 */
	protected $znNameEn;

	/**
	 * @var string
	 */
	protected $znNameDa;

	/**
	 * @var string
	 */
	protected $znNameDe;

	/**
	 * @var string
	 */
	protected $znNameEs;

	/**
	 * @var string
	 */
	protected $znNameFr;

	/**
	 * @var string
	 */
	protected $znNameGl;

	/**
	 * @var string
	 */
	protected $znNameIt;

	/**
	 * @var string
	 */
	protected $znNameJa;

	/**
	 * @var string
	 */
	protected $znNameKm;

	/**
	 * @var string
	 */
	protected $znNameNl;

	/**
	 * @var string
	 */
	protected $znNameNo;

	/**
	 * @var string
	 */
	protected $znNameRu;

	/**
	 * @var string
	 */
	protected $znNameSv;

	/**
	 * @var string
	 */
	protected $znNameUa;

	/**
	 * @return string
	 */
	public function getZnCountryIso2() {
		return $this->znCountryIso2;
	}

	/**
	 * @return string
	 */
	public function getZnCountryIso3() {
		return $this->znCountryIso3;
	}

	/**
	 * @return string
	 */
	public function getZnCountryIsoNr() {
		return $this->znCountryIsoNr;
	}

	/**
	 * @return string
	 */
	public function getZnCode() {
		return $this->znCode;
	}

	/**
	 * @return string
	 */
	public function getZnNameLocal() {
		return $this->znNameLocal;
	}

	/**
	 * @return string
	 */
	public function getZnNameEn() {
		return $this->znNameEn;
	}

	/**
	 * @return string
	 */
	public function getZnNameDa() {
		return $this->znNameDa;
	}

	/**
	 * @return string
	 */
	public function getZnNameDe() {
		return $this->znNameDe;
	}

	/**
	 * @return string
	 */
	public function getZnNameEs() {
		return $this->znNameEs;
	}

	/**
	 * @return string
	 */
	public function getZnNameFr() {
		return $this->znNameFr;
	}

	/**
	 * @return string
	 */
	public function getZnNameGl() {
		return $this->znNameGl;
	}

	/**
	 * @return string
	 */
	public function getZnNameIt() {
		return $this->znNameIt;
	}

	/**
	 * @return string
	 */
	public function getZnNameJa() {
		return $this->znNameJa;
	}

	/**
	 * @return string
	 */
	public function getZnNameKm() {
		return $this->znNameKm;
	}

	/**
	 * @return string
	 */
	public function getZnNameNl() {
		return $this->znNameNl;
	}

	/**
	 * @return string
	 */
	public function getZnNameNo() {
		return $this->znNameNo;
	}

	/**
	 * @return string
	 */
	public function getZnNameRu() {
		return $this->znNameRu;
	}

	/**
	 * @return string
	 */
	public function getZnNameSv() {
		return $this->znNameSv;
	}

	/**
	 * @return string
	 */
	public function getZnNameUa() {
		return $this->znNameUa;
	}
}

?>