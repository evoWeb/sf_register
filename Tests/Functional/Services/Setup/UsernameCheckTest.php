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
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

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
    #[WithoutErrorHandler]
    public function checkThrowsTypeErrorForMissingFieldsKeyWhenEmailAsUsernameEnabled(): void
    {
        // Characterizes df53334 behaviour: $settings['fields']['selected'] is accessed
        // unconditionally. With the 'fields' key entirely absent, $settings['fields'] is null and
        // in_array('username', null) throws an uncaught TypeError (in_array() requires an array
        // haystack). 30e771a adds a guard treating it as "no field selected" (behaviour change, not a
        // pure type-fix), so this test goes RED once 30e771a is cherry-picked -> revert that part in
        // 30e771a; the real fix belongs in a later step.
        //
        // #[WithoutErrorHandler] disables PHPUnit's error handler so the "Undefined array key"/
        // "access offset on null" PHP warnings that precede the characterized TypeError do not fail
        // the test via failOnWarning.
        $subject = $this->getSubject();
        $settings = ['useEmailAddressAsUsername' => '1'];

        $this->expectException(\TypeError::class);

        $subject->check($settings);
    }

    #[Test]
    #[WithoutErrorHandler]
    public function checkThrowsTypeErrorForMissingFieldsKeyWhenEmailAsUsernameDisabled(): void
    {
        // Same root cause as above, but here the second branch dereferences the missing 'fields' key.
        // df53334 throws an uncaught TypeError; 30e771a changes this to a graceful "neither configured"
        // result (behaviour change), so this test goes RED once 30e771a is cherry-picked -> revert that
        // part in 30e771a; the real fix belongs in a later step. #[WithoutErrorHandler] keeps the
        // preceding PHP warnings from failing the test via failOnWarning (see method above).
        $subject = $this->getSubject();
        $settings = ['useEmailAddressAsUsername' => '0'];

        $this->expectException(\TypeError::class);

        $subject->check($settings);
    }

    #[Test]
    public function checkThrowsTypeErrorForNonArraySelected(): void
    {
        // Characterizes df53334 behaviour: 'fields' is present but 'selected' is not an array (e.g. a
        // single scalar from misconfigured TypoScript). in_array('username', $scalar) throws an
        // uncaught TypeError. 30e771a adds an is_array() guard treating it as "no field selected"
        // (behaviour change, not a pure type-fix), so this test goes RED once 30e771a is cherry-picked
        // -> revert that part in 30e771a; the real fix belongs in a later step.
        $subject = $this->getSubject();
        $settings = [
            'useEmailAddressAsUsername' => '1',
            'fields' => ['selected' => 'username'],
        ];

        $this->expectException(\TypeError::class);

        $subject->check($settings);
    }
}
