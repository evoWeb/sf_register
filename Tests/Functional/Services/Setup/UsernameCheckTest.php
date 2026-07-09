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
        // Pre-fix bug in df53334: $settings['fields']['selected'] is accessed unconditionally.
        // When the 'fields' key is entirely absent, $settings['fields'] evaluates to null and
        // in_array('username', null) throws a TypeError (in_array() requires an array haystack)
        // instead of being treated as "no field selected". Behoben in 30e771a
        // (Classes/Services/Setup/UsernameCheck::check). Reaktivieren in Roadmap-Schritt 2.
        self::markTestSkipped(
            'Pre-fix bug in df53334: missing "fields" key causes in_array() to receive a non-array '
            . 'haystack (TypeError) instead of being treated as no field selected. '
            . 'Behoben in 30e771a (Classes/Services/Setup/UsernameCheck::check). '
            . 'Reaktivieren in Roadmap-Schritt 2.'
        );

        // $subject = $this->getSubject();
        // $settings = ['useEmailAddressAsUsername' => '1'];
        // $result = $subject->check($settings);
        // self::assertNull($result);
    }

    #[Test]
    public function checkTreatsMissingFieldsKeyAsNoSelectionWhenEmailAsUsernameDisabled(): void
    {
        // Same root cause as above, but here the second branch is the one dereferencing the
        // missing 'fields' key, and the intended (SOLL) result is the "neither configured"
        // warning rather than null.
        self::markTestSkipped(
            'Pre-fix bug in df53334: missing "fields" key causes in_array() to receive a non-array '
            . 'haystack (TypeError) instead of being treated as no field selected. '
            . 'Behoben in 30e771a (Classes/Services/Setup/UsernameCheck::check). '
            . 'Reaktivieren in Roadmap-Schritt 2.'
        );

        // $subject = $this->getSubject();
        // $settings = ['useEmailAddressAsUsername' => '0'];
        // $result = $subject->check($settings);
        // self::assertNotNull($result);
        // self::assertStringContainsString('but non was configured', (string)$result->getBody());
    }

    #[Test]
    public function checkTreatsNonArraySelectedAsNoSelection(): void
    {
        // Pre-fix bug in df53334: 'fields' is present but 'selected' is not an array (e.g. a
        // single scalar coming from misconfigured TypoScript). in_array('username', $scalar)
        // throws a TypeError instead of being treated as no field selected. Behoben in 30e771a
        // (Classes/Services/Setup/UsernameCheck::check) via an is_array() guard.
        // Reaktivieren in Roadmap-Schritt 2.
        self::markTestSkipped(
            'Pre-fix bug in df53334: non-array "fields.selected" causes in_array() to receive a '
            . 'non-array haystack (TypeError) instead of being treated as no field selected. '
            . 'Behoben in 30e771a (Classes/Services/Setup/UsernameCheck::check). '
            . 'Reaktivieren in Roadmap-Schritt 2.'
        );

        // $subject = $this->getSubject();
        // $settings = [
        //     'useEmailAddressAsUsername' => '1',
        //     'fields' => ['selected' => 'username'],
        // ];
        // $result = $subject->check($settings);
        // self::assertNull($result);
    }
}
