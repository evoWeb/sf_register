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

namespace Evoweb\SfRegister\Tests\Functional\Middleware;

use Doctrine\DBAL\Exception\InvalidColumnIndex;
use Doctrine\DBAL\Result;
use Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository;
use Evoweb\SfRegister\Middleware\AjaxMiddleware;
use Evoweb\SfRegister\Tests\Functional\AbstractTestBase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\Response;

/**
 * AjaxMiddleware::process() is a PSR-15 middleware:
 * - canHandleRequest() checks `$request->getQueryParams()['ajax'] === 'sf_register'`; when
 *   that does not match, process() delegates unchanged to the given $handler and returns
 *   whatever it returns.
 * - when it matches, process() reads the `tx_sfregister` request argument (parsed body,
 *   falling back to query params) and dispatches on `tx_sfregister[action]`:
 *     - 'zones' calls zonesAction($parent) with `tx_sfregister[parent]`
 *     - anything else calls errorAction()
 *   Both return [$status, $message, $result], which process() wraps into its OWN
 *   JsonResponse `{"status":..., "message":..., "data":...}` - $handler is never invoked on
 *   this path.
 *
 * zonesAction($parent) resolves country zones from the static_info_tables tables:
 * - a numeric $parent (MathUtility::canBeInterpretedAsInteger()) is treated as the uid of a
 *   static_countries row and resolved via
 *   StaticCountryZoneRepository::findAllByParentUid((int)$parent)
 * - a non-numeric $parent is treated as a country ISO-2 code, upper-cased and stripped of
 *   non-letters, and resolved via
 *   StaticCountryZoneRepository::findAllByIso2()
 * The resulting Doctrine\DBAL\Result is asked `rowCount() == 0` first: with no rows,
 * status='error'/message='no zones'/data=[]; otherwise each row of fetchAllAssociative()
 * becomes `['value' => uid, 'label' => zn_name_local]`. A Doctrine\DBAL\Exception thrown by
 * either call is caught and turns into
 * status='database caused an exception ' . message / message='no zones'.
 *
 * IMPORTANT - repository is test-doubled, not DB-backed: an earlier version of this test
 * loaded the static_info_tables stub extension (as SelectStaticCountryZonesViewHelperTest
 * does) and queried real fixture rows through StaticCountryZoneRepository. That revealed
 * Doctrine\DBAL\Driver\SQLite3\Result::rowCount() returns the *connection's last write
 * "changes" counter* (SQLite3::changes()), NOT the number of rows the SELECT actually
 * matched - a documented SQLite3-driver limitation, present identically before and after
 * 30e771a and entirely unrelated to it. Under `-d sqlite` this makes zonesAction()'s
 * `rowCount() == 0` branch decision depend on unrelated prior writes instead of the real
 * result set, so asserting a concrete status/data pair through the real DB+driver stack
 * would characterize that driver artifact, not AjaxMiddleware's own logic. To test the
 * middleware's actual logic (parent-type routing, row-count branch, row-to-option mapping,
 * exception handling) deterministically, StaticCountryZoneRepository and its
 * Doctrine\DBAL\Result return values are mocked here instead; AjaxMiddleware is
 * instantiated directly (constructor injection only - no container needed) with the mock.
 */
