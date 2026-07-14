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

namespace Evoweb\SfRegister\Middleware;

use Doctrine\DBAL\Exception;
use Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\DependencyInjection\Attribute\Lazy;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Api to get information via ajax calls
 * Possible information are static info tables country zones
 * Call eid like
 * ?eID=sf_register&tx_sfregister[action]=zones&tx_sfregister[parent]=DE
 */
class AjaxMiddleware implements MiddlewareInterface
{
    public function __construct(
        #[Lazy]
        protected StaticCountryZoneRepository $staticCountryZoneRepository
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->canHandleRequest($request)) {
            return $handler->handle($request);
        }

        $requestArguments = $this->getParamFromRequest($request, 'tx_sfregister');
        switch ($requestArguments['action']) {
            case 'zones':
                // Behaviour-preserving: keep df53334 behaviour where a non-scalar `parent` is passed
                // straight into zonesAction(string) (uncaught TypeError). 30e771a's is_scalar guard
                // changed behaviour and is deferred to a later fix step.
                // @phpstan-ignore-next-line argument.type
                [$status, $message, $result] = $this->zonesAction($requestArguments['parent']);
                break;

            default:
                [$status, $message, $result] = $this->errorAction();
        }

        return new JsonResponse([
            'status' => $status,
            'message' => $message,
            'data' => $result,
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    protected function errorAction(): array
    {
        return ['error', 'unknown action', []];
    }

    /**
     * @return array<int, mixed>
     */
    protected function zonesAction(string $parent): array
    {
        if (MathUtility::canBeInterpretedAsInteger($parent)) {
            $zones = $this->staticCountryZoneRepository->findAllByParentUid((int)$parent);
        } else {
            $upper = preg_replace('/[^A-Za-z]{2}/', '', $parent);
            $upper = is_string($upper) ? mb_strtoupper($upper) : '';
            $zones = $this->staticCountryZoneRepository->findAllByIso2($upper);
        }

        $result = [];
        try {
            if ($zones->rowCount() == 0) {
                $status = 'error';
                $message = 'no zones';
            } else {
                $status = 'success';
                $message = '';

                $zones = $zones->fetchAllAssociative();
                array_walk($zones, static function (array $zone) use (&$result): void {
                    $result[] = [
                        'value' => $zone['uid'],
                        'label' => $zone['zn_name_local'],
                    ];
                });
            }
        } catch (Exception $exception) {
            $status = 'database caused an exception ' . $exception->getMessage();
            $message = 'no zones';
        }

        return [$status, $message, $result];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getParamFromRequest(ServerRequestInterface $request, string $name): array
    {
        $parsedBody = $request->getParsedBody();
        $parsedBody = is_array($parsedBody) ? $parsedBody : [];
        $arguments = $parsedBody[$name] ?? $request->getQueryParams()[$name] ?? [];
        return is_array($arguments) ? $arguments : [];
    }

    protected function canHandleRequest(ServerRequestInterface $request): bool
    {
        return ($request->getQueryParams()['ajax'] ?? '') === 'sf_register';
    }
}
