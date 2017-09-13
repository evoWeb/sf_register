<?php
namespace Evoweb\SfRegister\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-17 Sebastian Fischer <typo3@evoweb.de>
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
class StaticCountryZone extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
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
     * Short name (CZ)
     *
     * @var string
     */
    protected $znNameCz;

    /**
     * Short name (DA)
     *
     * @var string
     */
    protected $znNameDa;

    /**
     * Short name (DE)
     *
     * @var string
     */
    protected $znNameDe;

    /**
     * Short name (ES)
     *
     * @var string
     */
    protected $znNameEs;

    /**
     * Short name (FR)
     *
     * @var string
     */
    protected $znNameFr;

    /**
     * Short name (GA)
     *
     * @var string
     */
    protected $znNameGa;

    /**
     * Short name (GL)
     *
     * @var string
     */
    protected $znNameGl;

    /**
     * Short name (IT)
     *
     * @var string
     */
    protected $znNameIt;

    /**
     * Short name (JA)
     *
     * @var string
     */
    protected $znNameJa;

    /**
     * Short name (KM)
     *
     * @var string
     */
    protected $znNameKm;

    /**
     * Short name (NL)
     *
     * @var string
     */
    protected $znNameNl;

    /**
     * Short name (NO)
     *
     * @var string
     */
    protected $znNameNo;

    /**
     * Short name (PL)
     *
     * @var string
     */
    protected $znNamePl;

    /**
     * Short name (PT)
     *
     * @var string
     */
    protected $znNamePt;

    /**
     * Short name (RO)
     *
     * @var string
     */
    protected $znNameRo;

    /**
     * Short name (RU)
     *
     * @var string
     */
    protected $znNameRu;

    /**
     * Short name (SK)
     *
     * @var string
     */
    protected $znNameSk;

    /**
     * Short name (SV)
     *
     * @var string
     */
    protected $znNameSv;

    /**
     * Short name (UA)
     *
     * @var string
     */
    protected $znNameUa;

    /**
     * Short name (ZH)
     *
     * @var string
     */
    protected $znNameZh;

    /**
     * Getter for ISO 3166-1 A2 Country code
     *
     * @return string
     */
    public function getZnCountryIso2()
    {
        return $this->znCountryIso2;
    }

    /**
     * Getter for ISO 3166-1 A3 Country code
     *
     * @return string
     */
    public function getZnCountryIso3()
    {
        return $this->znCountryIso3;
    }

    /**
     * Getter for ISO 3166-1 Nr Country code
     *
     * @return string
     */
    public function getZnCountryIsoNr()
    {
        return $this->znCountryIsoNr;
    }

    /**
     * Getter for ISO 3166-2 Country Zone code
     *
     * @return string
     */
    public function getZnCode()
    {
        return $this->znCode;
    }

    /**
     * Getter for Name
     *
     * @return string
     */
    public function getZnNameLocal()
    {
        return $this->znNameLocal;
    }

    /**
     * Getter for Name (EN)
     *
     * @return string
     */
    public function getZnNameEn()
    {
        return $this->znNameEn;
    }

    /**
     * Getter for Short name (CZ)
     *
     * @return string
     */
    public function getZnNameCz()
    {
        return $this->znNameCz;
    }

    /**
     * Getter for Short name (DA)
     *
     * @return string
     */
    public function getZnNameDa()
    {
        return $this->znNameDa;
    }

    /**
     * Getter for Short name (DE)
     *
     * @return string
     */
    public function getZnNameDe()
    {
        return $this->znNameDe;
    }

    /**
     * Getter for Short name (ES)
     *
     * @return string
     */
    public function getZnNameEs()
    {
        return $this->znNameEs;
    }

    /**
     * Getter for Short name (FR)
     *
     * @return string
     */
    public function getZnNameFr()
    {
        return $this->znNameFr;
    }

    /**
     * Getter for Short name (GA)
     *
     * @return string
     */
    public function getZnNameGa()
    {
        return $this->znNameGa;
    }

    /**
     * Getter for Short name (GL)
     *
     * @return string
     */
    public function getZnNameGl()
    {
        return $this->znNameGl;
    }

    /**
     * Getter for Short name (IT)
     *
     * @return string
     */
    public function getZnNameIt()
    {
        return $this->znNameIt;
    }

    /**
     * Getter for Short name (JA)
     *
     * @return string
     */
    public function getZnNameJa()
    {
        return $this->znNameJa;
    }

    /**
     * Getter for Short name (KM)
     *
     * @return string
     */
    public function getZnNameKm()
    {
        return $this->znNameKm;
    }

    /**
     * Getter for Short name (NL)
     *
     * @return string
     */
    public function getZnNameNl()
    {
        return $this->znNameNl;
    }

    /**
     * Getter for Short name (NO)
     *
     * @return string
     */
    public function getZnNameNo()
    {
        return $this->znNameNo;
    }

    /**
     * Getter for Short name (PL)
     *
     * @return string
     */
    public function getZnNamePl()
    {
        return $this->znNamePl;
    }

    /**
     * Getter for Short name (PT)
     *
     * @return string
     */
    public function getZnNamePt()
    {
        return $this->znNamePt;
    }

    /**
     * Getter for Short name (RO)
     *
     * @return string
     */
    public function getZnNameRo()
    {
        return $this->znNameRo;
    }

    /**
     * Getter for Short name (RU)
     *
     * @return string
     */
    public function getZnNameRu()
    {
        return $this->znNameRu;
    }

    /**
     * Getter for Short name (SK)
     *
     * @return string
     */
    public function getZnNameSk()
    {
        return $this->znNameSk;
    }

    /**
     * Getter for Short name (SV)
     *
     * @return string
     */
    public function getZnNameSv()
    {
        return $this->znNameSv;
    }

    /**
     * Getter for Short name (UA)
     *
     * @return string
     */
    public function getZnNameUa()
    {
        return $this->znNameUa;
    }

    /**
     * Getter for Short name (ZH)
     *
     * @return string
     */
    public function getZnNameZh()
    {
        return $this->znNameZh;
    }
}
