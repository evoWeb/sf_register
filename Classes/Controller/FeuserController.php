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
 * An frontend user controller
 */
class Tx_SfRegister_Controller_FeuserController extends Tx_Extbase_MVC_Controller_ActionController {
	/**
	 * @var Tx_SfRegister_Domain_Model_FrontendUserRepository
	 */
	protected $userRepository = null;

	/**
	 * @see Tx_Extbase_MVC_Controller_ActionController::initializeAction()
	 * @return void
	 */
	protected function initializeAction() {
		$this->userRepository = t3lib_div::makeInstance('Tx_SfRegister_Domain_Repository_FrontendUserRepository');

	}

	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return string An HTML form
	 * @dontvalidate $user
	 */
	public function formAction(Tx_SfRegister_Domain_Model_FrontendUser $user = NULL) {
		if ($user == NULL && $GLOBALS['TSFE']->fe_user->user != FALSE) {
			$user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);
		}

		$this->view->assign('user', $user);
	}

	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return void
	 * @validate $user Tx_SfRegister_Domain_Validator_UserValidator
	 */
	public function previewAction(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$this->view->assign('user', $user);
	}

	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return void
	 * @validate $user Tx_SfRegister_Domain_Validator_UserValidator
	 */
	public function proxyAction(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		if ($this->request->hasArgument('form')) {
			$action = 'form';
		} else {
			$action = 'save';
		}

		$this->forward($action);
	}

	/**
	 * @param string $pidList
	 * @param integer $recursive
	 * @return string
	 */
	public function getPageIdList($pidList, $recursive = 0) {
		if (!strcmp($pidList, '')) {
			$pidList = $GLOBALS['TSFE']->id;
		}

		$recursive = t3lib_div::intInRange($recursive, 0);

		$pids = array_unique(t3lib_div::trimExplode(',', $pidList, 1));
		$pageIdsForPagesAndChildrens = array();

		foreach ($pids as $pid) {
			$pid = t3lib_div::intInRange($pid, 0);
			if ($pid) {
				$pageIdOfTree = $this->cObj->getTreeList(-1 * $pid, $recursive);
				if ($pageIdOfTree) {
					$pageIdsForPagesAndChildrens[] = $pageIdOfTree;
				}
			}
		}

		return implode(',', $pageIdsForPagesAndChildrens);
	}
}

?>