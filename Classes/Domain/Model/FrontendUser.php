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

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * An extended frontend user with more area
 */
class FrontendUser extends AbstractEntity implements FrontendUserInterface
{
    protected string $username = '';

    protected string $password = '';

    protected string $name = '';

    protected string $firstName = '';

    protected string $middleName = '';

    protected string $lastName = '';

    protected string $address = '';

    protected string $telephone = '';

    protected string $fax = '';

    protected string $email = '';

    protected string $title = '';

    protected string $zip = '';

    protected string $city = '';

    protected string $country = '';

    protected string $www = '';

    protected string $company = '';

    /**
     * If the account is disabled
     */
    protected bool $disable = false;

    protected string $pseudonym = '';

    /**
     * Gender
     *   1 - mr
     *   2 - mrs
     */
    protected int $gender = 1;

    protected int $dateOfBirthDay = 0;

    protected int $dateOfBirthMonth = 0;

    protected int $dateOfBirthYear = 0;

    protected string $language = '';

    /**
     * Code of state/province
     */
    protected string $zone = '';

    protected float $timezone = 0;

    /**
     * Daylight saving time
     */
    protected bool $daylight = false;

    /**
     * Country with static info table code
     */
    protected string $staticInfoCountry = '';

    protected string $mobilephone = '';

    /**
     * General terms and conditions accepted
     */
    protected bool $gtc = false;

    /**
     * Privacy agreement accepted
     */
    protected bool $privacy = false;

    protected int $status = 0;

    /**
     * Whether the user registered by invitation
     */
    protected bool $byInvitation = false;

    protected string $comments = '';

    /**
     * if Dmail should be enabled
     */
    protected bool $moduleSysDmailNewsletter = false;

    /**
     * if emails should be sent as HTML or plain text
     */
    protected bool $moduleSysDmailHtml = true;

    /**
     * new email address before edit
     */
    protected string $emailNew = '';

    /**
     * email address of invitee
     */
    protected string $invitationEmail = '';

    protected ?\DateTime $lastlogin = null;

    protected ?\DateTime $activatedOn = null;

    protected ?\DateTime $dateOfBirth = null;

    #[Extbase\ORM\Transient]
    protected string $captcha = '';

    #[Extbase\ORM\Transient]
    protected string $passwordRepeat = '';

    #[Extbase\ORM\Transient]
    protected string $emailRepeat = '';

    /**
     * Sets the usergroups. Keep in mind that the property is called "usergroup"
     * although it can hold several usergroups.
     *
     * @var ?ObjectStorage<FrontendUserGroup>
     */
    protected ?ObjectStorage $usergroup = null;

    /**
     * @var ?ObjectStorage<FileReference>
     */
    protected ?ObjectStorage $image = null;

    /**
     * @var ?ObjectStorage<Category>
     */
    protected ?ObjectStorage $moduleSysDmailCategory = null;

    public function __construct(string $username = '', string $password = '')
    {
        $this->username = $username;
        $this->password = $password;
        $this->initializeObject();
    }

    public function initializeObject(): void
    {
        $this->usergroup = $this->usergroup ?? new ObjectStorage();
        $this->image = $this->image ?? new ObjectStorage();
        $this->moduleSysDmailCategory = new ObjectStorage();
    }

    public function setUsergroup(ObjectStorage $usergroup): void
    {
        $this->usergroup = $usergroup;
    }

    public function getUsergroup(): ObjectStorage
    {
        return $this->usergroup;
    }

    public function addUsergroup(FrontendUserGroup $usergroup): void
    {
        $this->usergroup->attach($usergroup);
    }

    public function removeUsergroup(FrontendUserGroup $usergroup): void
    {
        $this->usergroup->detach($usergroup);
    }

    public function setImage(ObjectStorage $image): void
    {
        $this->image = $image;
    }

    public function getImage(): ObjectStorage
    {
        return $this->image;
    }

    public function setModuleSysDmailCategory(ObjectStorage $moduleSysDmailCategory): void
    {
        $this->moduleSysDmailCategory = $moduleSysDmailCategory;
    }

    public function getModuleSysDmailCategory(): ObjectStorage
    {
        return $this->moduleSysDmailCategory;
    }

    public function removeImage(): void
    {
        $this->image->removeAll($this->image);
    }

    public function setLastlogin(\DateTime $lastlogin): void
    {
        $this->lastlogin = $lastlogin;
    }

    public function getLastlogin(): ?\DateTime
    {
        return $this->lastlogin;
    }

    public function setActivatedOn(?\DateTime $activatedOn): void
    {
        $this->activatedOn = $activatedOn;
    }

    public function getActivatedOn(): ?\DateTime
    {
        return $this->activatedOn;
    }

