<?php

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

namespace Evoweb\SfRegister\Validation\Validator;

use Evoweb\SfRegister\Domain\Model\FrontendUser;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator to check against current password
 */
class EqualCurrentPasswordValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    protected array $settings = [];

    public function __construct(
        protected Context $context,
        protected FrontendUserRepository $userRepository,
        ConfigurationManagerInterface $configurationManager
    ) {
        $this->settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'SfRegister',
            'Form'
        );
    }

    /**
     * If value is equal with the current password
     */
    public function isValid(mixed $value): void
    {
        try {
            /** @var UserAspect $userAspect */
            $userAspect = $this->context->getAspect('frontend.user');

            if (!$userAspect->isLoggedIn()) {
                $this->addError(
                    $this->translateErrorMessage('error_changepassword_notloggedin', 'SfRegister'),
                    1301599489
                );
                return;
            }

            /** @var FrontendUser $user */
            $user = $this->userRepository->findByUid($userAspect->get('id'));
            /** @var PasswordHashFactory $passwordHashFactory */
            $passwordHashFactory = GeneralUtility::makeInstance(PasswordHashFactory::class);

            $passwordHash = $passwordHashFactory->get($user->getPassword(), 'FE');
            if (!$passwordHash->checkPassword($value, $user->getPassword())) {
                $this->addError(
                    $this->translateErrorMessage('error_changepassword_notequal', 'SfRegister'),
                    1301599507
                );
            }
        } catch (\Exception $exception) {
            $this->addError($exception->getMessage(), $exception->getCode());
        }
    }
}
