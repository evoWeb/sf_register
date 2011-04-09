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

		$this->fileService = t3lib_div::makeInstance('Tx_SfRegister_Services_File', 'image');
		$this->fileService->setRequest($this->request);
		$this->fileService->setSettings((array) $this->settings);

		if ($this->request->hasArgument('removeImage') && $this->request->getArgument('removeImage') &&
			$this->request->getControllerActionName() != 'removeImage') {
			$this->forward('removeImage');
		}
	}

	/**
	 * @TODO FIX THIS
	 * @param Tx_Extbase_MVC_View_ViewInterface $view
	 * @return void
	 */
	protected function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
		if (isset($this->settings['templateRootPath']) && !empty($this->settings['templateRootPath'])) {
			$this->view->setTemplateRootPath($this->settings['templateRootPath']);
		}
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
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @param string $imagefile
	 * @return void
	 * @dontvalidate $user
 	 */
	protected function removeImageAction(Tx_SfRegister_Domain_Model_FrontendUser $user, $imagefile) {
		if ($this->fileIsTemporary()) {
			$removedImage = $this->fileService->removeTemporaryFile($imagefile);
		} else {
			$removedImage = $this->fileService->removeUploadedImage($imagefile);
		}
		$user = $this->removeImageFromUserAndRequest($user, $removedImage);

		$user->removeImage($removedImage);
		$requestUser = $this->request->getArgument('user');
		$requestUser['image'] = $user->getImage();
		$this->request->setArgument('user', $requestUser);

		$this->request->setArgument('removeImage', FALSE);

		if ($this->request->hasArgument('__referrer')) {
			$referrer = $this->request->getArgument('__referrer');
			$this->forward($referrer['actionName'], $referrer['controllerName'], $referrer['extensionName'], $this->request->getArguments());
		}
	}

	/**
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
	 */
	protected function removeImageFromUserAndRequest(Tx_SfRegister_Domain_Model_FrontendUser $user, $removeImage) {
		if ($user->getUid() !== NULL) {
			$localUser = $this->userRepository->findByUid($user->getUid());
			$localUser->removeImage($removeImage);
			$this->userRepository->update($localUser);

			$persistenceManager = Tx_Extbase_Dispatcher::getPersistenceManager();
			$persistenceManager->persistAll();
		}

		$user->removeImage($removeImage);

		$requestUser = $this->request->getArgument('user');
		$requestUser['image'] = $user->getImage();
		$this->request->setArgument('user', $requestUser);

		return $user;
	}

	/**
	 * @return boolean
	 */
	protected function fileIsTemporary() {
		$result = FALSE;

		if ($this->request->hasArgument('temporary') && $this->request->getArgument('temporary') != '') {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * Move uploaded image and add to user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
 	 */
	protected function moveTempFile($user) {
		if ($imagePath = $this->fileService->moveTempFileToTempFolder()) {
			$user->addImage($imagePath);
		}

		return $user;
	}

	/**
	 * Move uploaded image and add to user
	 *
	 * @param Tx_SfRegister_Domain_Model_FrontendUser $user
	 * @return Tx_SfRegister_Domain_Model_FrontendUser
 	 */
	protected function moveImageFile($user) {
		$oldFilename = $user->getImage();

		$this->fileService->moveFileFromTempFolderToUploadFolder($oldFilename);

		$user->setImage($oldFilename);

		return $user;
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