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
 * A static language
 */
class StaticLanguage extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * ISO 639-1 A2 Language code
     *
     * @var string
     */
    protected $lgIso2;

    /**
     * Name
     *
     * @var string
     */
    protected $lgNameLocal;

    /**
     * Name (EN)
     *
     * @var string
     */
    protected $lgNameEn;

    /**
     * Short name (CZ)
     *
     * @var string
     */
    protected $lgNameCz;

    /**
     * Short name (DA)
     *
     * @var string
     */
    protected $lgNameDa;

    /**
     * Short name (DE)
     *
     * @var string
     */
    protected $lgNameDe;

    /**
     * Short name (ES)
     *
     * @var string
     */
    protected $lgNameEs;

    /**
     * Short name (FR)
     *
     * @var string
     */
    protected $lgNameFr;

    /**
     * Short name (GA)
     *
     * @var string
     */
    protected $lgNameGa;

    /**
     * Short name (GL)
     *
     * @var string
     */
    protected $lgNameGl;

    /**
     * Short name (IT)
     *
     * @var string
     */
    protected $lgNameIt;

    /**
     * Short name (JA)
     *
     * @var string
     */
    protected $lgNameJa;

    /**
     * Short name (KM)
     *
     * @var string
     */
    protected $lgNameKm;

    /**
     * Short name (NL)
     *
     * @var string
     */
    protected $lgNameNl;

    /**
     * Short name (NO)
     *
     * @var string
     */
    protected $lgNameNo;

    /**
     * Short name (PL)
     *
     * @var string
     */
    protected $lgNamePl;

    /**
     * Short name (PT)
     *
     * @var string
     */
    protected $lgNamePt;

    /**
     * Short name (RO)
     *
     * @var string
     */
    protected $lgNameRo;

    /**
     * Short name (RU)
     *
     * @var string
     */
    protected $lgNameRu;

    /**
     * Short name (SK)
     *
     * @var string
     */
    protected $lgNameSk;

    /**
     * Short name (SV)
     *
     * @var string
     */
    protected $lgNameSv;

    /**
     * Short name (UA)
     *
     * @var string
     */
    protected $lgNameUa;

    /**
     * Short name (ZH)
     *
     * @var string
     */
    protected $lgNameZh;

    public function getLgIso2(): string
    {
        return $this->lgIso2;
    }

    public function getLgNameLocal(): string
    {
        return $this->lgNameLocal;
    }

    public function getLgNameEn(): string
    {
        return $this->lgNameEn;
    }

    public function getLgNameCz(): string
    {
        return $this->lgNameCz;
    }

    public function getLgNameDa(): string
    {
        return $this->lgNameDa;
    }

    public function getLgNameDe(): string
    {
        return $this->lgNameDe;
    }

    public function getLgNameEs(): string
    {
        return $this->lgNameEs;
    }

    public function getLgNameFr(): string
    {
        return $this->lgNameFr;
    }

    public function getLgNameGa(): string
    {
        return $this->lgNameGa;
    }

    public function getLgNameGl(): string
    {
        return $this->lgNameGl;
    }

    public function getLgNameIt(): string
    {
        return $this->lgNameIt;
    }

    public function getLgNameJa(): string
    {
        return $this->lgNameJa;
    }

    public function getLgNameKm(): string
    {
        return $this->lgNameKm;
    }

    public function getLgNameNl(): string
    {
        return $this->lgNameNl;
    }

    public function getLgNameNo(): string
    {
        return $this->lgNameNo;
    }

    public function getLgNamePl(): string
    {
        return $this->lgNamePl;
    }

    public function getLgNamePt(): string
    {
        return $this->lgNamePt;
    }

    public function getLgNameRo(): string
    {
        return $this->lgNameRo;
    }

    public function getLgNameRu(): string
    {
        return $this->lgNameRu;
    }

    public function getLgNameSk(): string
    {
        return $this->lgNameSk;
    }

    public function getLgNameSv(): string
    {
        return $this->lgNameSv;
    }

    public function getLgNameUa(): string
    {
        return $this->lgNameUa;
    }

    public function getLgNameZh(): string
    {
        return $this->lgNameZh;
    }
}
