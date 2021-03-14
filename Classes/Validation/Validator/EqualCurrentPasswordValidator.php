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

use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator to check against current password
 */
class EqualCurrentPasswordValidator extends AbstractValidator
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    protected ?Context $context = null;

    protected ?FrontendUserRepository $userRepository = null;

    protected ?ConfigurationManager $configurationManager = null;

    protected array $settings = [];

    public function injectContext(Context $context)
    {
        $this->context = $context;
    }

    public function injectUserRepository(FrontendUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
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
            $passwordHashFactory = GeneralUtility::makeInstance(
                PasswordHashFactory::class
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
