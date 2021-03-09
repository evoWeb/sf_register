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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A static country
 */
class StaticCountry extends AbstractEntity
{
    /**
     * ISO 3166-1 A2 Country code
     *
     * @var string
     */
    protected string $cnIso2 = '';

    /**
     * ISO 3166-1 A3 Country code
     *
     * @var string
     */
    protected string $cnIso3 = '';

    /**
     * ISO 3166-1 Nr Country code
     *
     * @var int
     */
    protected int $cnIsoNr = 0;

    /**
     * Official name (local)
     *
     * @var string
     */
    protected string $cnOfficialNameLocal = '';

    /**
     * Official name (EN)
     *
     * @var string
     */
    protected string $cnOfficialNameEn = '';

    /**
     * Short name local
     *
     * @var string
     */
    protected string $cnShortLocal = '';

    /**
     * Short name (EN)
     *
     * @var string
     */
    protected string $cnShortEn = '';

    /**
     * Short name (CZ)
     *
     * @var string
     */
    protected string $cnShortCz = '';

    /**
     * Short name (DA)
     *
     * @var string
     */
    protected string $cnShortDa = '';

    /**
     * Short name (DE)
     *
     * @var string
     */
    protected string $cnShortDe = '';

    /**
     * Short name (ES)
     *
     * @var string
     */
    protected string $cnShortEs = '';

    /**
     * Short name (FR)
     *
     * @var string
     */
    protected string $cnShortFr = '';

    /**
     * Short name (GA)
     *
     * @var string
     */
    protected string $cnShortGa = '';

    /**
     * Short name (GL)
     *
     * @var string
     */
    protected string $cnShortGl = '';

    /**
     * Short name (IT)
     *
     * @var string
     */
    protected string $cnShortIt = '';

    /**
     * Short name (JA)
     *
     * @var string
     */
    protected string $cnShortJa = '';

    /**
     * Short name (KM)
     *
     * @var string
     */
    protected string $cnShortKm = '';

    /**
     * Short name (NL)
     *
     * @var string
     */
    protected string $cnShortNl = '';

    /**
     * Short name (NO)
     *
     * @var string
     */
    protected string $cnShortNo = '';

    /**
     * Short name (PL)
     *
     * @var string
     */
    protected string $cnShortPl = '';

    /**
     * Short name (PT)
     *
     * @var string
     */
    protected string $cnShortPt = '';

    /**
     * Short name (RO)
     *
     * @var string
     */
    protected string $cnShortRo = '';

    /**
     * Short name (RU)
     *
     * @var string
     */
    protected string $cnShortRu = '';

    /**
     * Short name (SK)
     *
     * @var string
     */
    protected string $cnShortSk = '';

    /**
     * Short name (SV)
     *
     * @var string
     */
    protected string $cnShortSv = '';

    /**
     * Short name (UA)
     *
     * @var string
     */
    protected string $cnShortUa = '';

    /**
     * Short name (ZH)
     *
     * @var string
     */
    protected string $cnShortZh = '';

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
