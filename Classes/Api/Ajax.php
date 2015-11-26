<?php
namespace Evoweb\SfRegister\Api;

/***************************************************************
 * Copyright notice
 *
 * (c) 2011-15 Sebastian Fischer <typo3@evoweb.de>
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

/**
 * Api to get informations via ajax calls
 * Possible informations are static info tables country zones
 * Call eid like
 * ?eID=sf_register&tx_sfregister[action]=zones&tx_sfregister[parent]=DE
 */
class Ajax
{
    /**
     * Request parameters from url
     *
     * @var array
     */
    protected $requestArguments = [];

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

    /**
     * Constructor of the class
     *
     * @return self
     */
    public function __construct()
    {
        $this->requestArguments = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('tx_sfregister');
    }

    /**
     * Dispatch the given action and call the output rendering afterwards
     *
     * @return void
     */
    public function dispatch()
    {
        switch ($this->requestArguments['action']) {
            case 'zones':
                if (\TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($this->requestArguments['parent'])) {
                    $this->getZonesByParentId();
                } else {
                    $this->getZonesByParentIso2Code();
                }
                break;

            default:
                $this->status = 'error';
                $this->message = 'unknown action';
        }

        $this->output();
    }

    /**
     * Query the static info table country zones to
     * get all zones for the given parent if any
     *
     * @return void
     */
    protected function getZonesByParentId()
    {
        $zones = [];
        $parent = (int) $this->requestArguments['parent'];

        if ($parent) {
            /**
             * Database connection
             *
             * @var $database \TYPO3\CMS\Core\Database\DatabaseConnection
             */
            $database = &$GLOBALS['TYPO3_DB'];
            $queryResult = $database->exec_SELECTquery(
                'z.uid as value, z.zn_name_local as label',
                'static_country_zones AS z
                    INNER JOIN static_countries AS c ON z.zn_country_iso_2 = c.cn_iso_2',
                'c.uid = ' . $parent . ' AND z.deleted = 0 AND c.deleted = 0',
                '',
                'z.zn_name_local'
            );

            if (!$database->sql_num_rows($queryResult)) {
                $this->status = 'error';
                $this->message = 'no zones';
            } else {
                while (($rows = $database->sql_fetch_assoc($queryResult))) {
                    $zones[] = $rows;
                }
            }
            $database->sql_free_result($queryResult);
        }

        $this->result = $zones;
    }

    /**
     * Query the static info table country zones to
     * get all zones for the given parent if any
     *
     * @return void
     */
    protected function getZonesByParentIso2Code()
    {
        $zones = [];
        $parent = strtoupper(preg_replace('/[^A-Za-z]/', '', $this->requestArguments['parent']));

        if (strlen($parent)) {
            /**
             * Database connection
             *
             * @var $database \TYPO3\CMS\Core\Database\DatabaseConnection
             */
            $database = &$GLOBALS['TYPO3_DB'];
            $queryResult = $database->exec_SELECTquery(
                'zn_code as value, zn_name_local as label',
                'static_country_zones',
                'zn_country_iso_2 = \'' . $parent . '\' AND deleted = 0',
                '',
                'zn_name_local'
            );

            if (!$database->sql_num_rows($queryResult)) {
                $this->status = 'error';
                $this->message = 'no zones';
            } else {
                while (($rows = $database->sql_fetch_assoc($queryResult))) {
                    $zones[] = $rows;
                }
            }
            $database->sql_free_result($queryResult);
        }

        $this->result = $zones;
    }

    /**
     * Render the status, message and result as json encoded array as response
     *
     * @return void
     */
    protected function output()
    {
        $result = array(
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->result,
        );

        echo json_encode($result);
    }
}

if (\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('eID')) {
    /** @var \Evoweb\SfRegister\Api\Ajax $ajax */
    $ajax = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Evoweb\SfRegister\Api\Ajax::class);
    $ajax->dispatch();
}
