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
 * Service to handle user logins
 */
class Tx_SfRegister_Services_Login implements t3lib_Singleton {
	/**
	 * Login user by id
	 *
	 * @param integer $userid
	 * @return void
	 */
	public function loginUserById($userid) {
		$this->initFEuser($this->fetchUserdata($userid));
		$GLOBALS['TSFE']->fe_user->createUserSession($this->fetchUserdata($userid));
		$GLOBALS['TSFE']->initUserGroups();
		$GLOBALS['TSFE']->setSysPageWhereClause();
	}

	/**
	 * Initialize fe_user object
	 * 
	 * @param array $userdata
	 * @return void
	 */
	protected function initFEuser(array $userdata) {
		/** @var $feUser tslib_feUserAuth */
		$feUser = t3lib_div::makeInstance('tslib_feUserAuth');

		$feUser->lockIP = $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'];
		$feUser->checkPid = $GLOBALS['TYPO3_CONF_VARS']['FE']['checkFeUserPid'];
		$feUser->lifetime = intval($GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime']);
		$feUser->checkPid_value = $GLOBALS['TYPO3_DB']->cleanIntList(t3lib_div::_GP('pid'));	// List of pid's acceptable

		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie']) {
			$feUser->dontSetCookie = 1;
		}

		$feUser->start();
		$feUser->unpack_uc('');
		$feUser->fetchSessionData();

		$userdata[$feUser->lastLogin_column] = $GLOBALS['EXEC_TIME'];
		$userdata['is_online'] = $GLOBALS['EXEC_TIME'];
		$feUser->user = $userdata;

		$GLOBALS['TSFE']->fe_user = &$feUser;

		$this->updateLastLogin($feUser);

			// Call hook for possible manipulation of frontend user object
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'])) {
			$parameters = array('pObj' => &$GLOBALS['TSFE']);
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'] as $functionReference) {
				t3lib_div::callUserFunction($functionReference, $parameters, $GLOBALS['TSFE']);
			}
		}
	}

	/**
	 * For every 60 seconds the is_online timestamp is updated.
	 *
	 * @param 	tslib_feUserAuth	$feUser
	 * @return	void
	 */
	protected function updateLastLogin(tslib_feUserAuth $feUser) {
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'fe_users',
			'uid = ' . intval($feUser->user['uid']),
			array(
				$feUser->lastLogin_column => $GLOBALS['EXEC_TIME'],
				'is_online' => $GLOBALS['EXEC_TIME']
			)
		);
	}

	/**
	 * Fetch user data from fe_user table
	 *
	 * @param integer $uid
	 * @return array
	 */
	protected function fetchUserdata($uid) {
		return current($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'fe_users', 'uid = ' . (int) $uid));
	}
}

?>