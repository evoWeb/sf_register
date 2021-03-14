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
 * A static country zone
 */
class StaticCountryZone extends AbstractEntity
{
    /**
     * ISO 3166-1 A2 Country code
     *
     * @var string
     */
    protected string $znCountryIso2;

    /**
     * ISO 3166-1 A3 Country code
     *
     * @var string
     */
    protected string $znCountryIso3;

    /**
     * ISO 3166-1 Nr Country code
     *
     * @var int
     */
    protected int $znCountryIsoNr;

    /**
     * ISO 3166-2 Country Zone code
     *
     * @var string
     */
    protected string $znCode;

    /**
     * Name
     *
     * @var string
     */
    protected string $znNameLocal;

    /**
     * Name (EN)
     *
     * @var string
     */
    protected string $znNameEn;

    /**
     * Short name (CZ)
     *
     * @var string
     */
    protected string $znNameCz;

    /**
     * Short name (DA)
     *
     * @var string
     */
    protected string $znNameDa;

    /**
     * Short name (DE)
     *
     * @var string
     */
    protected string $znNameDe;

    /**
     * Short name (ES)
     *
     * @var string
     */
    protected string $znNameEs;

    /**
     * Short name (FR)
     *
     * @var string
     */
    protected string $znNameFr;

    /**
     * Short name (GA)
     *
     * @var string
     */
    protected string $znNameGa;

    /**
     * Short name (GL)
     *
     * @var string
     */
    protected string $znNameGl;

    /**
     * Short name (IT)
     *
     * @var string
     */
    protected string $znNameIt;

    /**
     * Short name (JA)
     *
     * @var string
     */
    protected string $znNameJa;

    /**
     * Short name (KM)
     *
     * @var string
     */
    protected string $znNameKm;

    /**
     * Short name (NL)
     *
     * @var string
     */
    protected string $znNameNl;

    /**
     * Short name (NO)
     *
     * @var string
     */
    protected string $znNameNo;

    /**
     * Short name (PL)
     *
     * @var string
     */
    protected string $znNamePl;

    /**
     * Short name (PT)
     *
     * @var string
     */
    protected string $znNamePt;

    /**
     * Short name (RO)
     *
     * @var string
     */
    protected string $znNameRo;

    /**
     * Short name (RU)
     *
     * @var string
     */
    protected string $znNameRu;

    /**
     * Short name (SK)
     *
     * @var string
     */
    protected string $znNameSk;

    /**
     * Short name (SV)
     *
     * @var string
     */
    protected string $znNameSv;

    /**
     * Short name (UA)
     *
     * @var string
     */
    protected string $znNameUa;

    /**
     * Short name (ZH)
     *
     * @var string
     */
    protected string $znNameZh;

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
