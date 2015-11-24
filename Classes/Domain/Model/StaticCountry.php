<?php
namespace Evoweb\SfRegister\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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
 * A static country
 */
class StaticCountry extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * ISO 3166-1 A2 Country code
     *
     * @var    string
     */
    protected $cnIso2;

    /**
     * ISO 3166-1 A3 Country code
     *
     * @var    string
     */
    protected $cnIso3;

    /**
     * ISO 3166-1 Nr Country code
     *
     * @var    integer
     */
    protected $cnIsoNr;

    /**
     * Official name (local)
     *
     * @var    string
     */
    protected $cnOfficialNameLocal;

    /**
     * Official name (EN)
     *
     * @var    string
     */
    protected $cnOfficialNameEn;

    /**
     * Short name (EN)
     *
     * @var string
     */
    protected $cnShortEn;

    /**
     * Short name (DA)
     *
     * @var string
     */
    protected $cnShortDa;

    /**
     * Short name (DE)
     *
     * @var string
     */
    protected $cnShortDe;

    /**
     * Short name (ES)
     *
     * @var string
     */
    protected $cnShortEs;

    /**
     * Short name (FR)
     *
     * @var string
     */
    protected $cnShortFr;

    /**
     * Short name (GL)
     *
     * @var string
     */
    protected $cnShortGl;

    /**
     * Short name (IT)
     *
     * @var string
     */
    protected $cnShortIt;

    /**
     * Short name (JA)
     *
     * @var string
     */
    protected $cnShortJa;

    /**
     * Short name (KM)
     *
     * @var string
     */
    protected $cnShortKm;

    /**
     * Short name (NL)
     *
     * @var string
     */
    protected $cnShortNl;

    /**
     * Short name (NO)
     *
     * @var string
     */
    protected $cnShortNo;

    /**
     * Short name (RU)
     *
     * @var string
     */
    protected $cnShortRu;

    /**
     * Short name (SV)
     *
     * @var string
     */
    protected $cnShortSv;

    /**
     * Short name (UA)
     *
     * @var string
     */
    protected $cnShortUa;

    /**
     * Short name local
     *
     * @var string
     */
    protected $cnShortLocal;

    /**
     * Getter for ISO 3166-1 A2 Country code
     *
     * @return string
     */
    public function getCnIso2()
    {
        return $this->cnIso2;
    }

    /**
     * Getter for ISO 3166-1 A3 Country code
     *
     * @return string
     */
    public function getCnIso3()
    {
        return $this->cnIso3;
    }

    /**
     * Getter for ISO 3166-1 Nr Country code
     *
     * @return string
     */
    public function getCnIsoNr()
    {
        return $this->cnIsoNr;
    }

    /**
     * Getter for Official name (local)
     *
     * @return string
     */
    public function getCnOfficialNameLocal()
    {
        return $this->cnOfficialNameLocal;
    }

    /**
     * Getter for Official name (EN)
     *
     * @return string
     */
    public function getCnOfficialNameEn()
    {
        return $this->cnOfficialNameEn;
    }

    /**
     * Getter for Short name (EN)
     *
     * @return string
     */
    public function getCnShortEn()
    {
        return $this->cnShortEn;
    }

    /**
     * Getter for Short name (DA)
     *
     * @return string
     */
    public function getCnShortDa()
    {
        return $this->cnShortDa;
    }

    /**
     * Getter for Short name (DE)
     *
     * @return string
     */
    public function getCnShortDe()
    {
        return $this->cnShortDe;
    }

    /**
     * Getter for Short name (ES)
     *
     * @return string
     */
    public function getCnShortEs()
    {
        return $this->cnShortEs;
    }

    /**
     * Getter for Short name (FR)
     *
     * @return string
     */
    public function getCnShortFr()
    {
        return $this->cnShortFr;
    }

    /**
     * Getter for Short name (GL)
     *
     * @return string
     */
    public function getCnShortGl()
    {
        return $this->cnShortGl;
    }

    /**
     * Getter for Short name (IT)
     *
     * @return string
     */
    public function getCnShortIt()
    {
        return $this->cnShortIt;
    }

    /**
     * Getter for Short name (JA)
     *
     * @return string
     */
    public function getCnShortJa()
    {
        return $this->cnShortJa;
    }

    /**
     * Getter for Short name (KM)
     *
     * @return string
     */
    public function getCnShortKm()
    {
        return $this->cnShortKm;
    }

    /**
     * Getter for Short name (NL)
     *
     * @return string
     */
    public function getCnShortNl()
    {
        return $this->cnShortNl;
    }

    /**
     * Getter for Short name (NO)
     *
     * @return string
     */
    public function getCnShortNo()
    {
        return $this->cnShortNo;
    }

    /**
     * Getter for Short name (RU)
     *
     * @return string
     */
    public function getCnShortRu()
    {
        return $this->cnShortRu;
    }

    /**
     * Getter for Short name (SV)
     *
     * @return string
     */
    public function getCnShortSv()
    {
        return $this->cnShortSv;
    }

    /**
     * Getter for Short name (UA)
     *
     * @return string
     */
    public function getCnShortUa()
    {
        return $this->cnShortUa;
    }

    /**
     * Set cnShortLocal
     *
     * @param string $cnShortLocal
     * @return void
     */
    public function setCnShortLocal($cnShortLocal)
    {
        $this->cnShortLocal = $cnShortLocal;
    }

    /**
     * Get cnShortLocal
     *
     * @return string
     */
    public function getCnShortLocal()
    {
        return $this->cnShortLocal;
    }
}
