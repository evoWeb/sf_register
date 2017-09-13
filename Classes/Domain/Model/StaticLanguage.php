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

    /**
     * Getter for ISO 639-1 A2 Language code
     *
     * @return string
     */
    public function getLgIso2()
    {
        return $this->lgIso2;
    }

    /**
     * Getter for Name
     *
     * @return string
     */
    public function getLgNameLocal()
    {
        return $this->lgNameLocal;
    }

    /**
     * Getter for Name (EN)
     *
     * @return string
     */
    public function getLgNameEn()
    {
        return $this->lgNameEn;
    }

    /**
     * Getter for Short name (CZ)
     *
     * @return string
     */
    public function getLgNameCz()
    {
        return $this->lgNameCz;
    }

    /**
     * Getter for Short name (DA)
     *
     * @return string
     */
    public function getLgNameDa()
    {
        return $this->lgNameDa;
    }

    /**
     * Getter for Short name (DE)
     *
     * @return string
     */
    public function getLgNameDe()
    {
        return $this->lgNameDe;
    }

    /**
     * Getter for Short name (ES)
     *
     * @return string
     */
    public function getLgNameEs()
    {
        return $this->lgNameEs;
    }

    /**
     * Getter for Short name (FR)
     *
     * @return string
     */
    public function getLgNameFr()
    {
        return $this->lgNameFr;
    }

    /**
     * Getter for Short name (GA)
     *
     * @return string
     */
    public function getLgNameGa()
    {
        return $this->lgNameGa;
    }

    /**
     * Getter for Short name (GL)
     *
     * @return string
     */
    public function getLgNameGl()
    {
        return $this->lgNameGl;
    }

    /**
     * Getter for Short name (IT)
     *
     * @return string
     */
    public function getLgNameIt()
    {
        return $this->lgNameIt;
    }

    /**
     * Getter for Short name (JA)
     *
     * @return string
     */
    public function getLgNameJa()
    {
        return $this->lgNameJa;
    }

    /**
     * Getter for Short name (KM)
     *
     * @return string
     */
    public function getLgNameKm()
    {
        return $this->lgNameKm;
    }

    /**
     * Getter for Short name (NL)
     *
     * @return string
     */
    public function getLgNameNl()
    {
        return $this->lgNameNl;
    }

    /**
     * Getter for Short name (NO)
     *
     * @return string
     */
    public function getLgNameNo()
    {
        return $this->lgNameNo;
    }

    /**
     * Getter for Short name (PL)
     *
     * @return string
     */
    public function getLgNamePl()
    {
        return $this->lgNamePl;
    }

    /**
     * Getter for Short name (PT)
     *
     * @return string
     */
    public function getLgNamePt()
    {
        return $this->lgNamePt;
    }

    /**
     * Getter for Short name (RO)
     *
     * @return string
     */
    public function getLgNameRo()
    {
        return $this->lgNameRo;
    }

    /**
     * Getter for Short name (RU)
     *
     * @return string
     */
    public function getLgNameRu()
    {
        return $this->lgNameRu;
    }

    /**
     * Getter for Short name (SK)
     *
     * @return string
     */
    public function getLgNameSk()
    {
        return $this->lgNameSk;
    }

    /**
     * Getter for Short name (SV)
     *
     * @return string
     */
    public function getLgNameSv()
    {
        return $this->lgNameSv;
    }

    /**
     * Getter for Short name (UA)
     *
     * @return string
     */
    public function getLgNameUa()
    {
        return $this->lgNameUa;
    }

    /**
     * Getter for Short name (ZH)
     *
     * @return string
     */
    public function getLgNameZh()
    {
        return $this->lgNameZh;
    }
}
