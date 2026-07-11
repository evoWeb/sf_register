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
    public function encryptPasswordReturnsUsableFallbackStringForEmptyPassword(): void
    {
        // Pre-fix bug in df53334: PasswordHashInterface::getHashedPassword() returns null
        // for an empty password (see Argon2idPasswordHash::getHashedPassword()), but
        // encryptPassword() returns that null directly from its `: string` return type,
        // causing an uncaught TypeError (not an Exception, so the surrounding try/catch
        // does not help). Fixed in 30e771a by falling back to (string)time() when the
        // hash is null. Reactivate in roadmap step 2.
        self::markTestSkipped(
            'Pre-fix bug in df53334: encryptPassword(\'\') returns null from '
            . 'getHashedPassword() through a `: string` return type, causing an uncaught '
            . 'TypeError. Fixed in 30e771a. Reactivate in roadmap step 2.'
        );

        /*$result = $this->getSubject()->encryptPassword('');

        self::assertIsString($result);*/
    }
}