class AjaxMiddlewareTest extends AbstractTestBase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createServerRequest();
    }

    protected function getSubject(StaticCountryZoneRepository $repository): AjaxMiddleware
    {
        return new AjaxMiddleware($repository);
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    protected function requestWithQueryParams(array $queryParams): ServerRequestInterface
    {
        return $this->request->withQueryParams($queryParams);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeJsonBody(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded);
        return $decoded;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    protected function createResultMock(int $rowCount, array $rows): Result
    {
        $result = $this->createMock(Result::class);
        $result->method('rowCount')->willReturn($rowCount);
        $result->method('fetchAllAssociative')->willReturn($rows);
        return $result;
    }

    /**
     * canHandleRequest() clause: without an `ajax` query param the request does not match
     * `=== 'sf_register'`, so process() delegates unchanged to $handler and returns its
     * response as-is. handle() must be called exactly once with the very same request. The
     * repository is never touched on this path.
     */
    #[Test]
    public function delegatesToHandlerWhenAjaxQueryParamIsMissing(): void
    {
        $request = $this->requestWithQueryParams([]);
        $expectedResponse = new Response();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($expectedResponse);

        $repository = $this->createMock(StaticCountryZoneRepository::class);
        $repository->expects($this->never())->method('findAllByIso2');
        $repository->expects($this->never())->method('findAllByParentUid');

        $result = $this->getSubject($repository)->process($request, $handler);

        self::assertSame($expectedResponse, $result);
    }

    /**
     * Same canHandleRequest() clause, but proving the STRICT equality check: an `ajax`
     * query param with any other value than 'sf_register' still delegates to $handler.
     */
    #[Test]
    public function delegatesToHandlerWhenAjaxQueryParamDoesNotMatchSfRegister(): void
    {
        $request = $this->requestWithQueryParams(['ajax' => 'some_other_extension']);
        $expectedResponse = new Response();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($expectedResponse);

        $repository = $this->createMock(StaticCountryZoneRepository::class);

        $result = $this->getSubject($repository)->process($request, $handler);

        self::assertSame($expectedResponse, $result);
    }

    /**
     * With a matching `ajax` query param, process() builds its OWN JsonResponse and never
     * touches $handler at all - not even to inspect it.
     */
    #[Test]
    public function returnsOwnJsonResponseAndNeverCallsHandlerWhenActionIsUnknown(): void
    {
        $request = $this->requestWithQueryParams([
            'ajax' => 'sf_register',
            'tx_sfregister' => ['action' => 'unknown-action'],
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())->method('handle');

        $repository = $this->createMock(StaticCountryZoneRepository::class);
        $repository->expects($this->never())->method('findAllByIso2');
        $repository->expects($this->never())->method('findAllByParentUid');

        $result = $this->getSubject($repository)->process($request, $handler);

        self::assertInstanceOf(JsonResponse::class, $result);
        self::assertSame(200, $result->getStatusCode());
        self::assertSame('application/json; charset=utf-8', $result->getHeaderLine('Content-Type'));
        self::assertSame(
            ['status' => 'error', 'message' => 'unknown action', 'data' => []],
            $this->decodeJsonBody($result)
        );
    }

    /**
     * zonesAction() ISO-2 branch: a non-numeric parent ("us") is upper-cased and routed to
     * findAllByIso2('US') - never findAllByParentUid(). Rows found (rowCount 3) map to
     * ['value' => uid, 'label' => zn_name_local] in the order returned.
     */
    #[Test]
    public function returnsZonesMappedFromRepositoryWhenIsoParentHasMatches(): void
    {
        $request = $this->requestWithQueryParams([
            'ajax' => 'sf_register',
            'tx_sfregister' => ['action' => 'zones', 'parent' => 'us'],
        ]);

        $repository = $this->createMock(StaticCountryZoneRepository::class);
        $repository->expects($this->once())
            ->method('findAllByIso2')
            ->with('US')
            ->willReturn($this->createResultMock(3, [
                ['uid' => 1, 'zn_name_local' => 'California'],
                ['uid' => 2, 'zn_name_local' => 'New York'],
                ['uid' => 3, 'zn_name_local' => 'Texas'],
            ]));
        $repository->expects($this->never())->method('findAllByParentUid');

        $result = $this->getSubject($repository)
            ->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertSame(
            [
                'status' => 'success',
                'message' => '',
                'data' => [
                    ['value' => 1, 'label' => 'California'],
                    ['value' => 2, 'label' => 'New York'],
                    ['value' => 3, 'label' => 'Texas'],
                ],
            ],
            $this->decodeJsonBody($result)
        );
    }

    /**
     * zonesAction() no-rows branch: rowCount() == 0 yields status='error',
     * message='no zones', data=[] - distinct from errorAction()'s 'unknown action' message.
     */
    #[Test]
    public function returnsNoZonesErrorWhenIsoParentHasNoMatches(): void
    {
        $request = $this->requestWithQueryParams([
            'ajax' => 'sf_register',
            'tx_sfregister' => ['action' => 'zones', 'parent' => 'FR'],
        ]);

        $repository = $this->createMock(StaticCountryZoneRepository::class);
        $repository->expects($this->once())
            ->method('findAllByIso2')
            ->with('FR')
            ->willReturn($this->createResultMock(0, []));

        $result = $this->getSubject($repository)
            ->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertSame(
            ['status' => 'error', 'message' => 'no zones', 'data' => []],
            $this->decodeJsonBody($result)
        );
    }

    /**
     * zonesAction() parent-uid branch: MathUtility::canBeInterpretedAsInteger('1') is true,
     * so the parent is cast to int and routed to findAllByParentUid(1) - never
     * findAllByIso2().
     */
    #[Test]
    public function returnsZonesMappedFromRepositoryWhenParentUidHasMatches(): void
    {
        $request = $this->requestWithQueryParams([
            'ajax' => 'sf_register',
            'tx_sfregister' => ['action' => 'zones', 'parent' => '1'],
        ]);

        $repository = $this->createMock(StaticCountryZoneRepository::class);
        $repository->expects($this->once())
            ->method('findAllByParentUid')
            ->with(1)
            ->willReturn($this->createResultMock(1, [
                ['uid' => 4, 'zn_name_local' => 'Bayern'],
            ]));
        $repository->expects($this->never())->method('findAllByIso2');

        $result = $this->getSubject($repository)
            ->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertSame(
            [
                'status' => 'success',
                'message' => '',
                'data' => [
                    ['value' => 4, 'label' => 'Bayern'],
                ],
            ],
            $this->decodeJsonBody($result)
        );
    }

    /**
     * zonesAction() no-rows branch on the parent-uid route.
     */
    #[Test]
    public function returnsNoZonesErrorWhenParentUidHasNoMatches(): void
    {
        $request = $this->requestWithQueryParams([
            'ajax' => 'sf_register',
            'tx_sfregister' => ['action' => 'zones', 'parent' => '999'],
        ]);

        $repository = $this->createMock(StaticCountryZoneRepository::class);
        $repository->expects($this->once())
            ->method('findAllByParentUid')
            ->with(999)
            ->willReturn($this->createResultMock(0, []));

        $result = $this->getSubject($repository)
            ->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertSame(
            ['status' => 'error', 'message' => 'no zones', 'data' => []],
            $this->decodeJsonBody($result)
        );
    }

    /**
     * Exception branch: a Doctrine\DBAL\Exception raised while reading the Result (either
     * rowCount() or fetchAllAssociative()) is caught and turned into
     * status = 'database caused an exception ' . <exception message>, message='no zones'.
     */
    #[Test]
    public function returnsDatabaseExceptionStatusWhenRepositoryResultThrows(): void
    {
        $request = $this->requestWithQueryParams([
            'ajax' => 'sf_register',
            'tx_sfregister' => ['action' => 'zones', 'parent' => 'US'],
        ]);

        $exception = InvalidColumnIndex::new(0);
        $resultMock = $this->createMock(Result::class);
        $resultMock->method('rowCount')->willThrowException($exception);

        $repository = $this->createMock(StaticCountryZoneRepository::class);
        $repository->method('findAllByIso2')->willReturn($resultMock);

        $result = $this->getSubject($repository)
            ->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertSame(
            [
                'status' => 'database caused an exception ' . $exception->getMessage(),
                'message' => 'no zones',
                'data' => [],
            ],
            $this->decodeJsonBody($result)
        );
    }

    /**
     * Pre-fix bug in df53334: process() passes $requestArguments['parent'] straight into
     * zonesAction(string $parent) without a scalar guard. When `tx_sfregister[parent]`
     * arrives as an array (e.g. built from `tx_sfregister[parent][]=1` on the querystring),
     * handing an array to a `string`-typed parameter under `declare(strict_types=1)`
     * throws an uncaught TypeError instead of returning a graceful JSON error response.
     *
     * Verified RED: un-skipping this test against the pre-fix code throws
     * `TypeError: Evoweb\SfRegister\Middleware\AjaxMiddleware::zonesAction(): Argument #1
     * ($parent) must be of type string, array given, called in .../AjaxMiddleware.php on
     * line 51` instead of returning a Response - the repository mock is never reached.
     *
     * Behoben in 30e771a (`$parent = is_scalar($requestArguments['parent']) ?
     * (string)$requestArguments['parent'] : '';` before calling zonesAction()).
     * Reaktivieren in Roadmap-Schritt 2.
     */
    #[Test]
    public function throwsTypeErrorForNonScalarParentInsteadOfReturningResponse(): void
    {
        // Characterizes df53334 behaviour: process() passes an array `tx_sfregister[parent]` straight
        // into zonesAction(string $parent) -> uncaught TypeError under strict_types=1 (the repository is
        // never reached). 30e771a adds a scalar guard coercing a non-scalar parent to '' (behaviour
        // change, not a pure type-fix), so this test goes RED once 30e771a is cherry-picked -> revert
        // that part in 30e771a; the real fix belongs in a later step.
        $request = $this->requestWithQueryParams([
            'ajax' => 'sf_register',
            'tx_sfregister' => ['action' => 'zones', 'parent' => ['1']],
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $repository = $this->createMock(StaticCountryZoneRepository::class);

        $this->expectException(\TypeError::class);

        $this->getSubject($repository)->process($request, $handler);
    }
}
