<?php
namespace Evoweb\SfRegister\Controller;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Evoweb\SfRegister\Domain\Model\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * An frontend user controller
 */
class FeuserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * User repository
     *
     * @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $userRepository = null;

    /**
     * Usergroup repository
     *
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
     * @inject
     */
    protected $userGroupRepository = null;

    /**
     * File service
     *
     * @var \Evoweb\SfRegister\Services\File
     */
    protected $fileService;

    /**
     * Signal slot dispatcher
     *
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * The current view, as resolved by resolveView()
     *
     * @var \TYPO3\CMS\Fluid\View\TemplateView
     * @api
     */
    protected $view;

    /**
     * Proxy action
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @return void
     * @validate $user Evoweb.SfRegister:User
     */
    public function proxyAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        $action = 'save';

        if ($this->request->hasArgument('form')) {
            $action = 'form';
        }

        $this->forward($action);
    }

    /**
     * Disable Flashmessages
     *
     * @return boolean
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * Initialize all actions
     *
     * @see \TYPO3\CMS\Extbase\Mvc\Controller\ActionController::initializeAction()
     * @return void
     */
    protected function initializeAction()
    {
        $this->fileService = $this->objectManager->get(\Evoweb\SfRegister\Services\File::class);

        if ($this->settings['processInitializeActionSignal']) {
            $this->signalSlotDispatcher->dispatch(
                __CLASS__,
                __FUNCTION__,
                array('controller' => $this, 'settings' => $this->settings,)
            );
        }

        if ($this->request->getControllerActionName() != 'removeImage'
            && $this->request->hasArgument('removeImage')
            && $this->request->getArgument('removeImage')
        ) {
            $this->forward('removeImage');
        }
    }

    /**
     * Inject an view object to be able to set templateRootPath from flexform
     *
     * @param \TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     * @return void
     */
    protected function initializeView(\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view)
    {
        if (isset($this->settings['templateRootPath']) && !empty($this->settings['templateRootPath'])) {
            $templateRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(
                $this->settings['templateRootPath'],
                true
            );
            if (\TYPO3\CMS\Core\Utility\GeneralUtility::isAllowedAbsPath($templateRootPath)) {
                $this->view->setTemplateRootPaths(array($templateRootPath));
            }
        }
    }


    /**
     * Remove an image and forward to the action where it was called
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $imagefile
     * @return void
     * @ignorevalidation $user
     */
    protected function removeImageAction(\Evoweb\SfRegister\Domain\Model\FrontendUser $user, $imagefile)
    {
        if ($this->fileIsTemporary()) {
            $removedImage = $this->fileService->removeTemporaryFile($imagefile);
        } else {
            $removedImage = $this->fileService->removeUploadedImage($imagefile);
        }

        $user = $this->removeImageFromUserAndRequest($user, $removedImage);

        $requestUser = $this->request->getArgument('user');
        $requestUser['image'] = $user->getImage();
        $this->request->setArgument('user', $requestUser);

        $this->request->setArgument('removeImage', false);

        $referrer = $this->request->getInternalArgument('__referrer');
        if ($referrer !== null) {
            $this->forward(
                $referrer['@action'],
                $referrer['@controller'],
                $referrer['@extension'],
                $this->request->getArguments()
            );
        }
    }

    /**
     * Check if a file is only temporary uploaded
     *
     * @return boolean
     */
    protected function fileIsTemporary()
    {
        $result = false;

        if ($this->request->hasArgument('temporary') && $this->request->getArgument('temporary') != '') {
            $result = true;
        }

        return $result;
    }

    /**
     * Remove an image from user object and request object
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $removeImage
     * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected function removeImageFromUserAndRequest(\Evoweb\SfRegister\Domain\Model\FrontendUser $user, $removeImage)
    {
        if ($user->getUid() !== null) {
            $localUser = $this->userRepository->findByUid($user->getUid());
            $localUser->removeImage($removeImage);
            $this->userRepository->update($localUser);

            $this->persistAll();
        }

        $user->removeImage($removeImage);

        $requestUser = $this->request->getArgument('user');
        $requestUser['image'] = $user->getImage();
        $this->request->setArgument('user', $requestUser);

        return $user;
    }

    /**
     * Move uploaded image and add to user
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected function moveTempFile($user)
    {
        if (($file = $this->fileService->moveTempFileToTempFolder())) {
            /** @var ResourceFactory $resourceFactory */
            $resourceFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
            $fileReference = $resourceFactory->createFileReferenceObject(array(
                // uid of image
                'uid_local' => $file->getUid(),
                // uid of user
                'uid_foreign' => $user->getUid(),
                // uid of reference
                'uid' => uniqid('NEW_'),
            ));
            /** @var $image \Evoweb\SfRegister\Domain\Model\FileReference */
            /*$image = $this->objectManager->get('Evoweb\\SfRegister\\Domain\\Model\\FileReference');
            $image->setOriginalResource($fileReference);*/
            $user->addImage($fileReference);
        }

        return $user;
    }

    /**
     * Move uploaded image and add to user
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     *
     * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected function moveImageFile($user)
    {
        $images = $user->getImages();

        $imagesToMove = array();
        /** @var FileReference $image */
        foreach ($images as $image) {
            if ($image->getOriginalResource()->getStorage()->getUid() == 0) {
                $imagesToMove[] = $image;
            }
        }

        $this->fileService->moveFileFromTempFolderToUploadFolder($imagesToMove);

        return $user;
    }

    /**
     * Encrypt the password
     *
     * @param string $password
     * @param array $settings
     * @return string
     */
    public static function encryptPassword($password, $settings)
    {
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')
            && \TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE')
        ) {
            $saltObject = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance(null);
            if (is_object($saltObject)) {
                $password = $saltObject->getHashedPassword($password);
            }
        } elseif ($settings['encryptPassword'] === 'md5') {
            $password = md5($password);
        } elseif ($settings['encryptPassword'] === 'sha1') {
            $password = sha1($password);
        }

        return $password;
    }

    /**
     * Persist all data that was not stored by now
     *
     * @return void
     */
    protected function persistAll()
    {
        $this->objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager::class)->persistAll();
    }

    /**
     * Redirect to a page with given id
     *
     * @param integer $pageId
     * @return void
     */
    protected function redirectToPage($pageId)
    {
        $url = $this->uriBuilder->setTargetPageUid($pageId)->build();
        $this->redirectToUri($url);
    }

    /**
     * Send emails to user and/or to admin
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param string $type
     * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected function sendEmails($user, $type)
    {
        /** @var $mailService \Evoweb\SfRegister\Services\Mail */
        $mailService = $this->objectManager->get(\Evoweb\SfRegister\Services\Mail::class);

        if ($this->isNotifyAdmin($type)) {
            $user = $mailService->sendAdminNotification($user, $type);
        }

        if ($this->isNotifyUser($type)) {
            $user = $mailService->sendUserNotification($user, $type);
        }

        return $user;
    }

    /**
     * Check if the admin need to activate the account
     *
     * @param string $type
     * @return boolean
     */
    protected function isNotifyAdmin($type)
    {
        $result = false;

        if ($this->settings['notifyAdmin' . $type]) {
            $result = true;
        }

        return $result;
    }

    /**
     * Check if the user need to activate the account
     *
     * @param string $type
     * @return boolean
     */
    protected function isNotifyUser($type)
    {
        $result = false;

        if ($this->settings['notifyUser' . $type]) {
            $result = true;
        }

        return $result;
    }

    /**
     * Determines whether a user is in a given user group.
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup|string|int $userGroup
     * @return bool
     */
    protected function isUserInUserGroup(\Evoweb\SfRegister\Domain\Model\FrontendUser $user, $userGroup)
    {
        if ($userGroup instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup) {
            return $user->getUsergroup()->contains($userGroup);
        } elseif (!empty($userGroup)) {
            $userGroupUids = $this->getEntityUids(
                $user->getUsergroup()->toArray()
            );

            return in_array($userGroup, $userGroupUids);
        }

        return false;
    }

    /**
     * Determines whether a user is in a given user group.
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param array|\TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup[] $userGroups
     * @return bool
     */
    protected function isUserInUserGroups(\Evoweb\SfRegister\Domain\Model\FrontendUser $user, array $userGroups)
    {
        foreach ($userGroups as $userGroup) {
            if ($this->isUserInUserGroup($user, $userGroup)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user has a given usergroup currently set
     *
     * @param int $currentUserGroup
     * @param bool $excludeCurrentUserGroup
     * @return array
     */
    protected function getFollowingUserGroups($currentUserGroup, $excludeCurrentUserGroup = false)
    {
        $followingUserGroups = [];
        $userGroups = $this->getUserGroupIds();
        $currentIndex = array_search((int) $currentUserGroup, $userGroups);
        $additionalIndex = ($excludeCurrentUserGroup ? 1 : 0);
        if ($currentUserGroup !== false && $currentUserGroup < count($userGroups)) {
            $followingUserGroups = array_slice($userGroups, $currentIndex + $additionalIndex);
        }

        return $followingUserGroups;
    }

    /**
     * Get all configured usergroups
     *
     * @return array
     */
    protected function getUserGroupIds()
    {
        $userGroups = [];
        $settingsUserGroupKeys = array('usergroup', 'usergroupPostSave', 'usergroupPostConfirm', 'usergroupPostAccept');
        foreach ($settingsUserGroupKeys as $settingsUserGroupKey) {
            $userGroup = (int) $this->settings[$settingsUserGroupKey];
            if ($userGroup) {
                $userGroups[] = $userGroup;
            }
        }

        return $userGroups;
    }

    /**
     * Gets the uid of each given entity.
     *
     * @param array|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity[] $entities
     * @return array
     */
    protected function getEntityUids(array $entities)
    {
        $entityUids = [];
        foreach ($entities as $entity) {
            $entityUids[] = $entity->getUid();
        }

        return $entityUids;
    }

    /**
     * Login user with service
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @return void
     */
    protected function autoLogin(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        $this->objectManager->get(\Evoweb\SfRegister\Services\Login::class)->loginUserById($user->getUid());
    }

    /**
     * Determines the frontend user, either if it's
     * already submitted, or by looking up the mail hash code.
     *
     * @param NULL|\Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param NULL|string $hash
     * @return NULL|\Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected function determineFrontendUser(\Evoweb\SfRegister\Domain\Model\FrontendUser $user = null, $hash = null)
    {
        $frontendUser = null;

        $requestArguments = $this->request->getArguments();
        if ($user !== null && $hash !== null) {
            $calculatedHash = \TYPO3\CMS\Core\Utility\GeneralUtility::hmac(
                $requestArguments['action'] . '::' . $user->getUid()
            );
            if ($hash === $calculatedHash) {
                $frontendUser = $user;
            }
            // @deprecated authCode is still there for backward compatibility
        } elseif (!empty($requestArguments['authCode'])) {
            $frontendUser = $this->userRepository->findByMailhash($requestArguments['authCode']);
        }

        return $frontendUser;
    }

    /**
     * Change usergroup of user after activation
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @param integer $usergroupIdToBeRemoved
     * @param integer $usergroupIdToAdd
     * @return \Evoweb\SfRegister\Domain\Model\FrontendUser
     */
    protected function changeUsergroup(
        \Evoweb\SfRegister\Domain\Model\FrontendUser $user,
        $usergroupIdToAdd,
        $usergroupIdToBeRemoved = 0
    ) {
        $this->userGroupRepository = $this->objectManager->get(
            \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository::class
        );

        // cover deprecated behaviour
        if ($usergroupIdToBeRemoved) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(
                'Usage of $usergroupIdToBeRemoved and $usergroupIdToAdd is deprecated
                please use only $usergroupIdToAdd as all groups previously set get removed'
            );
            $usergroupIdToAdd = $usergroupIdToBeRemoved;
        }

        $this->removePreviousUserGroups($user);

        $usergroupIdToAdd = (int) $usergroupIdToAdd;
        if ($usergroupIdToAdd) {
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroupToAdd */
            $usergroupToAdd = $this->userGroupRepository->findByUid($usergroupIdToAdd);
            $user->addUsergroup($usergroupToAdd);
        }

        return $user;
    }

    /**
     * Removes all frontend usergroups that were set in previous actions
     *
     * @param \Evoweb\SfRegister\Domain\Model\FrontendUser $user
     * @return void
     */
    protected function removePreviousUserGroups(\Evoweb\SfRegister\Domain\Model\FrontendUser $user)
    {
        $userGroupIds = $this->getUserGroupIds();
        foreach ($userGroupIds as $userGroupId) {
            /** @var \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup $usergroupToRemove */
            $usergroupToRemove = $this->userGroupRepository->findByUid($userGroupId);
            $user->removeUsergroup($usergroupToRemove);
        }
    }
}
