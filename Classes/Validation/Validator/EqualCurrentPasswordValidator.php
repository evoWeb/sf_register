<?php

namespace Evoweb\SfRegister\Validation\Validator;

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

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

            /** @var \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory $passwordHashFactory */
            $passwordHashFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory::class
            );
            $passwordHash = $passwordHashFactory->get($user->getPassword(), 'FE');
            if (!$passwordHash->checkPassword($password, $user->getPassword())) {
                $this->addError(
                    $this->translateErrorMessage('error_changepassword_notequal', 'SfRegister'),
                    1301599507
                );
            }
        }
    }

    public function userIsLoggedIn(): bool
    {
        /** @var \TYPO3\CMS\Core\Context\UserAspect $userAspect */
        $userAspect = $this->context->getAspect('frontend.user');
        return $userAspect->isLoggedIn();
    }
}
