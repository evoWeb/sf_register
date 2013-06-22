<?php
namespace Evoweb\SfRegister\Domain\Model;
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
 * A static country zone
 */
class StaticCountryZone extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
	/**
	 * ISO 3166-1 A2 Country code
	 *
	 * @var string
	 */
	protected $znCountryIso2;

	/**
	 * ISO 3166-1 A3 Country code
	 *
	 * @var string
	 */
	protected $znCountryIso3;

	/**
	 * ISO 3166-1 Nr Country code
	 *
	 * @var string
	 */
	protected $znCountryIsoNr;

	/**
	 * ISO 3166-2 Country Zone code
	 *
	 * @var string
	 */
	protected $znCode;

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $znNameLocal;

	/**
	 * Name (EN)
	 *
	 * @var string
	 */
	protected $znNameEn;

	/**
	 * Name (DA)
	 *
	 * @var string
	 */
	protected $znNameDa;

	/**
	 * Name (DE)
	 *
	 * @var string
	 */
	protected $znNameDe;

	/**
	 * Name (ES)
	 *
	 * @var string
	 */
	protected $znNameEs;

	/**
	 * Name (FR)
	 *
	 * @var string
	 */
	protected $znNameFr;

	/**
	 * Name (GL)
	 *
	 * @var string
	 */
	protected $znNameGl;

	/**
	 * Name (IT)
	 *
	 * @var string
	 */
	protected $znNameIt;

	/**
	 * Name (JA)
	 *
	 * @var string
	 */
	protected $znNameJa;

	/**
	 * Name (KM)
	 *
	 * @var string
	 */
	protected $znNameKm;

	/**
	 * Name (NL)
	 *
	 * @var string
	 */
	protected $znNameNl;

	/**
	 * Name (NO)
	 *
	 * @var string
	 */
	protected $znNameNo;

	/**
	 * Name (RU)
	 *
	 * @var string
	 */
	protected $znNameRu;

	/**
	 * Name (SV)
	 *
	 * @var string
	 */
	protected $znNameSv;

	/**
	 * Name (UA)
	 *
	 * @var string
	 */
	protected $znNameUa;

	/**
	 * Getter for ISO 3166-1 A2 Country code
	 *
	 * @return string
	 */
	public function getZnCountryIso2() {
		return $this->znCountryIso2;
	}

	/**
	 * Getter for ISO 3166-1 A3 Country code
	 *
	 * @return string
	 */
	public function getZnCountryIso3() {
		return $this->znCountryIso3;
	}

	/**
	 * Getter for ISO 3166-1 Nr Country code
	 *
	 * @return string
	 */
	public function getZnCountryIsoNr() {
		return $this->znCountryIsoNr;
	}

	/**
	 * Getter for ISO 3166-2 Country Zone code
	 *
	 * @return string
	 */
	public function getZnCode() {
		return $this->znCode;
	}

	/**
	 * Getter for Name
	 *
	 * @return string
	 */
	public function getZnNameLocal() {
		return $this->znNameLocal;
	}

	/**
	 * Getter for Name (EN)
	 *
	 * @return string
	 */
	public function getZnNameEn() {
		return $this->znNameEn;
	}

	/**
	 * Getter for Name (DA)
	 *
	 * @return string
	 */
	public function getZnNameDa() {
		return $this->znNameDa;
	}

	/**
	 * Getter for Name (DE)
	 *
	 * @return string
	 */
	public function getZnNameDe() {
		return $this->znNameDe;
	}

	/**
	 * Getter for Name (ES)
	 *
	 * @return string
	 */
	public function getZnNameEs() {
		return $this->znNameEs;
	}

	/**
	 * Getter for Name (FR)
	 *
	 * @return string
	 */
	public function getZnNameFr() {
		return $this->znNameFr;
	}

	/**
	 * Getter for Name (GL)
	 *
	 * @return string
	 */
	public function getZnNameGl() {
		return $this->znNameGl;
	}

	/**
	 * Getter for Name (IT)
	 *
	 * @return string
	 */
	public function getZnNameIt() {
		return $this->znNameIt;
	}

	/**
	 * Getter for Name (JA)
	 *
	 * @return string
	 */
	public function getZnNameJa() {
		return $this->znNameJa;
	}

	/**
	 * Getter for Name (KM)
	 *
	 * @return string
	 */
	public function getZnNameKm() {
		return $this->znNameKm;
	}

	/**
	 * Getter for Name (NL)
	 *
	 * @return string
	 */
	public function getZnNameNl() {
		return $this->znNameNl;
	}

	/**
	 * Getter for Name (NO)
	 *
	 * @return string
	 */
	public function getZnNameNo() {
		return $this->znNameNo;
	}

	/**
	 * Getter for Name (RU)
	 *
	 * @return string
	 */
	public function getZnNameRu() {
		return $this->znNameRu;
	}

	/**
	 * Getter for Name (SV)
	 *
	 * @return string
	 */
	public function getZnNameSv() {
		return $this->znNameSv;
	}

	/**
	 * Getter for Name (UA)
	 *
	 * @return string
	 */
	public function getZnNameUa() {
		return $this->znNameUa;
	}
}

?>