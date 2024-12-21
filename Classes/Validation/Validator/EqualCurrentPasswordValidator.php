<?php

declare(strict_types=1);

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

use Evoweb\SfRegister\Services\FrontendUser as FrontendUserService;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validator to check against current password
 */
class EqualCurrentPasswordValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    public function __construct(
        protected FrontendUserService $frontendUserService,
        protected PasswordHashFactory $passwordHashFactory,
    ) {
    }

    /**
     * If value is equal with the current password
     */
    public function isValid(mixed $value): void
    {
        try {
            if (!$this->frontendUserService->userIsLoggedIn()) {
                $this->addError(
                    $this->translateErrorMessage('error_changepassword_notloggedin', 'SfRegister'),
                    1301599489
                );
                return;
            }

            $user = $this->frontendUserService->getLoggedInUser();

            $passwordHash = $this->passwordHashFactory->get($user->getPassword(), 'FE');
            if (!$passwordHash->checkPassword((string)$value, $user->getPassword())) {
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
