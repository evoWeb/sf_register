<?php

declare(strict_types=1);

/*
 * This file is developed by evoWeb.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Evoweb\SfRegister\Tests\Functional\Services;

use Evoweb\SfRegister\Services\Session;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Frontend\Middleware\FrontendUserAuthenticator;

class SessionTest extends AbstractTestBase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->createServerRequest();
    }

    #[Test]
    public function valueSetInOneRequestIsAvailableInFollowingRequest(): void
    {
        $firstResponse = $this->processFrontendRequest(
            static fn() => (new Session())->set('captchaWasValid', true)
        );

        $value = null;
        $this->processFrontendRequest(
            static function () use (&$value): void {
                $value = (new Session())->get('captchaWasValid');
            },
            $this->getCookiesFromResponse($firstResponse)
        );

        self::assertTrue($value);
    }

    #[Test]
    public function readingValuesDoesNotStartASession(): void
    {
        $response = $this->processFrontendRequest(
            static function (): void {
                $session = new Session();
                $session->has('captchaWasValid');
                $session->get('captchaWasValid');
            }
        );

        self::assertSame([], $this->getCookiesFromResponse($response));
    }

    #[Test]
    public function removedValueIsNotAvailableInFollowingRequest(): void
    {
        $firstResponse = $this->processFrontendRequest(
            static function (): void {
                $session = new Session();
                $session->set('captchaWasValid', true);
                $session->set('other', 'value');
                $session->remove('captchaWasValid');
            }
        );

        $has = null;
        $other = null;
        $this->processFrontendRequest(
            static function () use (&$has, &$other): void {
                $session = new Session();
                $has = $session->has('captchaWasValid');
                $other = $session->get('other');
            },
            $this->getCookiesFromResponse($firstResponse)
        );

        self::assertFalse($has);
        self::assertSame('value', $other);
    }

    /**
     * Runs the callback inside the frontend user middleware, like a plugin would be rendered
     *
     * @param array<string, string> $cookies
     */
    protected function processFrontendRequest(callable $callback, array $cookies = []): ResponseInterface
    {
        $request = $this->request->withCookieParams($cookies);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $handler = new class ($callback) implements RequestHandlerInterface {
            /** @var callable */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $GLOBALS['TYPO3_REQUEST'] = $request;
                ($this->callback)();
                return new Response();
            }
        };

        /** @var FrontendUserAuthenticator $frontendUserAuthenticator */
        $frontendUserAuthenticator = $this->get(FrontendUserAuthenticator::class);
        return $frontendUserAuthenticator->process($request, $handler);
    }

    /**
     * @return array<string, string>
     */
    protected function getCookiesFromResponse(ResponseInterface $response): array
    {
        $cookies = [];
        foreach ($response->getHeader('Set-Cookie') as $header) {
            [$cookie] = explode(';', $header, 2);
            [$name, $value] = explode('=', $cookie, 2);
            $cookies[urldecode($name)] = urldecode($value);
        }
        return $cookies;
    }
}
