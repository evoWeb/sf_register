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
	protected $mailhash = '';

	/**
	 * @var string Number of mobilephone
	 */
	protected $mobilephone = '';

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
		return is_bool($this->disable) ? $this->disable : FALSE;
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
}

?>