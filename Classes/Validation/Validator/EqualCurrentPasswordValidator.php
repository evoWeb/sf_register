<?php
namespace Evoweb\SfRegister\Validation\Validator;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
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
 * Validator to check against current password
 */
class EqualCurrentPasswordValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var \TYPO3\CMS\Core\Context\Context
     */
    protected $context;

    /**
     * @var \Evoweb\SfRegister\Domain\Repository\FrontendUserRepository
     */
    protected $userRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings = [];

    public function injectContext(\TYPO3\CMS\Core\Context\Context $context)
    {
        $this->context = $context;
    }

    public function injectUserRepository(\Evoweb\SfRegister\Domain\Repository\FrontendUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function injectConfigurationManager(
        \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
    ) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
    }

    /**
     * Validation method
     *
     * @param mixed $password
     */
    public function isValid($password)
    {
        if (!$this->userIsLoggedIn()) {
            $this->addError(
                $this->translateErrorMessage('error_changepassword_notloggedin', 'SfRegister'),
                1301599489
            );
        } else {
            $user = $this->userRepository->findByUid($this->context->getAspect('frontend.user')->get('id'));

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
                /** @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory $passwordHashFactory */
                $passwordHashFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory::class
                );
                $saltedPassword = $passwordHashFactory->get($user->getPassword(), 'FE');
                if (!$saltedPassword->checkPassword($password, $user->getPassword())) {
                    $this->addError(
                        $this->translateErrorMessage('error_changepassword_notequal', 'SfRegister'),
                        1301599507
                    );
                }
            } elseif ($this->settings['encryptPassword'] === 'md5') {
                if (md5($password) !== $user->getPassword()) {
                    $this->addError(
                        $this->translateErrorMessage('error_changepassword_notequal', 'SfRegister'),
                        1301599508
                    );
                }
            } elseif ($this->settings['encryptPassword'] === 'sha1') {
                if (sha1($password) !== $user->getPassword()) {
                    $this->addError(
                        $this->translateErrorMessage('error_changepassword_notequal', 'SfRegister'),
                        1301599509
                    );
                }
            }
        }
    }

    public function userIsLoggedIn(): bool
    {
        return $this->context->getAspect('frontend.user')->isLoggedIn();
    }
}
