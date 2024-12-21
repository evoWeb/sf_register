<?php

declare(strict_types=1);

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

namespace Evoweb\SfRegister\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A static country
 */
class StaticCountry extends AbstractEntity
{
    /**
     * ISO 3166-1 A2 Country code
     */
    protected string $cnIso2 = '';

    /**
     * ISO 3166-1 A3 Country code
     */
    protected string $cnIso3 = '';

    /**
     * ISO 3166-1 Nr Country code
     */
    protected int $cnIsoNr = 0;

    /**
     * Official name (local)
     */
    protected string $cnOfficialNameLocal = '';

    /**
     * Official name (EN)
     */
    protected string $cnOfficialNameEn = '';

    /**
     * Short name local
     */
    protected string $cnShortLocal = '';

    /**
     * Short name (EN)
     */
    protected string $cnShortEn = '';

    /**
     * Short name (CZ)
     */
    protected string $cnShortCz = '';

    /**
     * Short name (DA)
     */
    protected string $cnShortDa = '';

    /**
     * Short name (DE)
     */
    protected string $cnShortDe = '';

    /**
     * Short name (ES)
     */
    protected string $cnShortEs = '';

    /**
     * Short name (FR)
     */
    protected string $cnShortFr = '';

    /**
     * Short name (GA)
     */
    protected string $cnShortGa = '';

    /**
     * Short name (GL)
     */
    protected string $cnShortGl = '';

    /**
     * Short name (IT)
     */
    protected string $cnShortIt = '';

    /**
     * Short name (JA)
     */
    protected string $cnShortJa = '';

    /**
     * Short name (KM)
     */
    protected string $cnShortKm = '';

    /**
     * Short name (NL)
     */
    protected string $cnShortNl = '';

    /**
     * Short name (NO)
     */
    protected string $cnShortNo = '';

    /**
     * Short name (PL)
     */
    protected string $cnShortPl = '';

    /**
     * Short name (PT)
     */
    protected string $cnShortPt = '';

    /**
     * Short name (RO)
     */
    protected string $cnShortRo = '';

    /**
     * Short name (RU)
     */
    protected string $cnShortRu = '';

    /**
     * Short name (SK)
     */
    protected string $cnShortSk = '';

    /**
     * Short name (SV)
     */
    protected string $cnShortSv = '';

    /**
     * Short name (UA)
     */
    protected string $cnShortUa = '';

    /**
     * Short name (ZH)
     */
    protected string $cnShortZh = '';

    public function getCnIso2(): string
    {
        return $this->cnIso2;
    }

    public function setCnIso2(string $cnIso2): void
    {
        $this->cnIso2 = $cnIso2;
    }

    public function getCnIso3(): string
    {
        return $this->cnIso3;
    }

    public function setCnIso3(string $cnIso3): void
    {
        $this->cnIso3 = $cnIso3;
    }

    public function getCnIsoNr(): int
    {
        return $this->cnIsoNr;
    }

    public function setCnIsoNr(int $cnIsoNr): void
    {
        $this->cnIsoNr = $cnIsoNr;
    }

    public function getCnOfficialNameLocal(): string
    {
        return $this->cnOfficialNameLocal;
    }

    public function setCnOfficialNameLocal(string $cnOfficialNameLocal): void
    {
        $this->cnOfficialNameLocal = $cnOfficialNameLocal;
    }

    public function getCnOfficialNameEn(): string
    {
        return $this->cnOfficialNameEn;
    }

    public function setCnOfficialNameEn(string $cnOfficialNameEn): void
    {
        $this->cnOfficialNameEn = $cnOfficialNameEn;
    }

    public function getCnShortLocal(): string
    {
        return $this->cnShortLocal;
    }

    public function setCnShortLocal(string $cnShortLocal): void
    {
        $this->cnShortLocal = $cnShortLocal;
    }

    public function getCnShortEn(): string
    {
        return $this->cnShortEn;
    }

    public function setCnShortEn(string $cnShortEn): void
    {
        $this->cnShortEn = $cnShortEn;
    }

