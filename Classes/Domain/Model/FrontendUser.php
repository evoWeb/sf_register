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

use Evoweb\SfRegister\Interfaces\FrontendUserInterface;

/**
 * An extended frontend user with more area
 */
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser implements FrontendUserInterface
{
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Evoweb\SfRegister\Domain\Model\FrontendUserGroup>
     */
    protected $usergroup;

    /**
     * If the account is disabled or not
     *
     * @var bool
     */
    protected $disable = false;

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
    protected $captcha = '';

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected $passwordRepeat = '';

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected $emailRepeat = '';

    /**
     * Pseudonym
     *
     * @var string
     */
    protected $pseudonym = '';

    /**
     * Gender 1 or 2 for mr or mrs
     *
     * @var int
     */
    protected $gender = 1;

    /**
     * Date of birth
     *
     * @var \DateTime
     */
    protected $dateOfBirth;

    /**
     * Day of date of birth
     *
     * @var int
     */
    protected $dateOfBirthDay = 0;

    /**
     * Month of date of birth
     *
     * @var int
     */
    protected $dateOfBirthMonth = 0;

    /**
     * Year of date of birth
     *
     * @var int
     */
    protected $dateOfBirthYear = 0;

    /**
     * Language
     *
     * @var string
     */
    protected $language = '';

    /**
     * Code of state/province
     *
     * @var string
     */
    protected $zone = '';

    /**
     * Timezone
     *
     * @var float
     */
    protected $timezone = 0;

    /**
     * Daylight saving time
     *
     * @var bool
     */
    protected $daylight = false;

    /**
     * Country with static info table code
     *
     * @var string
     */
    protected $staticInfoCountry = '';

    /**
     * Number of mobilephone
     *
     * @var string
     */
    protected $mobilephone = '';

    /**
     * General terms and conditions accepted flag
     *
     * @var bool
     */
    protected $gtc = false;

    /**
     * Privacy agreement accepted flag
     *
     * @var bool
     */
    protected $privacy = false;

    /**
     * Status
     *
     * @var int
     */
    protected $status = 0;

    /**
     * whether the user register by invitation
     *
     * @var bool
     */
    protected $byInvitation = false;

    /**
     * comment of user
     *
     * @var string
     */
    protected $comments = '';

    /**
     * if Dmail should be enabled
     *
     * @var bool
     */
    protected $moduleSysDmailNewsletter = false;

    /**
     * if emails should be send as HTML or plain text
     *
     * @var bool
     */
    protected $moduleSysDmailHtml = true;

    /**
     * selected Dmail categories
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $moduleSysDmailCategory;

    /**
     * new email address before edit
     *
     * @var string
     */
    protected $emailNew = '';

    /**
     * email address of invitee
     *
     * @var string
     */
    protected $invitationEmail = '';

    public function initializeObject()
    {
        $this->moduleSysDmailCategory = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * Initializes the date of birth if related values are set by request to argument mapping
     */
    public function prepareDateOfBirth()
    {
        if ($this->dateOfBirthDay > 0 && $this->dateOfBirthMonth > 0 && $this->dateOfBirthYear > 0) {
            if ($this->dateOfBirth === null) {
                $this->dateOfBirth = new \DateTime();
            }
            $this->dateOfBirth->setDate($this->dateOfBirthYear, $this->dateOfBirthMonth, $this->dateOfBirthDay);
        }
    }

    public function setDisable(bool $disable)
    {
        $this->disable = ($disable ? true : false);
    }

    public function getDisable(): bool
    {
        return (bool) $this->disable;
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

    public function setActivatedOn(\DateTime $activatedOn = null)
    {
        $this->activatedOn = $activatedOn;
    }

    public function setCaptcha(string $captcha)
    {
        $this->captcha = trim($captcha);
    }

    public function getCaptcha(): string
    {
        return $this->captcha;
    }

    public function setPasswordRepeat(string $passwordRepeat)
    {
        $this->passwordRepeat = trim($passwordRepeat);
    }

    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    public function setEmailRepeat(string $emailRepeat)
    {
        $this->emailRepeat = trim($emailRepeat);
    }

    public function getEmailRepeat(): string
    {
        return $this->emailRepeat;
    }

    public function removeImage()
    {
        $this->image = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    public function setPseudonym(string $pseudonym)
    {
        $this->pseudonym = $pseudonym;
    }

    public function getPseudonym(): string
    {
        return $this->pseudonym;
    }

    public function setGender(int $gender)
    {
        $this->gender = $gender;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function setDateOfBirth(\DateTime $dateOfBirth = null)
    {
        $this->dateOfBirth = $dateOfBirth;
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

    public function setDateOfBirthDay(int $day)
    {
        $this->dateOfBirthDay = $day;
        $this->prepareDateOfBirth();
    }

    public function getDateOfBirthDay(): int
    {
        $result = 1;

        if ($this->dateOfBirth instanceof \DateTime) {
            $result = $this->dateOfBirth->format('j');
        }

        return $result;
    }

    public function setDateOfBirthMonth(int $month)
    {
        $this->dateOfBirthMonth = $month;
        $this->prepareDateOfBirth();
    }

    public function getDateOfBirthMonth(): int
    {
        $result = 1;

        if ($this->dateOfBirth instanceof \DateTime) {
            $result = $this->dateOfBirth->format('n');
        }

        return $result;
    }

    public function setDateOfBirthYear(int $year)
    {
        $this->dateOfBirthYear = $year;
        $this->prepareDateOfBirth();
    }

    public function getDateOfBirthYear(): int
    {
        $result = 1970;

        if ($this->dateOfBirth instanceof \DateTime) {
            $result = $this->dateOfBirth->format('Y');
        }

        return $result;
    }

    public function setMobilephone(string $mobilephone)
    {
        $this->mobilephone = $mobilephone;
    }

    public function getMobilephone(): string
    {
        return $this->mobilephone;
    }

    public function setZone(string $zone)
    {
        $this->zone = $zone;
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function setTimezone(int $timezone)
    {
        $this->timezone = ($timezone > 14 || $timezone < -12 ?
            $timezone / 10 :
            $timezone);
    }

    public function getTimezone(): int
    {
        return floor($this->timezone) != $this->timezone ?
            $this->timezone * 10 :
            $this->timezone;
    }

    public function setDaylight(bool $daylight)
    {
        $this->daylight = ($daylight ?
            true :
            false);
    }

    public function getDaylight(): bool
    {
        return $this->daylight ?
            true :
            false;
    }

    public function setStaticInfoCountry(string $staticInfoCountry)
    {
        $this->staticInfoCountry = $staticInfoCountry;
    }

    public function getStaticInfoCountry(): string
    {
        return $this->staticInfoCountry;
    }

    public function setGtc(bool $gtc)
    {
        $this->gtc = ($gtc ?
            true :
            false);
    }

    public function getGtc(): bool
    {
        return $this->gtc ?
            true :
            false;
    }

    public function setPrivacy(bool $privacy)
    {
        $this->privacy = ($privacy ?
            true :
            false);
    }

    public function getPrivacy(): bool
    {
        return $this->privacy ?
            true :
            false;
    }

    public function setByInvitation(bool $byInvitation)
    {
        $this->byInvitation = $byInvitation;
    }

    public function getByInvitation(): bool
    {
        return $this->byInvitation;
    }

    public function setComments(string $comments)
    {
        $this->comments = $comments;
    }

    public function getComments(): string
    {
        return $this->comments;
    }

    public function setLanguage(string $language)
    {
        $this->language = $language;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setModuleSysDmailCategory(array $moduleSysDmailCategory)
    {
        $this->moduleSysDmailCategory = $moduleSysDmailCategory;
    }

    /**
     * @return array|null|\TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getModuleSysDmailCategory()
    {
        return $this->moduleSysDmailCategory;
    }

    public function setModuleSysDmailNewsletter(bool $moduleSysDmailNewsletter)
    {
        $this->moduleSysDmailNewsletter = $moduleSysDmailNewsletter;
    }

    public function getModuleSysDmailNewsletter(): bool
    {
        return $this->moduleSysDmailNewsletter;
    }

    public function setModuleSysDmailHtml(bool $moduleSysDmailHtml)
    {
        $this->moduleSysDmailHtml = $moduleSysDmailHtml;
    }

    public function getModuleSysDmailHtml(): bool
    {
        return $this->moduleSysDmailHtml;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setEmailNew(string $emailNew)
    {
        $this->emailNew = $emailNew;
    }

    public function getEmailNew(): string
    {
        return $this->emailNew;
    }

    public function setInvitationEmail(string $invitationEmail)
    {
        $this->invitationEmail = $invitationEmail;
    }

    public function getInvitationEmail(): string
    {
        return $this->invitationEmail;
    }
}
