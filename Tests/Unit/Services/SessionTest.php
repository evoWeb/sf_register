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
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class SessionTest extends UnitTestCase
{
    protected mixed $originalRequest = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->originalRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

    public function tearDown(): void
    {
        if ($this->originalRequest !== null) {
            $GLOBALS['TYPO3_REQUEST'] = $this->originalRequest;
        } else {
            unset($GLOBALS['TYPO3_REQUEST']);
        }

        parent::tearDown();
    }

    /**
     * Registers a frontend user, which holds the given values as session data of sf_register
     *
     * @param array<string, mixed>|null $values
     */
    protected function createFrontendUser(?array $values = null): FrontendUserAuthentication&MockObject
    {
        $frontendUser = $this->createMock(FrontendUserAuthentication::class);
        $frontendUser->method('getSessionData')->with('sf_register')->willReturn($values);

        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('http://localhost/'))
            ->withAttribute('frontend.user', $frontendUser);

        return $frontendUser;
    }

    // -- fetch ----------------------------------------------------------------------------------

    #[Test]
    public function hasNoValuesWhenSessionHasNoStoredData(): void
    {
        $this->createFrontendUser();
        $subject = new Session();

        self::assertFalse($subject->has('foo'));
        self::assertNull($subject->get('foo'));
    }

    #[Test]
    public function hasNoValuesWithoutFrontendUser(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest('http://localhost/');
        $subject = new Session();

        self::assertFalse($subject->has('foo'));
        self::assertNull($subject->get('foo'));
    }

    // -- has ------------------------------------------------------------------------------------

    #[Test]
    public function hasReturnsTrueForExistingKey(): void
    {
        $this->createFrontendUser(['foo' => 'bar']);
        $subject = new Session();

        self::assertTrue($subject->has('foo'));
    }

    #[Test]
    public function hasReturnsFalseForMissingKey(): void
    {
        $this->createFrontendUser(['foo' => 'bar']);
        $subject = new Session();

        self::assertFalse($subject->has('baz'));
    }

    #[Test]
    public function hasReturnsTrueForKeyWithNullValue(): void
    {
        $this->createFrontendUser(['foo' => null]);
        $subject = new Session();

        self::assertTrue($subject->has('foo'));
    }

    // -- get ------------------------------------------------------------------------------------

    #[Test]
    public function getReturnsValueStoredForExistingKey(): void
    {
        $this->createFrontendUser(['foo' => 'bar']);
        $subject = new Session();

        self::assertSame('bar', $subject->get('foo'));
    }

    #[Test]
    public function getReturnsNullForMissingKey(): void
    {
        $this->createFrontendUser(['foo' => 'bar']);
        $subject = new Session();

        self::assertNull($subject->get('baz'));
    }

    // -- set ------------------------------------------------------------------------------------

    #[Test]
    public function setStoresAllValuesInSessionOfFrontendUser(): void
    {
        $frontendUser = $this->createFrontendUser(['foo' => 'bar']);
        $frontendUser->expects($this->once())
            ->method('setAndSaveSessionData')
            ->with('sf_register', ['foo' => 'bar', 'other' => 'value']);
        $subject = new Session();

        $result = $subject->set('other', 'value');

        self::assertSame('value', $subject->get('other'));
        self::assertSame($subject, $result);
    }

    // -- remove ---------------------------------------------------------------------------------

    #[Test]
    public function removeDeletesKeySoHasReturnsFalseAfterwards(): void
    {
        $frontendUser = $this->createFrontendUser(['foo' => 'bar', 'other' => 'value']);
        $frontendUser->expects($this->once())
            ->method('setAndSaveSessionData')
            ->with('sf_register', ['other' => 'value']);
        $subject = new Session();

        $result = $subject->remove('foo');

        self::assertFalse($subject->has('foo'));
        self::assertTrue($subject->has('other'));
        self::assertSame($subject, $result);
    }

    #[Test]
    public function removeOfLastKeyRemovesSessionDataOfFrontendUser(): void
    {
        $frontendUser = $this->createFrontendUser(['foo' => 'bar']);
        $frontendUser->expects($this->once())
            ->method('setAndSaveSessionData')
            ->with('sf_register', null);
        $subject = new Session();

        $subject->remove('foo');

        self::assertFalse($subject->has('foo'));
    }

    #[Test]
    public function removeOnMissingKeyLeavesValuesAndSessionUnchanged(): void
    {
        $frontendUser = $this->createFrontendUser(['foo' => 'bar']);
        $frontendUser->expects($this->never())->method('setAndSaveSessionData');
        $subject = new Session();

        $subject->remove('missing');

        self::assertSame('bar', $subject->get('foo'));
    }
}
