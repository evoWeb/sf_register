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

namespace Evoweb\SfRegister\Tests\Functional\Updates;

use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use Evoweb\SfRegister\Updates\UserCountryMigration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Characterization tests for the UserCountryMigration UpgradeWizard.
 *
 * What the migration does: before sf_register v13 the user's country was stored in
 * fe_users.static_info_country as a static_info_tables country *uid* (e.g. 54 = Germany). The
 * core now ships its own country provider, so the wizard rewrites that column IN PLACE to the
 * matching Country enum case NAME (the alpha-2 ISO code, e.g. 54 -> "DE"). getRecordsToUpdate()
 * selects the rows still holding a numeric uid via OR of LIKE 'N%' for N in 1..9 (any value
 * whose first character is a digit 1-9); executeUpdate() maps each via Country::tryFrom(uid)->name
 * and writes it back. Once rewritten to an ISO name the row no longer matches the WHERE, which is
 * how the wizard marks a row done (and gives idempotency: a second run selects nothing).
 */
class UserCountryMigrationTest extends AbstractTestBase
{
    /**
     * @var array<non-empty-string>
     *
     * fe_users.static_info_country is a TCA-only legacy column (added via addTCAcolumns in
     * Configuration/TCA/Overrides/fe_users.php) that no loaded ext_tables.sql declares as a real
     * DB column. The test_classes stub re-adds it (Tests/Fixtures/Extensions/test_classes/
     * ext_tables.sql) so the wizard's raw QueryBuilder can run against fixture data. Mirror the
     * parent list and append nothing new (parent already loads the stub), kept explicit here for
     * clarity of intent.
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/sf_register',
        'typo3conf/ext/sf_register/Tests/Fixtures/Extensions/test_classes',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users_country_migration.csv');
    }

    protected function getSubject(): UserCountryMigration
    {
        /** @var UserCountryMigration $subject */
        $subject = $this->get(UserCountryMigration::class);
        $subject->setOutput(new NullOutput());
        return $subject;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAllRecords(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('fe_users');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid', 'static_info_country')
            ->from('fe_users')
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $uid = $row['uid'] ?? null;
            $result[is_numeric($uid) ? (int)$uid : 0] = $row;
        }
        return $result;
    }

    // -- getRecordsToUpdateCount ------------------------------------------------------------------

    /**
     * getRecordsToUpdate()/getRecordsToUpdateCount() select rows whose static_info_country starts
     * with a digit 1-9. In the fixture that is uid1 "54", uid2 "220", uid3 "9" (3 rows); uid4 "DE"
     * (already an ISO name) and uid5 "" (empty) are non-migratable. 30e771a (sibling branch) will
     * change the final `return $count;` to `return is_numeric($count) ? (int)$count : 0;` - dead
     * type-narrowing, since a COUNT(uid) query always yields a numeric scalar. The returned count
     * is identical pre/post, so this is a plain green characterization test.
     */
    #[Test]
    public function getRecordsToUpdateCountReturnsNumberOfMigratableRows(): void
    {
        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getRecordsToUpdateCount');

        self::assertSame(3, $method->invoke($subject));
    }

    /**
     * updateNecessary() is a thin wrapper over getRecordsToUpdateCount() > 0. With migratable rows
     * present it must report the wizard as necessary. Unaffected by 30e771a.
     */
    #[Test]
    public function updateNecessaryReturnsTrueWhenMigratableRowsExist(): void
    {
        $subject = $this->getSubject();

        self::assertTrue($subject->updateNecessary());
    }

    /**
     * Marker-of-done / idempotency at the count level, provable WITHOUT running the (pre-fix broken)
     * executeUpdate(): once every static_info_country holds a non-numeric ISO name, no row matches
     * the LIKE 'N%' WHERE, so the count is 0 and updateNecessary() is false. We rewrite the column
     * directly to simulate the post-migration state. Unaffected by 30e771a.
     */
    #[Test]
    public function getRecordsToUpdateCountReturnsZeroWhenAllRowsAlreadyMigrated(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        // Put every row into the "already migrated" (non-numeric) state.
        $connection->update('fe_users', ['static_info_country' => 'DE'], ['uid' => 1]);
        $connection->update('fe_users', ['static_info_country' => 'US'], ['uid' => 2]);
        $connection->update('fe_users', ['static_info_country' => 'AO'], ['uid' => 3]);

        $subject = $this->getSubject();
        $method = $this->getPrivateMethod($subject, 'getRecordsToUpdateCount');

        self::assertSame(0, $method->invoke($subject));
        self::assertFalse($subject->updateNecessary());
    }

    // -- executeUpdate ----------------------------------------------------------------------------

    /**
     * SOLL: executeUpdate() rewrites each migratable row's static_info_country from the numeric
     * static_info_tables uid to the matching Country enum case NAME (uid1 54 -> "DE", uid2 220 ->
     * "US", uid3 9 -> "AO"), leaves the non-migratable rows (uid4 "DE", uid5 "") untouched, and is
     * idempotent (a second run migrates nothing; the count is 0 afterwards).
     *
     * Pre-fix bug in df53334: executeUpdate uses fetchAssociative() (single row -> iterates
     * columns) and Country::tryFrom()->name null-derefs; migration is broken. Behoben in 30e771a
     * (Classes/Updates/UserCountryMigration::executeUpdate). Reaktivieren in Roadmap-Schritt 2.
     */
    /**
     * Characterizes df53334 behaviour: executeUpdate() does `foreach ($records->fetchAssociative()
     * as $record)`, but fetchAssociative() returns a single associative row, so the foreach iterates
     * that row's scalar column values. `$record['uid']` / `$record['static_info_country']` then index
     * into scalars (PHP warnings), `(int)null` is 0, `Country::tryFrom(0)` is null and `->name`
     * null-derefs -> uncaught \Error (catch (Exception) does not catch it). The migration is broken.
     * 30e771a rewrites executeUpdate() (fetchAllAssociative + null-safe mapping) so it migrates
     * correctly = behaviour change, so this test goes RED once 30e771a is cherry-picked -> revert that
     * part in 30e771a; the real fix belongs in a later step.
     *
     * #[WithoutErrorHandler] disables PHPUnit's error handler for this test so the PHP warnings that
     * precede the \Error do not fail the test via failOnWarning before the characterized \Error is
     * reached.
     */
    #[Test]
    #[WithoutErrorHandler]
    public function executeUpdateThrowsErrorBecauseMigrationIsBroken(): void
    {
        $subject = $this->getSubject();

        $this->expectException(\Error::class);

        $subject->executeUpdate();
    }
}
