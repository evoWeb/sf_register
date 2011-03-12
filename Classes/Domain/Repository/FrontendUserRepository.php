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
 * A repository for feusers
 */
class Tx_SfRegister_Domain_Repository_FrontendUserRepository extends Tx_Extbase_Domain_Repository_FrontendUserRepository {
	/**
	 * (non-PHPdoc)
	 *
	 * @see Tx_Extbase_Persistence_Repository::findAll()
	 * @return Tx_Extbase_Persistence_ObjectStorage
	 */
	public function findAll() {
		$query = $this->createQuery();

		$query->getQuerySettings()->setRespectStoragePage(FALSE);

		return $query->execute();
	}

	/**
	 * Finds an object matching the given identifier.
	 *
	 * @param int $uid The identifier of the object to find
	 * @return object The matching object if found, otherwise NULL
	 * @api
	 */
	public function findByUid($uid) {
		if ($this->identityMap->hasIdentifier($uid, $this->objectType)) {
			$object = $this->identityMap->getObjectByIdentifier($uid, $this->objectType);
		} else {
			$query = $this->createQuery();
			$query->getQuerySettings()->setRespectSysLanguage(FALSE);
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
			$result = $query->matching($query->equals('uid', $uid))->execute();
			$object = NULL;
			if (count($result) > 0) {
				$object = current($result);
				$this->identityMap->registerObject($object, $uid);
			}
		}
		return $object;
	}

	/**
	 * Find user by mailhash
	 *
	 * @param string $mailhash
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	public function findByMailhash($mailhash) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectEnableFields(FALSE);

		$data = $query
			->matching($query->equals('mailhash', $mailhash))
			->setLimit(1)
			->execute();

		return current($data);
	}
}

?>