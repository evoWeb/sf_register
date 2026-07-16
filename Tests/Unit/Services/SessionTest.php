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

namespace Evoweb\SfRegister\Tests\Unit\Services;

use Evoweb\SfRegister\Services\Session;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Session\Backend\SessionBackendInterface;
use TYPO3\CMS\Core\Session\SessionManager;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\CMS\Core\Session\UserSessionManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class SessionTest extends UnitTestCase
{
    /**
     * UserSessionManager::create() registers a Logger via GeneralUtility::makeInstance()
     * which is tracked as a singleton, so it needs to be reset in tearDown().
     */
    protected bool $resetSingletonInstances = true;

    protected mixed $originalTypo3ConfVars = null;

    protected mixed $originalRequest = null;

    protected mixed $originalExecTime = null;

    public function setUp(): void
    {
        parent::setUp();

        /** @var array<string, mixed> $typo3ConfVars */
        $typo3ConfVars = $GLOBALS['TYPO3_CONF_VARS'] ?? [];
        $this->originalTypo3ConfVars = $typo3ConfVars;
        $this->originalRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;
        $this->originalExecTime = $GLOBALS['EXEC_TIME'] ?? null;

        // Values used by UserSessionManager::create('FE') resp. UserSession::createNonFixated()
        $feConfig = is_array($typo3ConfVars['FE'] ?? null) ? $typo3ConfVars['FE'] : [];
        $typo3ConfVars['FE'] = array_replace($feConfig, [
            'lockIP' => 0,
            'lockIPv6' => 0,
            'lifetime' => 0,
            'sessionTimeout' => 0,
            'sessionDataLifetime' => 0,
        ]);
        $GLOBALS['TYPO3_CONF_VARS'] = $typo3ConfVars;
        $GLOBALS['EXEC_TIME'] = $this->originalExecTime ?? time();
    }

    public function tearDown(): void
    {
        if ($this->originalTypo3ConfVars !== null) {
            $GLOBALS['TYPO3_CONF_VARS'] = $this->originalTypo3ConfVars;
        } else {
            unset($GLOBALS['TYPO3_CONF_VARS']);
        }
        if ($this->originalRequest !== null) {
            $GLOBALS['TYPO3_REQUEST'] = $this->originalRequest;
        } else {
            unset($GLOBALS['TYPO3_REQUEST']);
        }
        if ($this->originalExecTime !== null) {
            $GLOBALS['EXEC_TIME'] = $this->originalExecTime;
        } else {
            unset($GLOBALS['EXEC_TIME']);
        }

        parent::tearDown();
    }

    /**
     * Builds a minimal, real PSR-7 request without any session cookie so
     * UserSessionManager::createFromRequestOrAnonymous() creates a fresh
     * anonymous session instead of trying to load one from the backend.
     */
    protected function buildRequest(): ServerRequestInterface
    {
        return new ServerRequest(
            'http://localhost/',
            'GET',
            'php://input',
            [],
            ['HTTP_HOST' => 'localhost', 'SCRIPT_NAME' => '/index.php']
        );
    }

    /**
     * Creates a Session instance the same way `__construct()` does, except
     * the session backend is a mock. This avoids requiring a database backed
     * session backend (`Classes/Services/Session.php` uses
     * `UserSessionManager::create('FE')` internally, which by default needs a
     * configured, database backed session backend) while still exercising
     * the real constructor code.
     */
    protected function createConstructedSubject(): Session
    {
        $sessionBackend = $this->createMock(SessionBackendInterface::class);

        $sessionManager = $this->createMock(SessionManager::class);
        $sessionManager->method('getSessionBackend')->with('FE')->willReturn($sessionBackend);
        GeneralUtility::setSingletonInstance(SessionManager::class, $sessionManager);

        $GLOBALS['TYPO3_REQUEST'] = $this->buildRequest();

        return new Session();
    }

    /**
     * Creates a Session instance bypassing the real constructor (which needs
     * a database backed TYPO3 session backend, see createConstructedSubject())
     * and injects the given collaborators directly via reflection instead.
     *
     * @param array<string, mixed>|null $values
     */
    protected function createSubject(
        ?UserSession $session = null,
        ?UserSessionManager $userSessionManager = null,
        ?array $values = null,
    ): Session {
        $reflectionClass = new \ReflectionClass(Session::class);
        $subject = $reflectionClass->newInstanceWithoutConstructor();

        if ($session !== null) {
            $property = $reflectionClass->getProperty('session');
            $property->setValue($subject, $session);
        }

        if ($userSessionManager !== null) {
            $property = $reflectionClass->getProperty('userSessionManager');
            $property->setValue($subject, $userSessionManager);
        }

        if ($values !== null) {
            $property = $reflectionClass->getProperty('values');
            $property->setValue($subject, $values);
        }

        return $subject;
    }

    /**
     * @return UserSession&MockObject
     */
    protected function createSessionMock(): UserSession&MockObject
    {
        return $this->createMock(UserSession::class);
    }

    // -- __construct ----------------------------------------------------------------------------

    #[Test]
    public function constructInitializesEmptyValuesWhenSessionHasNoStoredData(): void
    {
        $subject = $this->createConstructedSubject();

        self::assertFalse($subject->has('foo'));
        self::assertNull($subject->get('foo'));
    }

    // -- has --------------------------------------------------------------------------------------

    #[Test]
    public function hasReturnsTrueForExistingKey(): void
    {
        $subject = $this->createSubject(values: ['foo' => 'bar']);

        self::assertTrue($subject->has('foo'));
    }

    #[Test]
    public function hasReturnsFalseForMissingKey(): void
    {
        $subject = $this->createSubject(values: ['foo' => 'bar']);

        self::assertFalse($subject->has('baz'));
    }

    #[Test]
    public function hasReturnsTrueForKeyWithNullValue(): void
    {
        $subject = $this->createSubject(values: ['foo' => null]);

        self::assertTrue($subject->has('foo'));
    }

    // -- get --------------------------------------------------------------------------------------

    #[Test]
    public function getReturnsValueStoredForExistingKey(): void
    {
        $subject = $this->createSubject(values: ['foo' => 'bar']);

        self::assertSame('bar', $subject->get('foo'));
    }

    #[Test]
    public function getReturnsNullForMissingKey(): void
    {
        $subject = $this->createSubject(values: ['foo' => 'bar']);

        self::assertNull($subject->get('baz'));
    }

    #[Test]
    public function getReturnsNullForExistingKeyWithNullValue(): void
    {
        $subject = $this->createSubject(values: ['foo' => null]);

        self::assertNull($subject->get('foo'));
    }

    // -- remove -----------------------------------------------------------------------------------

    #[Test]
    public function removeDeletesKeySoHasReturnsFalseAfterwards(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->buildRequest();

        $session = $this->createSessionMock();
        $session->expects($this->once())->method('set')->with(
            'sf_register',
            serialize(['other' => 'value'])
        );

        $userSessionManager = $this->createMock(UserSessionManager::class);
        $userSessionManager->expects($this->once())->method('updateSession')->with($session)->willReturn($session);

        $subject = $this->createSubject($session, $userSessionManager, ['foo' => 'bar', 'other' => 'value']);

        $result = $subject->remove('foo');

        self::assertFalse($subject->has('foo'));
        self::assertTrue($subject->has('other'));
        self::assertSame($subject, $result);
    }

    #[Test]
    public function removeOnMissingKeyLeavesValuesUnchangedButStillPersistsSession(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = $this->buildRequest();

        $session = $this->createSessionMock();
        $session->expects($this->once())->method('set')->with(
            'sf_register',
            serialize(['foo' => 'bar'])
        );

        $userSessionManager = $this->createMock(UserSessionManager::class);
        $userSessionManager->expects($this->once())->method('updateSession')->with($session)->willReturn($session);

        $subject = $this->createSubject($session, $userSessionManager, ['foo' => 'bar']);

        $subject->remove('missing');

        self::assertTrue($subject->has('foo'));
        self::assertSame('bar', $subject->get('foo'));
    }
}
