<?php
namespace Evoweb\SfRegister\Middleware;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-2019 Sebastian Fischer <typo3@evoweb.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Evoweb\SfRegister\Domain\Repository\StaticCountryZoneRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Api to get information via ajax calls
 * Possible information are static info tables country zones
 * Call eid like
 * ?eID=sf_register&tx_sfregister[action]=zones&tx_sfregister[parent]=DE
 */
class AjaxMiddleware implements \Psr\Http\Server\MiddlewareInterface
{
    /**
     * Status of the request returned with every response
     *
     * @var string
     */
    protected $status = 'success';

    /**
     * Message related to the status returned with every response
     *
     * @var string
     */
    protected $message = '';

    /**
     * Result of every action that gets returned with every response
     *
     * @var array
     */
    protected $result = [];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestArguments = $this->getParamFromRequest($request, 'tx_sfregister');

        if (!\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('ajax') == 'sf_register') {
            $response = $handler->handle($request);
        } else {
            switch ($requestArguments['action']) {
                case 'zones':
                    $this->zonesAction($requestArguments['parent']);
                    break;

                default:
                    $this->errorAction();
            }

            $response = new \TYPO3\CMS\Core\Http\JsonResponse([
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
     * @param int|string $parent
     */
    protected function zonesAction($parent)
    {
        /** @var StaticCountryZoneRepository $zoneRepository */
        $zoneRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Object\ObjectManager::class
        )->get(StaticCountryZoneRepository::class);

        if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($parent)) {
            $zones = $zoneRepository->findAllByParentUid((int) $parent);
        } else {
            $zones = $zoneRepository->findAllByIso2(strtoupper(preg_replace('/[^A-Za-z]{2}/', '', $parent)));
        }

        if ($zones->rowCount() == 0) {
            $this->status = 'error';
            $this->message = 'no zones';
        } else {
            $result = [];

            array_walk($zones->fetchAll(), function ($zone) use (&$result) {
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
