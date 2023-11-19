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
 * A static language
 */
class StaticLanguage extends AbstractEntity
{
    /**
     * ISO 639-1 A2 Language code
     */
    protected string $lgIso2;

    /**
     * Name
     */
    protected string $lgNameLocal;

    /**
     * Name (EN)
     */
    protected string $lgNameEn;

    /**
     * Short name (CZ)
     */
    protected string $lgNameCz;

    /**
     * Short name (DA)
     */
    protected string $lgNameDa;

    /**
     * Short name (DE)
     */
    protected string $lgNameDe;

    /**
     * Short name (ES)
     */
    protected string $lgNameEs;

    /**
     * Short name (FR)
     */
    protected string $lgNameFr;

    /**
     * Short name (GA)
     */
    protected string $lgNameGa;

    /**
     * Short name (GL)
     */
    protected string $lgNameGl;

    /**
     * Short name (IT)
     */
    protected string $lgNameIt;

    /**
     * Short name (JA)
     */
    protected string $lgNameJa;

    /**
     * Short name (KM)
     */
    protected string $lgNameKm;

    /**
     * Short name (NL)
     */
    protected string $lgNameNl;

    /**
     * Short name (NO)
     */
    protected string $lgNameNo;

    /**
     * Short name (PL)
     */
    protected string $lgNamePl;

    /**
     * Short name (PT)
     */
    protected string $lgNamePt;

    /**
     * Short name (RO)
     */
    protected string $lgNameRo;

    /**
     * Short name (RU)
     */
    protected string $lgNameRu;

    /**
     * Short name (SK)
     */
    protected string $lgNameSk;

    /**
     * Short name (SV)
     */
    protected string $lgNameSv;

    /**
     * Short name (UA)
     */
    protected string $lgNameUa;

    /**
     * Short name (ZH)
     */
    protected string $lgNameZh;

    public function getLgIso2(): string
    {
        return $this->lgIso2;
    }

    public function setLgIso2(string $lgIso2): void
    {
        $this->lgIso2 = $lgIso2;
    }

    public function getLgNameLocal(): string
    {
        return $this->lgNameLocal;
    }

    public function setLgNameLocal(string $lgNameLocal): void
    {
        $this->lgNameLocal = $lgNameLocal;
    }

    public function getLgNameEn(): string
    {
        return $this->lgNameEn;
    }

    public function setLgNameEn(string $lgNameEn): void
    {
        $this->lgNameEn = $lgNameEn;
    }

    public function getLgNameCz(): string
    {
        return $this->lgNameCz;
    }

    public function setLgNameCz(string $lgNameCz): void
    {
        $this->lgNameCz = $lgNameCz;
    }

    public function getLgNameDa(): string
    {
        return $this->lgNameDa;
    }

    public function setLgNameDa(string $lgNameDa): void
    {
        $this->lgNameDa = $lgNameDa;
    }

    public function getLgNameDe(): string
    {
        return $this->lgNameDe;
    }

    public function setLgNameDe(string $lgNameDe): void
    {
        $this->lgNameDe = $lgNameDe;
    }

    public function getLgNameEs(): string
    {
        return $this->lgNameEs;
    }

    public function setLgNameEs(string $lgNameEs): void
    {
        $this->lgNameEs = $lgNameEs;
    }

    public function getLgNameFr(): string
    {
        return $this->lgNameFr;
    }

    public function setLgNameFr(string $lgNameFr): void
    {
        $this->lgNameFr = $lgNameFr;
    }

    public function getLgNameGa(): string
    {
        return $this->lgNameGa;
    }

    public function setLgNameGa(string $lgNameGa): void
    {
        $this->lgNameGa = $lgNameGa;
    }

    public function getLgNameGl(): string
    {
        return $this->lgNameGl;
    }

    public function setLgNameGl(string $lgNameGl): void
    {
        $this->lgNameGl = $lgNameGl;
    }

    public function getLgNameIt(): string
    {
        return $this->lgNameIt;
    }

    public function setLgNameIt(string $lgNameIt): void
    {
        $this->lgNameIt = $lgNameIt;
    }

    public function getLgNameJa(): string
    {
        return $this->lgNameJa;
    }

    public function setLgNameJa(string $lgNameJa): void
    {
        $this->lgNameJa = $lgNameJa;
    }

    public function getLgNameKm(): string
    {
        return $this->lgNameKm;
    }

    public function setLgNameKm(string $lgNameKm): void
    {
        $this->lgNameKm = $lgNameKm;
    }

    public function getLgNameNl(): string
    {
        return $this->lgNameNl;
    }

    public function setLgNameNl(string $lgNameNl): void
    {
        $this->lgNameNl = $lgNameNl;
    }

    public function getLgNameNo(): string
    {
        return $this->lgNameNo;
    }

    public function setLgNameNo(string $lgNameNo): void
    {
        $this->lgNameNo = $lgNameNo;
    }

    public function getLgNamePl(): string
    {
        return $this->lgNamePl;
    }

    public function setLgNamePl(string $lgNamePl): void
    {
        $this->lgNamePl = $lgNamePl;
    }

    public function getLgNamePt(): string
    {
        return $this->lgNamePt;
    }

    public function setLgNamePt(string $lgNamePt): void
    {
        $this->lgNamePt = $lgNamePt;
    }

    public function getLgNameRo(): string
    {
        return $this->lgNameRo;
    }

    public function setLgNameRo(string $lgNameRo): void
    {
        $this->lgNameRo = $lgNameRo;
    }

    public function getLgNameRu(): string
    {
        return $this->lgNameRu;
    }

    public function setLgNameRu(string $lgNameRu): void
    {
        $this->lgNameRu = $lgNameRu;
    }

    public function getLgNameSk(): string
    {
        return $this->lgNameSk;
    }

    public function setLgNameSk(string $lgNameSk): void
    {
        $this->lgNameSk = $lgNameSk;
    }

    public function getLgNameSv(): string
    {
        return $this->lgNameSv;
    }

    public function setLgNameSv(string $lgNameSv): void
    {
        $this->lgNameSv = $lgNameSv;
    }

    public function getLgNameUa(): string
    {
        return $this->lgNameUa;
    }

    public function setLgNameUa(string $lgNameUa): void
    {
        $this->lgNameUa = $lgNameUa;
    }

    public function getLgNameZh(): string
    {
        return $this->lgNameZh;
    }

    public function setLgNameZh(string $lgNameZh): void
    {
        $this->lgNameZh = $lgNameZh;
    }
}
