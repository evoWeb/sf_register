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

use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * An extended frontend user with more area
 */
class FrontendUser extends AbstractEntity implements FrontendUserInterface
{
    /**
     * @var string
     */
    protected string $username = '';

    /**
     * @var string
     */
    protected string $password = '';

    /**
     * @var ?ObjectStorage<FrontendUserGroup>
     */
    protected ?ObjectStorage $usergroup = null;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $firstName = '';

    /**
     * @var string
     */
    protected string $middleName = '';

    /**
     * @var string
     */
    protected string $lastName = '';

    /**
     * @var string
     */
    protected string $address = '';

    /**
     * @var string
     */
    protected string $telephone = '';

    /**
     * @var string
     */
    protected string $fax = '';

    /**
     * @var string
     */
    protected string $email = '';

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var string
     */
    protected string $zip = '';

    /**
     * @var string
     */
    protected string $city = '';

    /**
     * @var string
     */
    protected string $country = '';

    /**
     * @var string
     */
    protected string $www = '';

    /**
     * @var string
     */
    protected string $company = '';

    /**
     * @var ?ObjectStorage<FileReference>
     */
    protected ?ObjectStorage $image = null;

    /**
     * @var ?\DateTime
     */
    protected ?\DateTime $lastlogin = null;

    /**
     * If the account is disabled or not
     *
     * @var bool
     */
    protected bool $disable = false;

    /**
     * Date on which the account was activated
     *
     * @var ?\DateTime
     */
    protected ?\DateTime $activatedOn = null;

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected string $captcha = '';

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected string $passwordRepeat = '';

    /**
     *  virtual not stored in database
     *
     * @var string
     */
    protected string $emailRepeat = '';

    /**
     * Pseudonym
     *
     * @var string
     */
    protected string $pseudonym = '';

    /**
     * Gender 1 or 2 for mr or mrs
     *
     * @var int
     */
    protected int $gender = 1;

    /**
     * Date of birth
     *
     * @var ?\DateTime
     */
    protected ?\DateTime $dateOfBirth = null;

    /**
     * Day of date of birth
     *
     * @var int
     */
    protected int $dateOfBirthDay = 0;

    /**
     * Month of date of birth
     *
     * @var int
     */
    protected int $dateOfBirthMonth = 0;

    /**
     * Year of date of birth
     *
     * @var int
     */
    protected int $dateOfBirthYear = 0;

    /**
     * Language
     *
     * @var string
     */
    protected string $language = '';

    /**
     * Code of state/province
     *
     * @var string
     */
    protected string $zone = '';

    /**
     * Timezone
     *
     * @var float
     */
    protected float $timezone = 0;

    /**
     * Daylight saving time
     *
     * @var bool
     */
    protected bool $daylight = false;

    /**
     * Country with static info table code
     *
     * @var string
     */
    protected string $staticInfoCountry = '';

    /**
     * Number of mobilephone
     *
     * @var string
     */
    protected string $mobilephone = '';

    /**
     * General terms and conditions accepted flag
     *
     * @var bool
     */
    protected bool $gtc = false;

    /**
     * Privacy agreement accepted flag
     *
     * @var bool
     */
    protected bool $privacy = false;

    /**
     * Status
     *
     * @var int
     */
    protected int $status = 0;

    /**
     * whether the user register by invitation
     *
     * @var bool
     */
    protected bool $byInvitation = false;

    /**
     * comment of user
     *
     * @var string
     */
    protected string $comments = '';

    /**
     * if Dmail should be enabled
     *
     * @var bool
     */
    protected bool $moduleSysDmailNewsletter = false;

    /**
     * if emails should be sent as HTML or plain text
     *
     * @var bool
     */
    protected bool $moduleSysDmailHtml = true;

    /**
     * selected Dmail categories
     *
     * @var ?ObjectStorage<Category>
     */
    protected ?ObjectStorage $moduleSysDmailCategory = null;

    /**
     * new email address before edit
     *
     * @var string
     */
    protected string $emailNew = '';

    /**
     * email address of invitee
     *
     * @var string
     */
    protected string $invitationEmail = '';

    /**
     * Constructs a new Front-End User
     *
     * @param string $username
     * @param string $password
     */
    public function __construct(string $username = '', string $password = '')
    {
        $this->username = $username;
        $this->password = $password;
        $this->initializeObject();
    }

    public function initializeObject()
    {
        $this->image = $this->image ?? new ObjectStorage();
        $this->usergroup = $this->usergroup ?? new ObjectStorage();
        $this->moduleSysDmailCategory = $this->moduleSysDmailCategory ?? new ObjectStorage();
    }

    /**
     * Sets the username value
     *
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * Returns the username value
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the password value
     *
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * Returns the password value
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @param ObjectStorage<FrontendUserGroup> $usergroup
     */
    public function setUsergroup(ObjectStorage $usergroup)
    {
        $this->usergroup = $usergroup;
    }

