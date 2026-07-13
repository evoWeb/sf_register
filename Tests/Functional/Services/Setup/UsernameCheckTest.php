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

namespace Evoweb\SfRegister\Tests\Functional\Services\Setup;

use Evoweb\SfRegister\Services\Setup\UsernameCheck;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * NOTE: Despite the "UsernameCheck" name and the task description this class was drafted from
 * (which anticipated a database lookup against fe_users for a colliding username), the actual
 * check() method under test performs no database access at all. It purely validates the plugin
 * TypoScript setup (the interplay of settings.useEmailAddressAsUsername and
 * settings.fields.selected) and returns a warning HtmlResponse when that setup is inconsistent,
 * or null when it is fine. This is confirmed by CheckFactory (Classes/Services/Setup/CheckFactory.php)
 * and the sibling UserGroupCheck/AutologinCheck classes, which follow the identical
 * settings-in/response-out pattern with no repository or QueryBuilder involved.
 *
 * The class is kept in Tests/Functional to match the location mandated by the task brief, but no
 * fe_users fixture is imported since check() never queries it.
 */
class UsernameCheckTest extends AbstractTestBase
{
    protected function getSubject(): UsernameCheck
    {
        return new UsernameCheck();
    }

    /**
     * @return array<string, array{0: array<string, string|array<string, int|array<int, string>>>}>
     */
    public static function validSetupDataProvider(): array
    {
        return [
            'email used as username, username field not selected' => [
                [
                    'useEmailAddressAsUsername' => '1',
                    'fields' => ['selected' => ['email', 'name']],
                ],
            ],
            'username field selected, email not used as username' => [
                [
                    'useEmailAddressAsUsername' => '0',
                    'fields' => ['selected' => ['username', 'email']],
                ],
            ],
        ];
    }

    /**
     * @param array<string, string|array<string, int|array<int, string>>> $settings
     */
    #[DataProvider('validSetupDataProvider')]
    #[Test]
    public function checkReturnsNullForConsistentSetup(array $settings): void
    {
        $subject = $this->getSubject();

        self::assertNull($subject->check($settings));
    }

    #[Test]
    public function checkReturnsWarningResponseWhenBothEmailAndUsernameFieldAreConfigured(): void
    {
        $subject = $this->getSubject();
        $settings = [
            'useEmailAddressAsUsername' => '1',
            'fields' => ['selected' => ['username', 'email']],
        ];

        $result = $subject->check($settings);

        self::assertNotNull($result);
        self::assertSame(200, $result->getStatusCode());
        self::assertStringContainsString(
            'but not both should be configured',
            (string)$result->getBody()
        );
    }

    #[Test]
    public function checkReturnsWarningResponseWhenNeitherEmailNorUsernameFieldIsConfigured(): void
    {
        $subject = $this->getSubject();
        $settings = [
            'useEmailAddressAsUsername' => '0',
            'fields' => ['selected' => ['email', 'name']],
        ];

        $result = $subject->check($settings);

        self::assertNotNull($result);
        self::assertSame(200, $result->getStatusCode());
        self::assertStringContainsString(
            'but non was configured',
            (string)$result->getBody()
        );
    }

    #[Test]
    public function checkTreatsMissingFieldsKeyAsNoSelectionWhenEmailAsUsernameEnabled(): void
    {
        // A missing 'fields' key is treated as "no field selected" (is_array guards) instead of
        // raising a TypeError from in_array() on a non-array haystack.
        $subject = $this->getSubject();
        $settings = ['useEmailAddressAsUsername' => '1'];

        self::assertNull($subject->check($settings));
    }

    #[Test]
    public function checkTreatsMissingFieldsKeyAsNoSelectionWhenEmailAsUsernameDisabled(): void
    {
        // Same root cause as above; here the "neither configured" branch is reached and returns the
        // warning response instead of raising a TypeError.
        $subject = $this->getSubject();
        $settings = ['useEmailAddressAsUsername' => '0'];

        $result = $subject->check($settings);

        self::assertNotNull($result);
        self::assertStringContainsString('but non was configured', (string)$result->getBody());
    }

    #[Test]
    public function checkTreatsNonArraySelectedAsNoSelection(): void
    {
        // A non-array 'fields.selected' (e.g. a scalar from misconfigured TypoScript) is treated as
        // "no field selected" (is_array guard) instead of raising a TypeError.
        $subject = $this->getSubject();
        $settings = [
            'useEmailAddressAsUsername' => '1',
            'fields' => ['selected' => 'username'],
        ];

        self::assertNull($subject->check($settings));
    }
}
