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
class Tx_SfRegister_Domain_Model_FrontendUser extends Tx_Extbase_Domain_Model_FrontendUser {
	/**
	 * @var boolean Flag if user is diable or not
	 */
	protected $disable;

	/**
	 * @var string Hash for confirmation mail
	 */
	protected $mailhash;



	/**
	 * @var  integer 1 or 2 for mr or mrs
	 */
	protected $gender;

	/**
	 * @var  DateTime date of birth
	 */
	protected $dateOfBirth;

	/**
	 * @var string of language
	 */
	protected $language;

	/**
	 * @var string code of zone
	 */
	protected $zone;

	/**
	 * @var string two character iso code of country
	 */
	protected $staticInfoCountry;

	/**
	 * @var string Number of mobilephone
	 */
	protected $mobilephone;

	/**
	 * @var boolean General terms and conditions accepted flag
	 */
	protected $gtc;

	/**
	 * @var integer of employee status
	 */
	protected $status;

	/**
	 * @var boolean wether the user register by invitation
	 */
	protected $byInvitation;

	/**
	 * @var string comment of user
	 */
	protected $comments;

	/**
	 * @var boolean if emails should be send as HTML or plain text
	 */
	protected $moduleSysDmailHtml;

	/**
	 * @var array of selected categories
	 */
	protected $moduleSysDmailCategory;



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
	 * Getter for dateOfBirth
	 *
	 * @return DateTime
	 */
	public function getDateOfBirth() {
		return $this->dateOfBirth;
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
}

?>