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
	 * User repository
	 *
	 * @var Tx_SfRegister_Domain_Model_FrontendUserRepository
	 */
	protected $userRepository = NULL;

	/**
	 * File service
	 *
	 * @var Tx_SfRegister_Services_File
	 */
	protected $fileService;

	/**
	 * Inject an frontenduser repository object
	 *
	 * @param Tx_SfRegister_Domain_Repository_FrontendUserRepository $repository
	 * @return void
	 */
	public function injectUserRepository(Tx_SfRegister_Domain_Repository_FrontendUserRepository $repository) {
		$this->userRepository = $repository;
	}

	/**
	 * Initialize all actions
	 *
	 * @see Tx_Extbase_MVC_Controller_ActionController::initializeAction()
	 * @return void
	 */
	protected function initializeAction() {
		$this->fileService = $this->objectManager->get('Tx_SfRegister_Services_File');

		if ($this->request->getControllerActionName() != 'removeImage' &&
				$this->request->hasArgument('removeImage') &&
				$this->request->getArgument('removeImage')) {
			$this->forward('removeImage');
		}
	}

	/**
	 * Inject an view object to be able to set templateRootPath from flexform
	 *
	 * @param Tx_Extbase_MVC_View_ViewInterface $view
	 * @return void
	 */
	protected function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
		if (isset($this->settings['templateRootPath']) && !empty($this->settings['templateRootPath'])) {
			$templateRootPath = t3lib_div::getFileAbsFileName($this->settings['templateRootPath'], TRUE);
			if (t3lib_div::isAllowedAbsPath($templateRootPath)) {
				$this->view->setTemplateRootPath($templateRootPath);
			}
		}
	}

	/**
	 * Proxy action
	 *
	 * @param Tx_SfRegister_Interfaces_FrontendUser $user
	 * @return void
	 * @validate $user Tx_SfRegister_Domain_Validator_UserValidator
	 */
	public function proxyAction(Tx_SfRegister_Domain_Model_FrontendUser $user) {
		$action = 'save';

		if ($this->request->hasArgument('form')) {
			$action = 'form';
		}

		$this->forward($action);
	}


	/**
	 * Check if the user is logged in
	 *
	 * @return boolean
	 */
	protected function isUserLoggedIn() {
			// @TODO move this into a scope outside of controller
		return $GLOBALS['TSFE']->fe_user->user === FALSE ? FALSE : TRUE;
	}

	/**
	 * Remove an image and forward to the action where it was called
	 *
	 * @param Tx_SfRegister_Interfaces_FrontendUser $user
	 * @param string $imagefile
	 * @return void
	 * @dontvalidate $user
 	 */
	protected function removeImageAction(Tx_SfRegister_Interfaces_FrontendUser $user, $imagefile) {
		if ($this->fileIsTemporary()) {
			$removedImage = $this->fileService->removeTemporaryFile($imagefile);
		} else {
			$removedImage = $this->fileService->removeUploadedImage($imagefile);
		}
		$user = $this->removeImageFromUserAndRequest($user, $removedImage);
			// @TODO can this get removed? testing
		$user->removeImage($removedImage);
		$requestUser = $this->request->getArgument('user');
		$requestUser['image'] = $user->getImage();
		$this->request->setArgument('user', $requestUser);

		$this->request->setArgument('removeImage', FALSE);

		if ($this->request->hasArgument('__referrer')) {
			$referrer = $this->request->getArgument('__referrer');
			$cryptographyHashService = t3lib_div::makeInstance('Tx_Extbase_Security_Cryptography_HashService');
			if (method_exists($cryptographyHashService, 'validateAndStripHmac')) {
				if (isset($referrer['@request'])) {
					$referrer = unserialize($cryptographyHashService->validateAndStripHmac($referrer['@request']));
					$this->forward($referrer['actionName'], $referrer['controllerName'], $referrer['extensionName'], $this->request->getArguments());
				}
			} else {
				$this->forward($referrer['actionName'], $referrer['controllerName'], $referrer['extensionName'], $this->request->getArguments());
			}
		}
	}

	/**
	 * Remove an image from user object and request object
	 *
	 * @param Tx_SfRegister_Interfaces_FrontendUser $user
	 * @param string $removeImage
	 * @return Tx_SfRegister_Interfaces_FrontendUser
	 */
	protected function removeImageFromUserAndRequest(Tx_SfRegister_Interfaces_FrontendUser $user, $removeImage) {
		if ($user->getUid() !== NULL) {
			$localUser = $this->userRepository->findByUid($user->getUid());
			$localUser->removeImage($removeImage);
			$this->userRepository->update($localUser);

			$this->objectManager->get('Tx_Extbase_Persistence_Manager')->persistAll();
		}

		$user->removeImage($removeImage);

		$requestUser = $this->request->getArgument('user');
		$requestUser['image'] = $user->getImage();
		$this->request->setArgument('user', $requestUser);

		return $user;
	}

	/**
	 * Check if a file is only temporary uploaded
	 *
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
	 * @param Tx_SfRegister_Interfaces_FrontendUser $user
	 * @return Tx_SfRegister_Interfaces_FrontendUser
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
	 * @param Tx_SfRegister_Interfaces_FrontendUser $user
	 * @return Tx_SfRegister_Interfaces_FrontendUser
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