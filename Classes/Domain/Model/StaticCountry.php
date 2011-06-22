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
 * A static country
 */
class Tx_SfRegister_Domain_Model_StaticCountry extends Tx_Extbase_DomainObject_AbstractEntity {
	/**
	 * @var 	string
	 */
	protected $cnIso2;

	/**
	 * @var 	string
	 */
	protected $cnIso3;

	/**
	 * @var 	integer
	 */
	protected $cnIsoNr;

	/**
	 * @var 	string
	 */
	protected $cnOfficialNameLocal;

	/**
	 * @var 	string
	 */
	protected $cnOfficialNameEn;

	/**
	 * @var string
	 */
	protected $cnShortEn;

	/**
	 * @var string
	 */
	protected $cnShortDa;

	/**
	 * @var string
	 */
	protected $cnShortDe;

	/**
	 * @var string
	 */
	protected $cnShortEs;

	/**
	 * @var string
	 */
	protected $cnShortFr;

	/**
	 * @var string
	 */
	protected $cnShortGl;

	/**
	 * @var string
	 */
	protected $cnShortIt;

	/**
	 * @var string
	 */
	protected $cnShortJa;

	/**
	 * @var string
	 */
	protected $cnShortKm;

	/**
	 * @var string
	 */
	protected $cnShortNl;

	/**
	 * @var string
	 */
	protected $cnShortNo;

	/**
	 * @var string
	 */
	protected $cnShortRu;

	/**
	 * @var string
	 */
	protected $cnShortSv;

	/**
	 * @var string
	 */
	protected $cnShortUa;

	/**
	 * @return string
	 */
	public function getCnIso2() {
		return $this->cnIso2;
	}

	/**
	 * @return string
	 */
	public function getCnIso3() {
		return $this->cnIso3;
	}

	/**
	 * @return string
	 */
	public function getCnIsoNr() {
		return $this->cnIsoNr;
	}

	/**
	 * @return string
	 */
	public function getCnOfficialNameLocal() {
		return $this->cnOfficialNameLocal;
	}

	/**
	 * @return string
	 */
	public function getCnOfficialNameEn() {
		return $this->cnOfficialNameEn;
	}

	/**
	 * @return string
	 */
	public function getCnShortEn() {
		return $this->cnShortEn;
	}

	/**
	 * @return string
	 */
	public function getCnShortDa() {
		return $this->cnShortDa;
	}

	/**
	 * @return string
	 */
	public function getCnShortDe() {
		return $this->cnShortDe;
	}

	/**
	 * @return string
	 */
	public function getCnShortEs() {
		return $this->cnShortEs;
	}

	/**
	 * @return string
	 */
	public function getCnShortFr() {
		return $this->cnShortFr;
	}

	/**
	 * @return string
	 */
	public function getCnShortGl() {
		return $this->cnShortGl;
	}

	/**
	 * @return string
	 */
	public function getCnShortIt() {
		return $this->cnShortIt;
	}

	/**
	 * @return string
	 */
	public function getCnShortJa() {
		return $this->cnShortJa;
	}

	/**
	 * @return string
	 */
	public function getCnShortKm() {
		return $this->cnShortKm;
	}

	/**
	 * @return string
	 */
	public function getCnShortNl() {
		return $this->cnShortNl;
	}

	/**
	 * @return string
	 */
	public function getCnShortNo() {
		return $this->cnShortNo;
	}

	/**
	 * @return string
	 */
	public function getCnShortRu() {
		return $this->cnShortRu;
	}

	/**
	 * @return string
	 */
	public function getCnShortSv() {
		return $this->cnShortSv;
	}

	/**
	 * @return string
	 */
	public function getCnShortUa() {
		return $this->cnShortUa;
	}
}

?>