    public function setDateOfBirth(?\DateTime $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function getDateOfBirth(): ?\DateTime
    {
        return $this->dateOfBirth;
    }

    /**
     * Initializes the date of birth if related values are set by request to argument mapping
     */
    public function prepareDateOfBirth(): void
    {
        if ($this->dateOfBirthDay > 0 && $this->dateOfBirthMonth > 0 && $this->dateOfBirthYear > 0) {
            if ($this->dateOfBirth === null) {
                $this->dateOfBirth = new \DateTime();
            }
            $this->dateOfBirth->setDate($this->dateOfBirthYear, $this->dateOfBirthMonth, $this->dateOfBirthDay);
        }
    }

    public function setDateOfBirthDay(int $day): void
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

    public function setDateOfBirthMonth(int $month): void
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

    public function setDateOfBirthYear(int $year): void
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

    public function setTimezone(float $timezone): void
    {
        $this->timezone = ($timezone > 14 || $timezone < -12)
            ? $timezone / 10
            : $timezone;
    }

    public function getTimezone(): float
    {
        return floor($this->timezone) != $this->timezone
            ? $this->timezone * 10
            : $this->timezone;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    public function setMiddleName(string $middleName): void
    {
        $this->middleName = $middleName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getTelephone(): string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): void
    {
        $this->telephone = $telephone;
    }

    public function getFax(): string
    {
        return $this->fax;
    }

    public function setFax(string $fax): void
    {
        $this->fax = $fax;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    public function getWww(): string
    {
        return $this->www;
    }

    public function setWww(string $www): void
    {
        $this->www = $www;
    }

    public function getCompany(): string
    {
        return $this->company;
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getDisable(): bool
    {
        return $this->disable;
    }

    public function setDisable(bool $disable): void
    {
        $this->disable = $disable;
    }

    public function getPseudonym(): string
    {
        return $this->pseudonym;
    }

    public function setPseudonym(string $pseudonym): void
    {
        $this->pseudonym = $pseudonym;
    }

    public function getGender(): int
    {
        return $this->gender;
    }

    public function setGender(int $gender): void
    {
        $this->gender = $gender;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function setZone(string $zone): void
    {
        $this->zone = $zone;
    }

    public function isDaylight(): bool
    {
        return $this->daylight;
    }

    public function setDaylight(bool $daylight): void
    {
        $this->daylight = $daylight;
    }

    public function getStaticInfoCountry(): string
    {
        return $this->staticInfoCountry;
    }

    public function setStaticInfoCountry(string $staticInfoCountry): void
    {
        $this->staticInfoCountry = $staticInfoCountry;
    }

    public function getMobilephone(): string
    {
        return $this->mobilephone;
    }

    public function setMobilephone(string $mobilephone): void
    {
        $this->mobilephone = $mobilephone;
    }

    public function isGtc(): bool
    {
        return $this->gtc;
    }

    public function setGtc(bool $gtc): void
    {
        $this->gtc = $gtc;
    }

    public function isPrivacy(): bool
    {
        return $this->privacy;
    }

    public function setPrivacy(bool $privacy): void
    {
        $this->privacy = $privacy;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function isByInvitation(): bool
    {
        return $this->byInvitation;
    }

    public function setByInvitation(bool $byInvitation): void
    {
        $this->byInvitation = $byInvitation;
    }

    public function getComments(): string
    {
        return $this->comments;
    }

    public function setComments(string $comments): void
    {
        $this->comments = $comments;
    }

    public function isModuleSysDmailNewsletter(): bool
    {
        return $this->moduleSysDmailNewsletter;
    }

    public function setModuleSysDmailNewsletter(bool $moduleSysDmailNewsletter): void
    {
        $this->moduleSysDmailNewsletter = $moduleSysDmailNewsletter;
    }

    public function isModuleSysDmailHtml(): bool
    {
        return $this->moduleSysDmailHtml;
    }

    public function setModuleSysDmailHtml(bool $moduleSysDmailHtml): void
    {
        $this->moduleSysDmailHtml = $moduleSysDmailHtml;
    }

    public function getEmailNew(): string
    {
        return $this->emailNew;
    }

    public function setEmailNew(string $emailNew): void
    {
        $this->emailNew = $emailNew;
    }

    public function getInvitationEmail(): string
    {
        return $this->invitationEmail;
    }

    public function setInvitationEmail(string $invitationEmail): void
    {
        $this->invitationEmail = $invitationEmail;
    }

    public function getCaptcha(): string
    {
        return $this->captcha;
    }

    public function setCaptcha(string $captcha): void
    {
        $this->captcha = $captcha;
    }

    public function getPasswordRepeat(): string
    {
        return $this->passwordRepeat;
    }

    public function setPasswordRepeat(string $passwordRepeat): void
    {
        $this->passwordRepeat = $passwordRepeat;
    }

    public function getEmailRepeat(): string
    {
        return $this->emailRepeat;
    }

    public function setEmailRepeat(string $emailRepeat): void
    {
        $this->emailRepeat = $emailRepeat;
    }
}
