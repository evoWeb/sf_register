<?php

namespace Evoweb\SfRegister\Domain\Model;

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

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
     * @var int
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
    public function getZnCountryIso2(): string
    {
        return $this->znCountryIso2;
    }

    /**
     * Getter for ISO 3166-1 A3 Country code
     *
     * @return string
     */
    public function getZnCountryIso3(): string
    {
        return $this->znCountryIso3;
    }

    /**
     * Getter for ISO 3166-1 Nr Country code
     *
     * @return int
     */
    public function getZnCountryIsoNr(): int
    {
        return $this->znCountryIsoNr;
    }

    public function getZnCode(): string
    {
        return $this->znCode;
    }

    public function getZnNameLocal(): string
    {
        return $this->znNameLocal;
    }

    public function getZnNameEn(): string
    {
        return $this->znNameEn;
    }

    public function getZnNameCz(): string
    {
        return $this->znNameCz;
    }

    public function getZnNameDa(): string
    {
        return $this->znNameDa;
    }

    public function getZnNameDe(): string
    {
        return $this->znNameDe;
    }

    public function getZnNameEs(): string
    {
        return $this->znNameEs;
    }

    public function getZnNameFr(): string
    {
        return $this->znNameFr;
    }

    public function getZnNameGa(): string
    {
        return $this->znNameGa;
    }

    public function getZnNameGl(): string
    {
        return $this->znNameGl;
    }

    public function getZnNameIt(): string
    {
        return $this->znNameIt;
    }

    public function getZnNameJa(): string
    {
        return $this->znNameJa;
    }

    public function getZnNameKm(): string
    {
        return $this->znNameKm;
    }

    public function getZnNameNl(): string
    {
        return $this->znNameNl;
    }

    public function getZnNameNo(): string
    {
        return $this->znNameNo;
    }

    public function getZnNamePl(): string
    {
        return $this->znNamePl;
    }

    public function getZnNamePt(): string
    {
        return $this->znNamePt;
    }

    public function getZnNameRo(): string
    {
        return $this->znNameRo;
    }

    public function getZnNameRu(): string
    {
        return $this->znNameRu;
    }

    public function getZnNameSk(): string
    {
        return $this->znNameSk;
    }

    public function getZnNameSv(): string
    {
        return $this->znNameSv;
    }

    public function getZnNameUa(): string
    {
        return $this->znNameUa;
    }

    public function getZnNameZh(): string
    {
        return $this->znNameZh;
    }
}
