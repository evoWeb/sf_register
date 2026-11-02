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

namespace Evoweb\SfRegister\Tests\Functional\Services;

use Evoweb\SfRegister\Services\Session;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\CMS\Core\Session\UserSessionManager;

class SessionTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createServerRequest();
    }

    protected function getSubject(): Session
    {
        /** @var Session $subject */
        $subject = $this->get(Session::class);
        return $subject;
    }

    protected function getUnderlyingSession(Session $subject): UserSession
    {
        /** @var UserSession $session */
        $session = $this->getPrivateProperty($subject, 'session')->getValue($subject);
        return $session;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAllPersistedSessionRecords(): array
    {
        return $this->getConnectionPool()
            ->getQueryBuilderForTable('fe_sessions')
            ->select('*')
            ->from('fe_sessions')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Reloads the session directly from the TYPO3 core session backend, bypassing
     * the subject entirely, to prove the data was really persisted and not just
     * held in the in-memory session object.
     *
     * @return array<string, mixed>
     */
    protected function reloadStoredValues(Session $subject): array
    {
        $identifier = $this->getUnderlyingSession($subject)->getIdentifier();
        $reloadedSession = UserSessionManager::create('FE')->createSessionFromStorage($identifier);

        $sessionValue = $reloadedSession->get('sf_register');
        if (empty($sessionValue)) {
            return [];
        }

        $values = unserialize((string)$sessionValue);
        return is_array($values) ? $values : [];
    }

    // -- __construct --------------------------------------------------------------------------

    #[Test]
    public function constructInitializesEmptyValuesWhenSessionHasNoStoredData(): void
    {
        $subject = $this->getSubject();

        self::assertFalse($subject->has('foo'));
        self::assertNull($subject->get('foo'));
    }

    #[Test]
    public function constructDoesNotYetPersistAFreshAnonymousSession(): void
    {
        $this->getSubject();

        self::assertCount(0, $this->fetchAllPersistedSessionRecords());
    }

    // -- set ------------------------------------------------------------------------------------

    #[Test]
    public function setPersistsThePreviouslyUnfixatedAnonymousSessionToTheBackend(): void
    {
        $subject = $this->getSubject();

        $subject->set('foo', 'bar');

        $records = $this->fetchAllPersistedSessionRecords();
        self::assertCount(1, $records);
        self::assertEquals(0, $records[0]['ses_userid']);
    }

    #[Test]
    public function setStoresValueRetrievableAfterReloadingTheSessionFromTheBackend(): void
    {
        $subject = $this->getSubject();

        $subject->set('foo', 'bar');

        self::assertSame(['foo' => 'bar'], $this->reloadStoredValues($subject));
    }

    #[Test]
    public function getReturnsValueStoredForExistingKey(): void
    {
        $subject = $this->getSubject();
        $subject->set('foo', 'bar');

        self::assertSame('bar', $subject->get('foo'));
    }

    #[Test]
    public function secondSetOnAnAlreadyFixatedSessionDoesNotFailAndKeepsBothValues(): void
    {
        $subject = $this->getSubject();

        $subject->set('foo', 'bar');
        $subject->set('other', 'value');

        self::assertCount(1, $this->fetchAllPersistedSessionRecords());
        self::assertSame(['foo' => 'bar', 'other' => 'value'], $this->reloadStoredValues($subject));
    }

    // -- remove ---------------------------------------------------------------------------------

    #[Test]
    public function removeDeletesKeyButKeepsSessionPersistedWithRemainingData(): void
    {
        $subject = $this->getSubject();
        $subject->set('foo', 'bar');
        $subject->set('other', 'value');

        $subject->remove('foo');

        self::assertFalse($subject->has('foo'));
        self::assertTrue($subject->has('other'));
        self::assertCount(1, $this->fetchAllPersistedSessionRecords());
        self::assertSame(['other' => 'value'], $this->reloadStoredValues($subject));
    }
}
