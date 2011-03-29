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
	protected $userRepository = NULL;

	/**
	 * @var Tx_SfRegister_Services_File
	 */
	protected $fileService;

	/**
	 * Initialize all actions
	 *
	 * @see Tx_Extbase_MVC_Controller_ActionController::initializeAction()
	 * @return void
	 */
	protected function initializeAction() {
		$this->userRepository = t3lib_div::makeInstance('Tx_SfRegister_Domain_Repository_FrontendUserRepository');
	}

	/**
	 * Form action
	 *
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
	 * Initialization of preview
	 *
	 * @return void
	 */
	protected function initializePreviewAction() {
		$this->fileService = t3lib_div::makeInstance('Tx_SfRegister_Services_File', 'image');
		$this->fileService->setRequest($this->request);
	}

	/**
	 * Initialization of preview
	 *
	 * @return void
	 */
	protected function initializeProxyAction() {
		$this->fileService = t3lib_div::makeInstance('Tx_SfRegister_Services_File', 'image');
		$this->fileService->setRequest($this->request);
	}

	/**
	 * Proxy action
	 *
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
	 * Remove an image and forward to the action where it was called
	 *
	 * @return void
	 */
	public function removeImageAction() {
		$this->fileService->removeImage($filename);

		if ($this->request->hasArgument('forward')) {
			$this->forward($this->request->getArgument('forward'));
		}
	}

	/**
	 * Encrypt the password
	 *
	 * @param string $password
	 * @return string
	 */
	protected function encryptPassword($password) {
		if (t3lib_extMgm::isLoaded('saltedpasswords') && tx_saltedpasswords_div::isUsageEnabled('FE')) {
			$saltObject = tx_saltedpasswords_salts_factory::getSaltingInstance(NULL);

			if (is_object($saltObject)) {
				$password = $saltObject->getHashedPassword($password);
			}
		} elseif ($this->settings['encryptPassword'] === 'md5') {
			$password = md5($password);
		} elseif ($this->settings['encryptPassword'] === 'sha1') {
			$password = sha1($password);
		}

		return $password;
	}
}

?>