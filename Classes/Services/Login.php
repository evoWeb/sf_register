<?php
namespace Evoweb\SfRegister\Services;

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

/**
 * Service to handle user logins
 */
class Login implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * Object manager
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $database;

    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->database = $GLOBALS['TYPO3_DB'];
    }

    /**
     * Login user by id
     *
     * @param integer $userid
     *
     * @return void
     */
    public function loginUserById($userid)
    {
        $this->initFrontendEuser($this->fetchUserdata($userid));

        /**
         * @var $frontend \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
         */
        $frontend = $GLOBALS['TSFE'];
        $frontend->fe_user->createUserSession($this->fetchUserdata($userid));
        $frontend->initUserGroups();
        $frontend->setSysPageWhereClause();
    }

    /**
     * Initialize fe_user object
     *
     * @param array $userdata
     *
     * @return void
     */
    protected function initFrontendEuser(array $userdata)
    {
        /** @var $feUser \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication */
        $feUser = $this->objectManager->get(\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication::class);

        $feUser->lockIP = $GLOBALS['TYPO3_CONF_VARS']['FE']['lockIP'];
        $feUser->checkPid = $GLOBALS['TYPO3_CONF_VARS']['FE']['checkFeUserPid'];
        $feUser->lifetime = intval($GLOBALS['TYPO3_CONF_VARS']['FE']['lifetime']);
        // List of pid's acceptable
        $feUser->checkPid_value = $this->database->cleanIntList(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('pid'));

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
        $feUser->setKey('ses', 'SfRegisterAutoLoginUser', true);

        $this->signalSlotDispatcher->dispatch(__CLASS__, 'save', array('frontend' => &$GLOBALS['TSFE']));
    }

    /**
     * For every 60 seconds the is_online timestamp is updated.
     *
     * @param  \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUser
     *
     * @return void
     */
    protected function updateLastLogin(\TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $feUser)
    {
        $this->database->exec_UPDATEquery('fe_users', 'uid = ' . intval($feUser->user['uid']), array(
                $feUser->lastLogin_column => $GLOBALS['EXEC_TIME'],
                'is_online' => $GLOBALS['EXEC_TIME']
            ));
    }

    /**
     * Fetch user data from fe_user table
     *
     * @param integer $uid
     *
     * @return array
     */
    protected function fetchUserdata($uid)
    {
        return current($this->database->exec_SELECTgetRows('*', 'fe_users', 'uid = ' . (int) $uid));
    }

    /**
     * Check if the user is logged in
     *
     * @return boolean
     */
    public static function isLoggedIn()
    {
        /**
         * @var \TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication $frontendUser
         */
        $frontendUser = $GLOBALS['TSFE']->fe_user;

        return is_array($frontendUser->user);
    }
}
