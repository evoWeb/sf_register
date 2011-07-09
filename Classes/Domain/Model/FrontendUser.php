<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * An extended frontend user with more attributes
 */
class Tx_SfRegister_Domain_Model_FrontendUser extends Tx_Extbase_Domain_Model_FrontendUser implements Tx_SfRegister_Interfaces_FrontendUser {
	/**
	 * If the account is diabled or not
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
	 * @var DateTime
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
	 * @var  integer
	 */
	protected $gender;


	/**
	 * Date of birth
	 *
	 * @var  DateTime
	 */
	protected $dateOfBirth;

	/**
	 * Day of date of birth
	 *
	 * @var  integer
	 */
	protected $dateOfBirthDay;

	/**
	 * Month of date of birth
	 *
	 * @var  integer
	 */
	protected $dateOfBirthMonth;

	/**
	 * Year of date of birth
	 *
	 * @var  integer
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
	 * Constructs a new Front-End User
	 *
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($username = '', $password = '') {
		parent::__construct($username, $password);

		$this->activatedOn = new DateTime();
		$this->dateOfBirth = new DateTime();
   }

	/**
	 * Initializes the date of birth if related values are set by request to argument mapping
	 *
	 * @return void
	 */
	public function prepareDateOfBirth() {
		if ($this->dateOfBirthDay) {
			$this->setDateOfBirthDay($this->dateOfBirthDay);
		}
		if ($this->dateOfBirthMonth) {
			$this->setDateOfBirthMonth($this->dateOfBirthMonth);
		}
		if ($this->dateOfBirthYear) {
			$this->setDateOfBirthYear($this->dateOfBirthYear);
		}
	}


	/**
	 * Setter for disable
	 *
	 * @param boolean $disable
	 * @return void
	 */
	public function setDisable($disable) {
		$this->disable = ($disable ? TRUE : FALSE);
	}

	/**
	 * Getter for disable
	 *
	 * @return boolean
	 */
	public function getDisable() {
		return ($this->disable ? TRUE : FALSE);
	}

	/**
	 * Setter for mailhash
	 *
	 * @param string $mailhash
	 * @return void
	 */
	public function setMailhash($mailhash) {
		$this->mailhash = trim($mailhash);
	}

	/**
	 * Getter for mailhash
	 *
	 * @return string
	 */
	public function getMailhash() {
		return $this->mailhash;
	}

	/**
	 * @param \DateTime $activatedOn
	 */
	public function setActivatedOn($activatedOn) {
		$this->activatedOn = $activatedOn;
	}

	/**
	 * @return \DateTime
	 */
	public function getActivatedOn() {
		return $this->activatedOn;
	}


	/**
	 * Setter for captcha
	 *
	 * @param string $captcha
	 * @return void
	 */
	public function setCaptcha($captcha) {
		$this->captcha = trim($captcha);
	}

	/**
	 * Getter for captcha
	 *
	 * @return string
	 */
	public function getCaptcha() {
		return $this->captcha;
	}

	/**
	 * Setter for passwordRepeat
	 *
	 * @param string $passwordRepeat
	 * @return void
	 */
	public function setPasswordRepeat($passwordRepeat) {
		$this->passwordRepeat = trim($passwordRepeat);
	}

	/**
	 * Getter for passwordRepeat
	 *
	 * @return string
	 */
	public function getPasswordRepeat() {
		return $this->passwordRepeat;
	}

	/**
	 * Setter for emailRepeat
	 *
	 * @param string $emailRepeat
	 * @return void
	 */
	public function setEmailRepeat($emailRepeat) {
		$this->emailRepeat = trim($emailRepeat);
	}

	/**
	 * Getter for emailRepeat
	 *
	 * @return string
	 */
	public function getEmailRepeat() {
		return $this->emailRepeat;
	}


	/**
	 * Set an image list
	 *
	 * @param array $imageList
	 * @return void
	 */
	public function setImageList($imageList) {
		$this->image = implode(',', $imageList);
	}

	/**
	 * Get an image list
	 *
	 * @return array
	 */
	public function getImageList() {
		return t3lib_div::trimExplode(',', $this->image, TRUE);
	}

	/**
	 * Add an image to the imagelist
	 *
	 * @param string $image
	 * @return void
	 */
	public function addImage($image) {
		$imageList = $this->getImageList();

		if (!in_array($image, $imageList)) {
			$imageList = array_merge($imageList, array($image));
		}

		$this->setImageList($imageList);
	}

	/**
	 * Remove an image from the imagelist
	 *
	 * @param string $image
	 * @return void
	 */
	public function removeImage($image) {
		$imageList = $this->getImageList();

		if (in_array($image, $imageList)) {
			$imageList = array_diff($imageList, array($image));
		}

		$this->setImageList($imageList);
	}



	/**
	 * Setter for title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		if ($title == 'none') {
			$title = '';
		}
		$this->title = $title;
	}

	/**
	 * Setter for pseudonym
	 *
	 * @param string $pseudonym
	 */
	public function setPseudonym($pseudonym) {
		$this->pseudonym = $pseudonym;
	}

