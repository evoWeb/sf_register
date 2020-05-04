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
 * A static country
 */
class StaticCountry extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * ISO 3166-1 A2 Country code
     *
     * @var string
     */
    protected $cnIso2;

    /**
     * ISO 3166-1 A3 Country code
     *
     * @var string
     */
    protected $cnIso3;

    /**
     * ISO 3166-1 Nr Country code
     *
     * @var int
     */
    protected $cnIsoNr;

    /**
     * Official name (local)
     *
     * @var string
     */
    protected $cnOfficialNameLocal;

    /**
     * Official name (EN)
     *
     * @var string
     */
    protected $cnOfficialNameEn;

    /**
     * Short name local
     *
     * @var string
     */
    protected $cnShortLocal;

    /**
     * Short name (EN)
     *
     * @var string
     */
    protected $cnShortEn;

    /**
     * Short name (CZ)
     *
     * @var string
     */
    protected $cnShortCz;

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
     * Short name (GA)
     *
     * @var string
     */
    protected $cnShortGa;

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
     * Short name (PL)
     *
     * @var string
     */
    protected $cnShortPl;

    /**
     * Short name (PT)
     *
     * @var string
     */
    protected $cnShortPt;

    /**
     * Short name (RO)
     *
     * @var string
     */
    protected $cnShortRo;

    /**
     * Short name (RU)
     *
     * @var string
     */
    protected $cnShortRu;

    /**
     * Short name (SK)
     *
     * @var string
     */
    protected $cnShortSk;

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
     * Short name (ZH)
     *
     * @var string
     */
    protected $cnShortZh;

    /**
     * Getter for ISO 3166-1 A2 Country code
     *
     * @return string
     */
    public function getCnIso2(): string
    {
        return $this->cnIso2;
    }

    /**
     * Getter for ISO 3166-1 A3 Country code
     *
     * @return string
     */
    public function getCnIso3(): string
    {
        return $this->cnIso3;
    }

    /**
     * Getter for ISO 3166-1 Nr Country code
     *
     * @return int
     */
    public function getCnIsoNr(): int
    {
        return $this->cnIsoNr;
    }

    public function getCnOfficialNameLocal(): string
    {
        return $this->cnOfficialNameLocal;
    }

    public function getCnOfficialNameEn(): string
    {
        return $this->cnOfficialNameEn;
    }

    public function getCnShortEn(): string
    {
        return $this->cnShortEn;
    }

    public function getCnShortCz(): string
    {
        return $this->cnShortCz;
    }

    public function getCnShortDa(): string
    {
        return $this->cnShortDa;
    }

    public function getCnShortDe(): string
    {
        return $this->cnShortDe;
    }

    public function getCnShortEs(): string
    {
        return $this->cnShortEs;
    }

    public function getCnShortFr(): string
    {
        return $this->cnShortFr;
    }

    public function getCnShortGa(): string
    {
        return $this->cnShortGa;
    }

    public function getCnShortGl(): string
    {
        return $this->cnShortGl;
    }

    public function getCnShortIt(): string
    {
        return $this->cnShortIt;
    }

    public function getCnShortJa(): string
    {
        return $this->cnShortJa;
    }

    public function getCnShortKm(): string
    {
        return $this->cnShortKm;
    }

    public function getCnShortNl(): string
    {
        return $this->cnShortNl;
    }

    public function getCnShortNo(): string
    {
        return $this->cnShortNo;
    }

    public function getCnShortPl(): string
    {
        return $this->cnShortPl;
    }

    public function getCnShortPt(): string
    {
        return $this->cnShortPt;
    }

    public function getCnShortRo(): string
    {
        return $this->cnShortRo;
    }

    public function getCnShortRu(): string
    {
        return $this->cnShortRu;
    }

    public function getCnShortSk(): string
    {
        return $this->cnShortSk;
    }

    public function getCnShortSv(): string
    {
        return $this->cnShortSv;
    }

    public function getCnShortUa(): string
    {
        return $this->cnShortUa;
    }

    public function getCnShortZh(): string
    {
        return $this->cnShortZh;
    }

    public function getCnShortLocal(): string
    {
        return $this->cnShortLocal;
    }
}
