<?php

namespace Evoweb\SfRegister\Middleware;

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

use Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Api to get information via ajax calls
 * Possible information are static info tables country zones
 * Call eid like
 * ?eID=sf_register&tx_sfregister[action]=zones&tx_sfregister[parent]=DE
 */
class AjaxMiddleware implements MiddlewareInterface
{
    /**
     * Status of the request returned with every response
     *
     * @var string
     */
    protected string $status = 'success';

    /**
     * Message related to the status returned with every response
     *
     * @var string
     */
    protected string $message = '';

    /**
     * Result of every action that gets returned with every response
     *
     * @var array
     */
    protected array $result = [];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestArguments = $this->getParamFromRequest($request, 'tx_sfregister');

        if ((GeneralUtility::_GET('ajax') ?? '') !== 'sf_register') {
            $response = $handler->handle($request);
        } else {
            switch ($requestArguments['action']) {
                case 'zones':
                    $this->zonesAction($requestArguments['parent']);
                    break;

                default:
                    $this->errorAction();
            }

            $response = new JsonResponse([
                'status' => $this->status,
                'message' => $this->message,
                'data' => $this->result,
            ]);
        }

        return $response;
    }

    protected function errorAction()
    {
        $this->status = 'error';
        $this->message = 'unknown action';
    }

    /**
     * @param string $parent
     */
    protected function zonesAction(string $parent)
    {
        /** @var StaticCountryZoneRepository $zoneRepository */
        $zoneRepository = GeneralUtility::getContainer()
            ->get(StaticCountryZoneRepository::class);

        if (MathUtility::canBeInterpretedAsInteger($parent)) {
            $zones = $zoneRepository->findAllByParentUid((int)$parent);
        } else {
            $zones = $zoneRepository->findAllByIso2(strtoupper(preg_replace('/[^A-Za-z]{2}/', '', $parent)));
        }

        if ($zones->rowCount() == 0) {
            $this->status = 'error';
            $this->message = 'no zones';
        } else {
            $result = [];

            $zones = $zones->fetchAllAssociative();
            array_walk($zones, function ($zone) use (&$result) {
                /** @var array $zone */
                $result[] = [
                    'value' => $zone['uid'],
                    'label' => $zone['zn_name_local'],
                ];
            });

            $this->result = $result;
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $name
     *
     * @return array
     */
    protected function getParamFromRequest(ServerRequestInterface $request, string $name): array
    {
        $arguments = $request->getParsedBody()[$name] ?? $request->getQueryParams()[$name] ?? [];
        return is_array($arguments) ? $arguments : [];
    }
}