	/**
	 * Getter for pseudonym
	 *
	 * @return string
	 */
	public function getPseudonym() {
		return $this->pseudonym;
	}

	/**
	 * Setter for gender
	 *
	 * @param integer $gender
	 * @return void
	 */
	public function setGender($gender) {
		$this->gender = $gender;
	}

	/**
	 * Getter for gender
	 *
	 * @return integer
	 */
	public function getGender() {
		return $this->gender;
	}


	/**
	 * Setter for dateOfBirth
	 *
	 * @param string $dateOfBirth
	 * @return void
	 */
	public function setDateOfBirth($dateOfBirth) {
		$this->dateOfBirth = $dateOfBirth;
	}

	/**
	 * Setter for day of dateOfBirth
	 *
	 * @param integer	$day
	 * @return void
	 */
	public function setDateOfBirthDay($day) {
		$this->dateOfBirthDay = $day;
		$this->dateOfBirth->setDate($this->dateOfBirth->format('Y'), $this->dateOfBirth->format('m'), $day);
	}

	/**
	 * Setter for month of dateOfBirth
	 *
	 * @param integer	$mont
	 * @return void
	 */
	public function setDateOfBirthMonth($month) {
		$this->dateOfBirthMonth = $month;
		$this->dateOfBirth->setDate($this->dateOfBirth->format('Y'), $month, $this->dateOfBirth->format('d'));
	}

	/**
	 * Setter for month of dateOfBirth
	 *
	 * @param integer	$year
	 * @return void
	 */
	public function setDateOfBirthYear($year) {
		$this->dateOfBirthYear = $year;
		$this->dateOfBirth->setDate($year, $this->dateOfBirth->format('m'), $this->dateOfBirth->format('d'));
	}

	/**
	 * Getter for dateOfBirth
	 *
	 * @return DateTime
	 */
	public function getDateOfBirth() {
		return $this->dateOfBirth;
	}

	/**
	 * Getter for day of dateOfBirth
	 *
	 * @return integer
	 */
	public function getDateOfBirthDay() {
		if ($this->dateOfBirth instanceof DateTime) {
			return $this->dateOfBirth->format('j');
		}
	}

	/**
	 * Getter for month of dateOfBirth
	 *
	 * @return integer
	 */
	public function getDateOfBirthMonth() {
		if ($this->dateOfBirth instanceof DateTime) {
			return $this->dateOfBirth->format('n');
		}
	}

	/**
	 * Getter for year of dateOfBirth
	 * 
	 * @return integer
	 */
	public function getDateOfBirthYear() {
		if ($this->dateOfBirth instanceof DateTime) {
			return $this->dateOfBirth->format('Y');
		}
	}


	/**
	 * Setter for mobilphone
	 *
	 * @param string $mobilephone
	 * @return void
	 */
	public function setMobilephone($mobilephone) {
		$this->mobilephone = $mobilephone;
	}

	/**
	 * Getter for mobilphone
	 *
	 * @return string
	 */
	public function getMobilephone() {
		return $this->mobilephone;
	}

	/**
	 * Setter for zone
	 * 
	 * @param string $zone
	 * @return void
	 */
	public function setZone($zone) {
		$this->zone = $zone;
	}

	/**
	 * Getter for zone
	 *
	 * @return string
	 */
	public function getZone() {
		return $this->zone;
	}

	/**
	 * @param integer $timezone
	 */
	public function setTimezone($timezone) {
		$this->timezone = ($timezone > 14 || $timezone < -12 ? $timezone / 10 : $timezone);
	}

	/**
	 * @return integer
	 */
	public function getTimezone() {
		return floor($this->timezone) != $this->timezone ? $this->timezone * 10 : $this->timezone;
	}

	/**
	 * @param boolean $daylight
	 */
	public function setDaylight($daylight) {
		$this->daylight = ($daylight ? TRUE : FALSE);
	}

	/**
	 * @return boolean
	 */
	public function getDaylight() {
		return $this->daylight ? TRUE : FALSE;
	}

	/**
	 * Setter got static info country
	 *
	 * @param string $staticInfoCountry
	 * @return void
	 */
	public function setStaticInfoCountry($staticInfoCountry) {
		$this->staticInfoCountry = $staticInfoCountry;
	}

	/**
	 * Getter for static info cpuntry
	 * 
	 * @return string
	 */
	public function getStaticInfoCountry() {
		return $this->staticInfoCountry;
	}

	/**
	 * Setter for gtc
	 *
	 * @param boolean $gtc
	 * @return void
	 */
	public function setGtc($gtc) {
		$this->gtc = ($gtc ? TRUE : FALSE);
	}

	/**
	 * Getter for gtc
	 *
	 * @return boolean
	 */
	public function getGtc() {
		return $this->gtc ? TRUE : FALSE;
	}

	/**
	 * Setter for privacy agreement flag
	 *
	 * @param boolean $privacy
	 */
	public function setPrivacy($privacy) {
		$this->privacy = ($privacy ? TRUE : FALSE);
	}

	/**
	 * Getter for privacy agreement flag
	 *
	 * @return boolean
	 */
	public function getPrivacy() {
		return $this->privacy ? TRUE : FALSE;
	}
}

?>