<?php
namespace Evoweb\SfRegister\Validation\Validator;

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

use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;

/**
 * Validator to check against current password
 *
 * @scope singleton
 */
class EqualCurrentPasswordValidator extends AbstractValidator implements ValidatorInterface
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * Configuration manager
     *
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * Settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Frontend user repository
     *
     * @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $userRepository = null;


    /**
     * Inject a configuration manager
     *
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     *
     * @return void
     */
    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * Validation method
     *
     * @param mixed $password
     *
     * @return bool
     */
    public function isValid($password)
    {
        $result = true;

        if (!\Evoweb\SfRegister\Services\Login::isLoggedIn()) {
            $this->addError(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'error_changepassword_notloggedin',
                    'SfRegister'
                ),
                1301599489
            );
            $result = false;
        } else {
            $user = $this->userRepository->findByUid($GLOBALS['TSFE']->fe_user->user['uid']);

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')
                && \TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('FE')
            ) {
                /** @var \TYPO3\CMS\Saltedpasswords\Salt\SaltInterface $saltedPassword */
                $saltedPassword = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance(
                    $user->getPassword(),
                    null
                );
                if (!$saltedPassword->checkPassword($password, $user->getPassword())) {
                    $this->addError(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'error_changepassword_notequal',
                            'SfRegister'
                        ),
                        1301599507
                    );
                    $result = false;
                }
            } elseif ($this->settings['encryptPassword'] === 'md5') {
                if (md5($password) !== $user->getPassword()) {
                    $this->addError(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'error_changepassword_notequal',
                            'SfRegister'
                        ),
                        1301599507
                    );
                    $result = false;
                }
            } elseif ($this->settings['encryptPassword'] === 'sha1') {
                if (sha1($password) !== $user->getPassword()) {
                    $this->addError(
                        \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                            'error_changepassword_notequal',
                            'SfRegister'
                        ),
                        1301599507
                    );
                    $result = false;
                }
            }
        }

        return $result;
    }
}
