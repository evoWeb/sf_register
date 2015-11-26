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

use Evoweb\SfRegister\Interfaces\FrontendUserInterface;

/**
 * An extended frontend user with more attributes
 */
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser implements FrontendUserInterface
{
    /**
     * If the account is disabled or not
     *
     * @var boolean
     */
    protected $disable;

    /**
     * Mailhash for activation by email
     *
     * @var string
     */
    protected $mailhash;

    /**
     * Date on which the account was activated
     *
     * @var \DateTime|NULL
     */
    protected $activatedOn;

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected $captcha;

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected $passwordRepeat;

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected $emailRepeat;

    /**
     * Pseudonym
     *
     * @var string
     */
    protected $pseudonym;

    /**
     * Gender 1 or 2 for mr or mrs
     *
     * @var integer
     */
    protected $gender;

    /**
     * Date of birth
     *
     * @var \DateTime
     */
    protected $dateOfBirth;

    /**
     * Day of date of birth
     *
     * @var integer
     */
    protected $dateOfBirthDay;

    /**
     * Month of date of birth
     *
     * @var integer
     */
    protected $dateOfBirthMonth;

    /**
     * Year of date of birth
     *
     * @var integer
     */
    protected $dateOfBirthYear;

    /**
     * Language
     *
     * @var string
     */
    protected $language;

    /**
     * Code of state/province
     *
     * @var string
     */
    protected $zone;

    /**
     * Timezone
     *
     * @var float
     */
    protected $timezone;

    /**
     * Daylight saving time
     *
     * @var boolean
     */
    protected $daylight;

    /**
     * Country with static info table code
     *
     * @var string
     */
    protected $staticInfoCountry;

    /**
     * Number of mobilephone
     *
     * @var string
     */
    protected $mobilephone;

    /**
     * General terms and conditions accepted flag
     *
     * @var boolean
     */
    protected $gtc;

    /**
     * Privacy agreement accepted flag
     *
     * @var boolean
     */
    protected $privacy;

    /**
     * Status
     *
     * @var integer
     */
    protected $status;

    /**
     * wether the user register by invitation
     *
     * @var boolean
     */
    protected $byInvitation;

    /**
     * comment of user
     *
     * @var string
     */
    protected $comments;

    /**
     * if Dmail should be enabled
     *
     * @var boolean
     */
    protected $moduleSysDmailNewsletter;

    /**
     * if emails should be send as HTML or plain text
     *
     * @var boolean
     */
    protected $moduleSysDmailHtml;

    /**
     * selected dmail categories
     *
     * @var array
     */
    protected $moduleSysDmailCategory;

    /**
     * new email address before edit
     *
     * @var string
     */
    protected $emailNew;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\SfRegister\Domain\Model\FileReference>
     */
    protected $image;


    /**
     * Constructs a new Front-End User
     *
     * @param string $username
     * @param string $password
     * @return self
     */
    public function __construct($username = '', $password = '')
    {
        parent::__construct($username, $password);
        $this->image = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Initializes the date of birth if related values
     * are set by request to argument mapping
     *
     * @return void
     */
    public function prepareDateOfBirth()
    {
        if ($this->dateOfBirthDay !== null && $this->dateOfBirthMonth !== null && $this->dateOfBirthYear !== null) {
            if ($this->dateOfBirth === null) {
                $this->dateOfBirth = new \DateTime();
            }
            $this->dateOfBirth->setDate($this->dateOfBirthYear, $this->dateOfBirthMonth, $this->dateOfBirthDay);
        } else {
            $this->dateOfBirth = null;
        }
    }

    /**
     * Getter for disable
     *
     * @return boolean
     */
    public function getDisable()
    {
        return ($this->disable ? true : false);
    }

    /**
     * Setter for disable
     *
     * @param boolean $disable
     * @return void
     */
    public function setDisable($disable)
    {
        $this->disable = ($disable ? true : false);
    }

    /**
     * Getter for mailhash
     *
     * @return string
     * @deprecated
     */
    public function getMailhash()
    {
        return $this->mailhash;
    }

    /**
     * Setter for mailhash
     *
     * @param string $mailhash
     * @return void
     * @deprecated
     */
    public function setMailhash($mailhash)
    {
        $this->mailhash = trim($mailhash);
    }

    /**
     * Getter for activatedOn
     *
     * @return \DateTime|NULL
     */
    public function getActivatedOn()
    {
        return $this->activatedOn;
    }

    /**
     * Setter for activatedOn
     *
     * @param \DateTime|NULL $activatedOn
     * @return void
     */
    public function setActivatedOn(\DateTime $activatedOn = null)
    {
        $this->activatedOn = $activatedOn;
    }

    /**
     * Getter for captcha
     *
     * @return string
     */
    public function getCaptcha()
    {
        return $this->captcha;
    }

    /**
     * Setter for captcha
     *
     * @param string $captcha
     * @return void
     */
    public function setCaptcha($captcha)
    {
        $this->captcha = trim($captcha);
    }

    /**
     * Getter for passwordRepeat
     *
     * @return string
     */
    public function getPasswordRepeat()
    {
        return $this->passwordRepeat;
    }

    /**
     * Setter for passwordRepeat
     *
     * @param string $passwordRepeat
     * @return void
     */
    public function setPasswordRepeat($passwordRepeat)
    {
        $this->passwordRepeat = trim($passwordRepeat);
    }

    /**
     * Getter for emailRepeat
     *
     * @return string
     */
    public function getEmailRepeat()
    {
        return $this->emailRepeat;
    }

    /**
     * Setter for emailRepeat
     *
     * @param string $emailRepeat
     * @return void
     */
    public function setEmailRepeat($emailRepeat)
    {
        $this->emailRepeat = trim($emailRepeat);
    }

    /**
     * Add an image to the imagelist
     *
     * @param \Evoweb\SfRegister\Domain\Model\FileReference|\TYPO3\CMS\Core\Resource\FileReference $image
     * @return void
     */
    public function addImage($image)
    {
        $this->image->attach($image);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getImages()
    {
        return $this->image;
    }

    /**
     * Remove an image from the imagelist
     *
     * @param \Evoweb\SfRegister\Domain\Model\FileReference $image
     * @return void
     */
    public function removeImage($image)
    {
        $this->image->detach($image);
    }

    /**
     * Setter for title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        if ($title == 'none') {
            $title = '';
        }
        $this->title = $title;
    }

    /**
     * Getter for pseudonym
     *
     * @return string
     */
    public function getPseudonym()
    {
        return $this->pseudonym;
    }

    /**
     * Setter for pseudonym
     *
     * @param string $pseudonym
     * @return void
     */
    public function setPseudonym($pseudonym)
    {
        $this->pseudonym = $pseudonym;
    }

    /**
     * Getter for gender
     *
     * @return integer
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Setter for gender
     *
     * @param integer $gender
     * @return void
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * Getter for dateOfBirth
     *
     * @return \DateTime|NULL
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * Setter for dateOfBirth
     *
     * @param \DateTime|NULL $dateOfBirth
     * @return void
     */
    public function setDateOfBirth(\DateTime $dateOfBirth = null)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * Getter for day of dateOfBirth
     *
     * @return integer
     */
    public function getDateOfBirthDay()
    {
        $result = null;

        if ($this->dateOfBirth instanceof \DateTime) {
            $result = $this->dateOfBirth->format('j');
        }

        return $result;
    }

    /**
     * Setter for day of dateOfBirth
     *
     * @param integer $day
     * @return void
     */
    public function setDateOfBirthDay($day)
    {
        $this->dateOfBirthDay = $day;
        $this->prepareDateOfBirth();
    }

    /**
     * Getter for month of dateOfBirth
     *
     * @return integer
     */
    public function getDateOfBirthMonth()
    {
        $result = null;

        if ($this->dateOfBirth instanceof \DateTime) {
            $result = $this->dateOfBirth->format('n');
        }

        return $result;
    }

    /**
     * Setter for month of dateOfBirth
     *
     * @param integer $month
     * @return void
     */
    public function setDateOfBirthMonth($month)
    {
        $this->dateOfBirthMonth = $month;
        $this->prepareDateOfBirth();
    }

    /**
     * Getter for year of dateOfBirth
     *
     * @return integer
     */
    public function getDateOfBirthYear()
    {
        $result = null;

        if ($this->dateOfBirth instanceof \DateTime) {
            $result = $this->dateOfBirth->format('Y');
        }

        return $result;
    }

    /**
     * Setter for month of dateOfBirth
     *
     * @param integer $year
     * @return void
     */
    public function setDateOfBirthYear($year)
    {
        $this->dateOfBirthYear = $year;
        $this->prepareDateOfBirth();
    }

    /**
     * Getter for mobilphone
     *
     * @return string
     */
    public function getMobilephone()
    {
        return $this->mobilephone;
    }

    /**
     * Setter for mobilphone
     *
     * @param string $mobilephone
     * @return void
     */
    public function setMobilephone($mobilephone)
    {
        $this->mobilephone = $mobilephone;
    }

    /**
     * Getter for zone
     *
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Setter for zone
     *
     * @param string $zone
     * @return void
     */
    public function setZone($zone)
    {
        $this->zone = $zone;
    }

    /**
     * Getter for timezone
     *
     * @return float
     */
    public function getTimezone()
    {
        return floor($this->timezone) != $this->timezone ?
            $this->timezone * 10 :
            $this->timezone;
    }

    /**
     * Setter for timezone
     *
     * @param float $timezone
     * @return void
     */
    public function setTimezone($timezone)
    {
        $this->timezone = ($timezone > 14 || $timezone < -12 ?
            $timezone / 10 :
            $timezone);
    }

    /**
     * Getter for daylight
     *
     * @return boolean
     */
    public function getDaylight()
    {
        return $this->daylight ?
            true :
            false;
    }

    /**
     * Setter for daylight
     *
     * @param boolean $daylight
     * @return void
     */
    public function setDaylight($daylight)
    {
        $this->daylight = ($daylight ?
            true :
            false);
    }

    /**
     * Getter for static info cpuntry
     *
     * @return string
     */
    public function getStaticInfoCountry()
    {
        return $this->staticInfoCountry;
    }

    /**
     * Setter got static info country
     *
     * @param string $staticInfoCountry
     * @return void
     */
    public function setStaticInfoCountry($staticInfoCountry)
    {
        $this->staticInfoCountry = $staticInfoCountry;
    }

    /**
     * Getter for gtc
     *
     * @return boolean
     */
    public function getGtc()
    {
        return $this->gtc ?
            true :
            false;
    }

    /**
     * Setter for gtc
     *
     * @param boolean $gtc
     * @return void
     */
    public function setGtc($gtc)
    {
        $this->gtc = ($gtc ?
            true :
            false);
    }

    /**
     * Getter for privacy agreement flag
     *
     * @return boolean
     */
    public function getPrivacy()
    {
        return $this->privacy ?
            true :
            false;
    }

    /**
     * Setter for privacy agreement flag
     *
     * @param boolean $privacy
     * @return void
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = ($privacy ?
            true :
            false);
    }

    /**
     * Setter for byInvitation
     *
     * @param boolean $byInvitation
     * @return void
     */
    public function setByInvitation($byInvitation)
    {
        $this->byInvitation = $byInvitation;
    }

    /**
     * Getter for byInvitation
     *
     * @return boolean
     */
    public function getByInvitation()
    {
        return $this->byInvitation;
    }

    /**
     * Setter for comments
     *
     * @param string $comments
     * @return void
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * Getter for comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Setter language
     *
     * @param string $language
     * @return void
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Getter for language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Setter for moduleSysDmailCategory
     *
     * @param array $moduleSysDmailCategory
     * @return void
     */
    public function setModuleSysDmailCategory($moduleSysDmailCategory)
    {
        $this->moduleSysDmailCategory = $moduleSysDmailCategory;
    }

    /**
     * Getter for moduleSysDmailCategory
     *
     * @return array
     */
    public function getModuleSysDmailCategory()
    {
        return $this->moduleSysDmailCategory;
    }

    /**
     * Set moduleSysDmailNewsletter
     *
     * @param boolean $moduleSysDmailNewsletter
     * @return void
     */
    public function setModuleSysDmailNewsletter($moduleSysDmailNewsletter)
    {
        $this->moduleSysDmailNewsletter = $moduleSysDmailNewsletter;
    }

    /**
     * Get moduleSysDmailNewsletter
     *
     * @return boolean
     */
    public function getModuleSysDmailNewsletter()
    {
        return $this->moduleSysDmailNewsletter;
    }

    /**
     * Setter for moduleSysDmailHtml
     *
     * @param boolean $moduleSysDmailHtml
     * @return void
     */
    public function setModuleSysDmailHtml($moduleSysDmailHtml)
    {
        $this->moduleSysDmailHtml = $moduleSysDmailHtml;
    }

    /**
     * Getter for moduleSysDmailHtml
     *
     * @return boolean
     */
    public function getModuleSysDmailHtml()
    {
        return $this->moduleSysDmailHtml;
    }

    /**
     * Setter for status
     *
     * @param int $status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Getter for status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Setter for emailNew
     *
     * @param string $emailNew
     * @return void
     */
    public function setEmailNew($emailNew)
    {
        $this->emailNew = $emailNew;
    }

    /**
     * Getter for emailNew
     *
     * @return string
     */
    public function getEmailNew()
    {
        return $this->emailNew;
    }
}
