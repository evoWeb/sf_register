<?php

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
 * A static country zone
 */
class StaticCountryZone extends AbstractEntity
{
    /**
     * ISO 3166-1 A2 Country code
     */
    protected string $znCountryIso2;

    /**
     * ISO 3166-1 A3 Country code
     */
    protected string $znCountryIso3;

    /**
     * ISO 3166-1 Nr Country code
     */
    protected int $znCountryIsoNr;

    /**
     * ISO 3166-2 Country Zone code
     */
    protected string $znCode;

    /**
     * Name
     */
    protected string $znNameLocal;

    /**
     * Name (EN)
     */
    protected string $znNameEn;

    /**
     * Short name (CZ)
     */
    protected string $znNameCz;

    /**
     * Short name (DA)
     */
    protected string $znNameDa;

    /**
     * Short name (DE)
     */
    protected string $znNameDe;

    /**
     * Short name (ES)
     */
    protected string $znNameEs;

    /**
     * Short name (FR)
     */
    protected string $znNameFr;

    /**
     * Short name (GA)
     */
    protected string $znNameGa;

    /**
     * Short name (GL)
     */
    protected string $znNameGl;

    /**
     * Short name (IT)
     */
    protected string $znNameIt;

    /**
     * Short name (JA)
     */
    protected string $znNameJa;

    /**
     * Short name (KM)
     */
    protected string $znNameKm;

    /**
     * Short name (NL)
     */
    protected string $znNameNl;

    /**
     * Short name (NO)
     */
    protected string $znNameNo;

    /**
     * Short name (PL)
     */
    protected string $znNamePl;

    /**
     * Short name (PT)
     */
    protected string $znNamePt;

    /**
     * Short name (RO)
     */
    protected string $znNameRo;

    /**
     * Short name (RU)
     */
    protected string $znNameRu;

    /**
     * Short name (SK)
     */
    protected string $znNameSk;

    /**
     * Short name (SV)
     */
    protected string $znNameSv;

    /**
     * Short name (UA)
     */
    protected string $znNameUa;

    /**
     * Short name (ZH)
     */
    protected string $znNameZh;

    public function getZnCountryIso2(): string
    {
        return $this->znCountryIso2;
    }

    public function setZnCountryIso2(string $znCountryIso2): void
    {
        $this->znCountryIso2 = $znCountryIso2;
    }

    public function getZnCountryIso3(): string
    {
        return $this->znCountryIso3;
    }

    public function setZnCountryIso3(string $znCountryIso3): void
    {
        $this->znCountryIso3 = $znCountryIso3;
    }

    public function getZnCountryIsoNr(): int
    {
        return $this->znCountryIsoNr;
    }

    public function setZnCountryIsoNr(int $znCountryIsoNr): void
    {
        $this->znCountryIsoNr = $znCountryIsoNr;
    }

    public function getZnCode(): string
    {
        return $this->znCode;
    }

    public function setZnCode(string $znCode): void
    {
        $this->znCode = $znCode;
    }

    public function getZnNameLocal(): string
    {
        return $this->znNameLocal;
    }

    public function setZnNameLocal(string $znNameLocal): void
    {
        $this->znNameLocal = $znNameLocal;
    }

    public function getZnNameEn(): string
    {
        return $this->znNameEn;
    }

    public function setZnNameEn(string $znNameEn): void
    {
        $this->znNameEn = $znNameEn;
    }

    public function getZnNameCz(): string
    {
        return $this->znNameCz;
    }

    public function setZnNameCz(string $znNameCz): void
    {
        $this->znNameCz = $znNameCz;
    }

    public function getZnNameDa(): string
    {
        return $this->znNameDa;
    }

    public function setZnNameDa(string $znNameDa): void
    {
        $this->znNameDa = $znNameDa;
    }

    public function getZnNameDe(): string
    {
        return $this->znNameDe;
    }

    public function setZnNameDe(string $znNameDe): void
    {
        $this->znNameDe = $znNameDe;
    }

    public function getZnNameEs(): string
    {
        return $this->znNameEs;
    }

    public function setZnNameEs(string $znNameEs): void
    {
        $this->znNameEs = $znNameEs;
    }

    public function getZnNameFr(): string
    {
        return $this->znNameFr;
    }

    public function setZnNameFr(string $znNameFr): void
    {
        $this->znNameFr = $znNameFr;
    }

    public function getZnNameGa(): string
    {
        return $this->znNameGa;
    }

    public function setZnNameGa(string $znNameGa): void
    {
        $this->znNameGa = $znNameGa;
    }

    public function getZnNameGl(): string
    {
        return $this->znNameGl;
    }

    public function setZnNameGl(string $znNameGl): void
    {
        $this->znNameGl = $znNameGl;
    }

    public function getZnNameIt(): string
    {
        return $this->znNameIt;
    }

    public function setZnNameIt(string $znNameIt): void
    {
        $this->znNameIt = $znNameIt;
    }

    public function getZnNameJa(): string
    {
        return $this->znNameJa;
    }

    public function setZnNameJa(string $znNameJa): void
    {
        $this->znNameJa = $znNameJa;
    }

    public function getZnNameKm(): string
    {
        return $this->znNameKm;
    }

    public function setZnNameKm(string $znNameKm): void
    {
        $this->znNameKm = $znNameKm;
    }

    public function getZnNameNl(): string
    {
        return $this->znNameNl;
    }

    public function setZnNameNl(string $znNameNl): void
    {
        $this->znNameNl = $znNameNl;
    }

    public function getZnNameNo(): string
    {
        return $this->znNameNo;
    }

    public function setZnNameNo(string $znNameNo): void
    {
        $this->znNameNo = $znNameNo;
    }

    public function getZnNamePl(): string
    {
        return $this->znNamePl;
    }

    public function setZnNamePl(string $znNamePl): void
    {
        $this->znNamePl = $znNamePl;
    }

    public function getZnNamePt(): string
    {
        return $this->znNamePt;
    }

    public function setZnNamePt(string $znNamePt): void
    {
        $this->znNamePt = $znNamePt;
    }

    public function getZnNameRo(): string
    {
        return $this->znNameRo;
    }

    public function setZnNameRo(string $znNameRo): void
    {
        $this->znNameRo = $znNameRo;
    }

    public function getZnNameRu(): string
    {
        return $this->znNameRu;
    }

    public function setZnNameRu(string $znNameRu): void
    {
        $this->znNameRu = $znNameRu;
    }

    public function getZnNameSk(): string
    {
        return $this->znNameSk;
    }

    public function setZnNameSk(string $znNameSk): void
    {
        $this->znNameSk = $znNameSk;
    }

    public function getZnNameSv(): string
    {
        return $this->znNameSv;
    }

    public function setZnNameSv(string $znNameSv): void
    {
        $this->znNameSv = $znNameSv;
    }

    public function getZnNameUa(): string
    {
        return $this->znNameUa;
    }

    public function setZnNameUa(string $znNameUa): void
    {
        $this->znNameUa = $znNameUa;
    }

    public function getZnNameZh(): string
    {
        return $this->znNameZh;
    }

    public function setZnNameZh(string $znNameZh): void
    {
        $this->znNameZh = $znNameZh;
    }
}
