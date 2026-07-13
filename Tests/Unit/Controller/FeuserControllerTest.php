<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Unit\Controller;

use Evoweb\SfRegister\Controller\FeuserController;
use Evoweb\SfRegister\Domain\Repository\FrontendUserRepository;
use Evoweb\SfRegister\Services\File as FileService;
use Evoweb\SfRegister\Services\ModifyValidator;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FeuserControllerTest extends UnitTestCase
{
    /**
     * FeuserController is abstract and only carries constructor dependencies that
     * encryptPassword() does not touch, so mocks are sufficient here.
     */
    protected function getSubject(): FeuserController
    {
        return new class (
            $this->createMock(ModifyValidator::class),
            $this->createMock(FileService::class),
            $this->createMock(FrontendUserRepository::class),
        ) extends FeuserController {};
    }

    #[Test]
    public function encryptPasswordHashesPlaintextPasswordVerifiableByTypo3HashMechanism(): void
    {
        $plaintext = 'S3cur3-Pa$5w0rd';

        $result = $this->getSubject()->encryptPassword($plaintext);

        self::assertNotSame($plaintext, $result);

        /** @var PasswordHashFactory $passwordHashFactory */
        $passwordHashFactory = GeneralUtility::makeInstance(PasswordHashFactory::class);
        $passwordHash = $passwordHashFactory->getDefaultHashInstance('FE');
        self::assertTrue($passwordHash->checkPassword($plaintext, $result));
    }

    #[Test]
    public function encryptPasswordThrowsTypeErrorForEmptyPassword(): void
    {
        // Characterizes df53334 behaviour: PasswordHashInterface::getHashedPassword() returns null
        // for an empty password (see Argon2idPasswordHash::getHashedPassword()), and encryptPassword()
        // returns that null through its `: string` return type -> uncaught TypeError (not an Exception,
        // so the surrounding try/catch does not help). 30e771a's `(string)time()` fallback removes the
        // TypeError = behaviour change, not a pure type-fix. This test goes RED when 30e771a is
        // cherry-picked -> revert that behaviour-changing part in 30e771a; the real fix belongs in a
        // later step.
        $this->expectException(\TypeError::class);

        $this->getSubject()->encryptPassword('');
    }
}
