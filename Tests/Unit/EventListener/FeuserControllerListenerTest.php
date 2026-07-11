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

namespace Evoweb\SfRegister\Tests\Unit\EventListener;

use Evoweb\SfRegister\Controller\Event\InitializeActionEvent;
use Evoweb\SfRegister\Controller\FeuserController;
use Evoweb\SfRegister\EventListener\FeuserControllerListener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class FeuserControllerListenerTest extends UnitTestCase
{
    protected Context&MockObject $context;

    protected UriBuilder&MockObject $uriBuilder;

    protected FeuserControllerListener $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->context = $this->createMock(Context::class);
        $this->uriBuilder = $this->createMock(UriBuilder::class);

        $this->subject = new FeuserControllerListener($this->context, $this->uriBuilder);
    }

    /**
     * UserAspect is `final readonly`, so it can't be mocked. Building a real
     * instance and, for the logged in case, a mocked AbstractUserAuthentication
     * carrying the minimal data isLoggedIn() reads, is used instead.
     */
    protected function configureLoginState(bool $loggedIn): void
    {
        if ($loggedIn) {
            $user = $this->createMock(AbstractUserAuthentication::class);
            $user->userid_column = 'uid';
            $user->user = ['uid' => 5];
            $userAspect = new UserAspect($user);
        } else {
            $userAspect = new UserAspect(null);
        }

        $this->context->method('getAspect')->with('frontend.user')->willReturn($userAspect);
    }

    /**
     * @param array<string, mixed> $settings
     */
    protected function createEvent(array $settings): InitializeActionEvent
    {
        $controller = $this->createMock(FeuserController::class);

        return new InitializeActionEvent($controller, $settings, null);
    }

    // -- __invoke, user logged in ------------------------------------------------------------------

    #[Test]
    public function invokeDoesNotTouchResponseWhenUserIsLoggedIn(): void
    {
        $this->configureLoginState(true);
        $this->uriBuilder->expects($this->never())->method('setTargetPageUid');

        $event = $this->createEvent([
            'redirectEvent' => ['page' => 5, 'action' => 'list', 'controller' => 'Feuser'],
        ]);

        ($this->subject)($event);

        self::assertNull($event->getResponse());
    }

    // -- __invoke, user not logged in, page redirect -----------------------------------------------

    #[Test]
    public function invokeSetsRedirectResponseWhenPageIsConfiguredAndUriBuilderReturnsUrl(): void
    {
        $this->configureLoginState(false);
        $this->uriBuilder->expects($this->once())->method('setTargetPageUid')->with(5)->willReturnSelf();
        $this->uriBuilder->expects($this->once())->method('build')->willReturn('https://example.org/redirect');

        $event = $this->createEvent([
            'redirectEvent' => ['page' => 5, 'action' => 'list', 'controller' => 'Feuser'],
        ]);

        ($this->subject)($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('https://example.org/redirect', $response->getHeaderLine('location'));
    }

    #[Test]
    public function invokeDoesNotSetResponseWhenPageIsConfiguredButUriBuilderReturnsEmptyUrl(): void
    {
        $this->configureLoginState(false);
        $this->uriBuilder->expects($this->once())->method('setTargetPageUid')->with(5)->willReturnSelf();
        $this->uriBuilder->expects($this->once())->method('build')->willReturn('');

        $event = $this->createEvent([
            'redirectEvent' => ['page' => 5, 'action' => 'list', 'controller' => 'Feuser'],
        ]);

        ($this->subject)($event);

        self::assertNull($event->getResponse());
    }

    // -- __invoke, user not logged in, forward redirect --------------------------------------------

    #[Test]
    public function invokeSetsForwardResponseWithControllerNameWhenPageIsNotConfiguredAndControllerIsGiven(): void
    {
        $this->configureLoginState(false);
        $this->uriBuilder->expects($this->never())->method('setTargetPageUid');

        $event = $this->createEvent([
            'redirectEvent' => ['page' => 0, 'action' => 'list', 'controller' => 'Feuser'],
        ]);

        ($this->subject)($event);

        $response = $event->getResponse();
        self::assertInstanceOf(ForwardResponse::class, $response);
        self::assertSame('list', $response->getActionName());
        self::assertSame('Feuser', $response->getControllerName());
    }

    #[Test]
    public function invokeSetsForwardResponseWithoutControllerNameWhenControllerIsNotGiven(): void
    {
        $this->configureLoginState(false);

        $event = $this->createEvent([
            'redirectEvent' => ['page' => 0, 'action' => 'list'],
        ]);

        ($this->subject)($event);

        $response = $event->getResponse();
        self::assertInstanceOf(ForwardResponse::class, $response);
        self::assertSame('list', $response->getActionName());
        self::assertNull($response->getControllerName());
    }
}