    /**
     * Adds an usergroup to the frontend user
     *
     * @param FrontendUserGroup $usergroup
     */
    public function addUsergroup(FrontendUserGroup $usergroup)
    {
        $this->usergroup->attach($usergroup);
    }

    /**
     * Removes an usergroup from the frontend user
     *
     * @param FrontendUserGroup $usergroup
     */
    public function removeUsergroup(FrontendUserGroup $usergroup)
    {
        $this->usergroup->detach($usergroup);
    }

    /**
     * Returns the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @return ObjectStorage<FrontendUserGroup> An object storage containing the usergroup
     */
    public function getUsergroup(): ObjectStorage
    {
        return $this->usergroup;
    }

    /**
     * Sets the name value
     *
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns the name value
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the firstName value
     *
     * @param string $firstName
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the firstName value
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Sets the middleName value
     *
     * @param string $middleName
     */
    public function setMiddleName(string $middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * Returns the middleName value
     *
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * Sets the lastName value
     *
     * @param string $lastName
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Returns the lastName value
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Sets the address value
     *
     * @param string $address
     */
    public function setAddress(string $address)
    {
        $this->address = $address;
    }

    /**
     * Returns the address value
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * Sets the telephone value
     *
     * @param string $telephone
     */
    public function setTelephone(string $telephone)
    {
        $this->telephone = $telephone;
    }

    /**
     * Returns the telephone value
     *
     * @return string
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * Sets the fax value
     *
     * @param string $fax
     */
    public function setFax(string $fax)
    {
        $this->fax = $fax;
    }

    /**
     * Returns the fax value
     *
     * @return string
     */
    public function getFax(): string
    {
        return $this->fax;
    }

    /**
     * Sets the email value
     *
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * Returns the email value
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the title value
     *
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Returns the title value
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the zip value
     *
     * @param string $zip
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;
    }

    /**
     * Returns the zip value
     *
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Sets the city value
     *
     * @param string $city
     */
    public function setCity(string $city)
    {
        $this->city = $city;
    }

    /**
     * Returns the city value
     *
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Sets the country value
     *
     * @param string $country
     */
    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    /**
     * Returns the country value
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * Sets the www value
     *
     * @param string $www
     */
    public function setWww(string $www)
    {
        $this->www = $www;
    }

    /**
     * Returns the www value
     *
     * @return string
     */
    public function getWww(): string
    {
        return $this->www;
    }

    /**
     * Sets the company value
     *
     * @param string $company
     */
    public function setCompany(string $company)
    {
        $this->company = $company;
    }

    /**
     * Returns the company value
     *
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * Sets the image value
     *
     * @param ObjectStorage<FileReference> $image
     */
    public function setImage(ObjectStorage $image)
    {
        $this->image = $image;
    }

    /**
     * Gets the image value
     *
     * @return ObjectStorage<FileReference>
     */
    public function getImage(): ?ObjectStorage
    {
        return $this->image;
    }

    /**
     * Sets the lastlogin value
     *
     * @param \DateTime $lastlogin
     */
    public function setLastlogin(\DateTime $lastlogin)
    {
        $this->lastlogin = $lastlogin;
    }

    /**
     * Returns the lastlogin value
     *
     * @return ?\DateTime
     */
    public function getLastlogin(): ?\DateTime
    {
        return $this->lastlogin;
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
        $this->disable = $disable;
    }

    public function getDisable(): bool
    {
        return $this->disable;
    }

    public function getActivatedOn(): ?\DateTime
    {
        return $this->activatedOn;
    }

    public function setActivatedOn(?\DateTime $activatedOn)
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
        $this->image = new ObjectStorage();
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

    public function setDateOfBirth(?\DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getDateOfBirth(): ?\DateTime
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

    public function setTimezone(float $timezone)
    {
        $this->timezone = ($timezone > 14 || $timezone < -12 ?
            $timezone / 10 :
            $timezone);
    }

    public function getTimezone(): float
    {
        return floor($this->timezone) != $this->timezone ?
            $this->timezone * 10 :
            $this->timezone;
    }

    public function setDaylight(bool $daylight)
    {
        $this->daylight = $daylight;
    }

    public function getDaylight(): bool
    {
        return $this->daylight;
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
        $this->gtc = $gtc;
    }

    public function getGtc(): bool
    {
        return $this->gtc;
    }

    public function setPrivacy(bool $privacy)
    {
        $this->privacy = $privacy;
    }

    public function getPrivacy(): bool
    {
        return $this->privacy;
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

    public function setModuleSysDmailCategory($moduleSysDmailCategory)
    {
        $this->moduleSysDmailCategory = $moduleSysDmailCategory;
    }

    public function getModuleSysDmailCategory(): ?ObjectStorage
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
