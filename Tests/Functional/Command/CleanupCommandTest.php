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

namespace Evoweb\SfRegister\Tests\Functional\Command;

use Evoweb\SfRegister\Command\CleanupCommand;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Characterization tests for the CleanupCommand console command
 * (`sfregister:cleanup <inactiveGroups> [days=14]`).
 *
 * What it does: deletes fe_users rows that are still assigned to one of the given "inactive"
 * (pre-confirmation) usergroups AND were created more than <days> days ago - i.e. accounts that
 * never finished the double opt-in (findInOutdatedTemporaryUsers() selects them with all query
 * restrictions reset, so hidden/deleted rows are candidates too). For every such orphaned user it
 * additionally removes any sys_file_reference row pointing at it (tablenames=fe_users,
 * fieldname=image) and deletes the referenced FAL file, so an fe_users avatar upload does not leak
 * in storage once its owning temp account is purged.
 *
 * FINDING - open bug: findInOutdatedTemporaryUsers() builds
 * `$queryBuilder->expr()->inSet('usergroup', $queryBuilder->createNamedParameter($inactiveUserGroup, ...))`.
 * TYPO3 core's ExpressionBuilder::inSet() explicitly forbids SQLite from being given a bound/named
 * query parameter as the `$value` argument (it throws InvalidArgumentException
 * "ExpressionBuilder::inSet() for SQLite can not be used with placeholder arguments.", see
 * vendor/typo3/cms-core/Classes/Database/Query/Expression/ExpressionBuilder.php:307-313). That
 * InvalidArgumentException is caught by execute()'s `catch (Exception | DbalException)`, so under
 * SQLite, execute() with any non-empty inactiveGroups argument ALWAYS returns Command::FAILURE and
 * removes nothing - it never even reaches removeUser()/fetchReference()/removeReference()/
 * removeImage(). The desired behaviour ("execute() removes the orphaned records and returns exit
 * code 0") is documented below as a skipped, RED-verified test; the actual (green) behavior under
 * SQLite is characterized directly next to it.
 */
class CleanupCommandTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/fe_users_cleanup.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/sys_file_storage.csv');
    }

    /**
     * @return array<int, int>
     */
    protected function fetchFeUserUids(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('fe_users');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid')
            ->from('fe_users')
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        return array_map(
            static fn(array $row): int => is_scalar($row['uid']) ? (int)$row['uid'] : 0,
            $rows
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchFileReferences(): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->select('uid', 'uid_foreign', 'uid_local')
            ->from('sys_file_reference')
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function createFileInStorage(string $filename): File
    {
        /** @var StorageRepository $storageRepository */
        $storageRepository = $this->get(StorageRepository::class);
        $storage = $storageRepository->getStorageObject(1);
        $rootFolder = $storage->getRootLevelFolder();

        $localFile = $this->createJpegFile($filename);
        return $storage->addFile($localFile, $rootFolder, $filename);
    }

    protected function addFileForUser(int $uidForeign): void
    {
        $file = $this->createFileInStorage('avatar-' . $uidForeign . '.jpg');

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file_reference');
        $connection->insert('sys_file_reference', [
            'pid' => 0,
            'uid_local' => $file->getUid(),
            'uid_foreign' => $uidForeign,
            'tablenames' => 'fe_users',
            'fieldname' => 'image',
        ]);
    }

    protected function createJpegFile(string $filename): string
    {
        // Minimal valid 1x1 JPEG so the storage mime-type consistency check passes.
        $bytes = base64_decode(
            '/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof'
            . 'Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/wAALCAABAAEBAREA/8QA'
            . 'FAABAAAAAAAAAAAAAAAAAAAAAv/EABQQAQAAAAAAAAAAAAAAAAAAAAD/2gAIAQEAAD8A'
            . 'fwD/2Q=='
        );
        $path = $this->instancePath . '/typo3temp/var/transient/';
        GeneralUtility::mkdir_deep($path);
        $testFilename = $path . $filename;
        file_put_contents($testFilename, (string)$bytes);
        return $testFilename;
    }

    // -- execute --------------------------------------------------------------------------------

    /**
     * SOLL (per task brief): execute() removes fe_users uid 1 (usergroup 2, matches the given
     * inactiveGroups argument, crdate far in the past -> outdated) together with its orphaned
     * image reference and the underlying FAL file, while leaving uid 2 (same group but a crdate
     * far in the future -> not outdated) and uid 3 (outdated but a different usergroup -> not
     * targeted) and uid 2's unrelated image reference untouched, and returns Command::SUCCESS.
     *
     * Blocked by the pre-existing SQLite/inSet() bug documented in the class docblock. RED-verified:
     * un-skipping this test and running it reproduces
     * "Failed asserting that 1 is identical to 0." with
     * "ExpressionBuilder::inSet() for SQLite can not be used with placeholder arguments." in the
     * command output ($commandTester->getDisplay()).
     */
    #[Test]
    public function executeRemovesOutdatedUserAndOrphanedFileReferenceAndReturnsSuccess(): void
    {
        self::markTestSkipped(
            'Pre-existing bug: findInOutdatedTemporaryUsers() passes a'
            . ' bound QueryBuilder parameter into ExpressionBuilder::inSet(), which TYPO3 core'
            . ' forbids for SQLite ("ExpressionBuilder::inSet() for SQLite can not be used with'
            . ' placeholder arguments."). execute() therefore always returns Command::FAILURE'
            . ' under SQLite for any non-empty inactiveGroups argument and removes nothing.'
            . ' RED-verified.'
        );

        // Assertions below are commented out while the test is skipped (blocked by the
        // SQLite incompatibility documented above).
        //
        // $this->addFileForUser(1);
        // $this->addFileForUser(2);
        //
        // /** @var CleanupCommand $command */
        // $command = $this->get(CleanupCommand::class);
        // $commandTester = new CommandTester($command);
        // $exitCode = $commandTester->execute(['inactiveGroups' => '2']);
        //
        // self::assertSame(Command::SUCCESS, $exitCode, $commandTester->getDisplay());
        //
        // // Orphaned user removed, non-orphaned users (wrong group / not outdated) kept.
        // self::assertSame([2, 3], $this->fetchFeUserUids());
        //
        // // Only the removed user's image reference is gone; the other user's reference remains.
        // $remainingReferences = $this->fetchFileReferences();
        // self::assertCount(1, $remainingReferences);
        // self::assertSame(2, (int)$remainingReferences[0]['uid_foreign']);
    }

    /**
     * ACTUAL (green) characterization: because of the SQLite/inSet() incompatibility documented in
     * the class docblock, execute() never reaches removeUser()/fetchReference()/removeReference()/
     * removeImage() - it throws inside findInOutdatedTemporaryUsers() on the very first
     * inactiveGroup iteration, is caught by execute()'s try/catch, and returns Command::FAILURE
     * while leaving every fe_users row and sys_file_reference row untouched.
     */
    #[Test]
    public function executeReturnsFailureAndLeavesFixtureUntouchedUnderSqlite(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file_reference');
        $connection->insert('sys_file_reference', [
            'pid' => 0,
            'uid_local' => 10,
            'uid_foreign' => 1,
            'tablenames' => 'fe_users',
            'fieldname' => 'image',
        ]);

        /** @var CleanupCommand $command */
        $command = $this->get(CleanupCommand::class);
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute(['inactiveGroups' => '2']);

        self::assertSame(Command::FAILURE, $exitCode);
        // SymfonyStyle word-wraps the comment, so match a fragment that survives wrapping.
        self::assertStringContainsString(
            'ExpressionBuilder::inSet() for SQLite can not be used with placeholder',
            $commandTester->getDisplay()
        );

        // Nothing was removed: the exception fires before any user is processed.
        self::assertSame([1, 2, 3], $this->fetchFeUserUids());
        self::assertCount(1, $this->fetchFileReferences());
    }

    // -- removeReference --------------------------------------------------------------------------

    /**
     * removeReference() deletes exactly the sys_file_reference row(s) matching
     * uid_foreign/tablenames=fe_users/fieldname=image for the given user, leaving any other
     * user's reference row untouched.
     */
    #[Test]
    public function removeReferenceDeletesOnlyTheTargetedUsersImageReference(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file_reference');
        $connection->insert('sys_file_reference', [
            'pid' => 0,
            'uid_local' => 10,
            'uid_foreign' => 1,
            'tablenames' => 'fe_users',
            'fieldname' => 'image',
        ]);
        $connection->insert('sys_file_reference', [
            'pid' => 0,
            'uid_local' => 11,
            'uid_foreign' => 2,
            'tablenames' => 'fe_users',
            'fieldname' => 'image',
        ]);

        /** @var CleanupCommand $command */
        $command = $this->get(CleanupCommand::class);
        $method = $this->getPrivateMethod($command, 'removeReference');
        $method->invoke($command, ['uid' => 1]);

        $remainingReferences = $this->fetchFileReferences();
        self::assertCount(1, $remainingReferences);
        self::assertSame(2, is_scalar($remainingReferences[0]['uid_foreign']) ? (int)$remainingReferences[0]['uid_foreign'] : 0);
        self::assertSame(11, is_scalar($remainingReferences[0]['uid_local']) ? (int)$remainingReferences[0]['uid_local'] : 0);
    }

    // -- removeImage ------------------------------------------------------------------------------

    /**
     * removeImage() iterates the given reference rows and, for each, resolves the FAL file by
     * its `uid_local` and deletes it from storage
     * (`resourceFactory->getFileObject($reference['uid_local'])->getStorage()->deleteFile($file)`).
     * `$reference['uid_local']` is narrowed via
     * `$uidLocal = is_scalar($reference['uid_local']) ? (int)$reference['uid_local'] : 0;` before it
     * is handed to getFileObject(); `uid_local` is a NOT NULL int(11) column on sys_file_reference,
     * so a real reference row always yields an int and the else-branch is dead code.
     */
    #[Test]
    public function removeImageDeletesReferencedFileFromStorage(): void
    {
        $file = $this->createFileInStorage('avatar-removeimage.jpg');
        $fileUid = $file->getUid();
        $storage = $file->getStorage();
        $identifier = $file->getIdentifier();

        // Guard: the file really exists in storage before removeImage runs.
        self::assertTrue($storage->hasFile($identifier));

        /** @var CleanupCommand $command */
        $command = $this->get(CleanupCommand::class);
        $method = $this->getPrivateMethod($command, 'removeImage');
        // Mirror the array shape fetchReference() passes: a list of rows each carrying uid_local.
        $method->invoke($command, [['uid_local' => $fileUid]]);

        // deleteFile() ran: the file is gone from storage and from the FAL index.
        self::assertFalse($storage->hasFile($identifier));

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('sys_file');
        $queryBuilder->getRestrictions()->removeAll();
        $remainingFiles = $queryBuilder
            ->select('uid')
            ->from('sys_file')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileUid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAllAssociative();
        self::assertCount(0, $remainingFiles);
    }
}