    public function getCnShortCz(): string
    {
        return $this->cnShortCz;
    }

    public function setCnShortCz(string $cnShortCz): void
    {
        $this->cnShortCz = $cnShortCz;
    }

    public function getCnShortDa(): string
    {
        return $this->cnShortDa;
    }

    public function setCnShortDa(string $cnShortDa): void
    {
        $this->cnShortDa = $cnShortDa;
    }

    public function getCnShortDe(): string
    {
        return $this->cnShortDe;
    }

    public function setCnShortDe(string $cnShortDe): void
    {
        $this->cnShortDe = $cnShortDe;
    }

    public function getCnShortEs(): string
    {
        return $this->cnShortEs;
    }

    public function setCnShortEs(string $cnShortEs): void
    {
        $this->cnShortEs = $cnShortEs;
    }

    public function getCnShortFr(): string
    {
        return $this->cnShortFr;
    }

    public function setCnShortFr(string $cnShortFr): void
    {
        $this->cnShortFr = $cnShortFr;
    }

    public function getCnShortGa(): string
    {
        return $this->cnShortGa;
    }

    public function setCnShortGa(string $cnShortGa): void
    {
        $this->cnShortGa = $cnShortGa;
    }

    public function getCnShortGl(): string
    {
        return $this->cnShortGl;
    }

    public function setCnShortGl(string $cnShortGl): void
    {
        $this->cnShortGl = $cnShortGl;
    }

    public function getCnShortIt(): string
    {
        return $this->cnShortIt;
    }

    public function setCnShortIt(string $cnShortIt): void
    {
        $this->cnShortIt = $cnShortIt;
    }

    public function getCnShortJa(): string
    {
        return $this->cnShortJa;
    }

    public function setCnShortJa(string $cnShortJa): void
    {
        $this->cnShortJa = $cnShortJa;
    }

    public function getCnShortKm(): string
    {
        return $this->cnShortKm;
    }

    public function setCnShortKm(string $cnShortKm): void
    {
        $this->cnShortKm = $cnShortKm;
    }

    public function getCnShortNl(): string
    {
        return $this->cnShortNl;
    }

    public function setCnShortNl(string $cnShortNl): void
    {
        $this->cnShortNl = $cnShortNl;
    }

    public function getCnShortNo(): string
    {
        return $this->cnShortNo;
    }

    public function setCnShortNo(string $cnShortNo): void
    {
        $this->cnShortNo = $cnShortNo;
    }

    public function getCnShortPl(): string
    {
        return $this->cnShortPl;
    }

    public function setCnShortPl(string $cnShortPl): void
    {
        $this->cnShortPl = $cnShortPl;
    }

    public function getCnShortPt(): string
    {
        return $this->cnShortPt;
    }

    public function setCnShortPt(string $cnShortPt): void
    {
        $this->cnShortPt = $cnShortPt;
    }

    public function getCnShortRo(): string
    {
        return $this->cnShortRo;
    }

    public function setCnShortRo(string $cnShortRo): void
    {
        $this->cnShortRo = $cnShortRo;
    }

    public function getCnShortRu(): string
    {
        return $this->cnShortRu;
    }

    public function setCnShortRu(string $cnShortRu): void
    {
        $this->cnShortRu = $cnShortRu;
    }

    public function getCnShortSk(): string
    {
        return $this->cnShortSk;
    }

    public function setCnShortSk(string $cnShortSk): void
    {
        $this->cnShortSk = $cnShortSk;
    }

    public function getCnShortSv(): string
    {
        return $this->cnShortSv;
    }

    public function setCnShortSv(string $cnShortSv): void
    {
        $this->cnShortSv = $cnShortSv;
    }

    public function getCnShortUa(): string
    {
        return $this->cnShortUa;
    }

    public function setCnShortUa(string $cnShortUa): void
    {
        $this->cnShortUa = $cnShortUa;
    }

    public function getCnShortZh(): string
    {
        return $this->cnShortZh;
    }

    public function setCnShortZh(string $cnShortZh): void
    {
        $this->cnShortZh = $cnShortZh;
    }
}
