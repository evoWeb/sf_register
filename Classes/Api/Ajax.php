<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Sebastian Fischer <typo3@evoweb.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Api to get informations via ajax calls
 * Possible informations are static info tables country zones
 *
 * Call eid like
 * ?eID=sf_register&tx_sfregister[action]=zones&tx_sfregister[parent]=DE
 */
class Tx_SfRegister_Api_Ajax {
	/**
	 * Request parameters from url
	 *
	 * @var	array
	 */
	protected $requestArguments = array();

	/**
	 * Status of the request returned with every response
	 *
	 * @var	string
	 */
	protected $status = 'success';

	/**
	 * Message related to the status returned with every response
	 *
	 * @var	string
	 */
	protected $message = '';

	/**
	 * Result of every action that gets returned with every response
	 *
	 * @var	array
	 */
	protected $result = array();

	/**
	 * Constructor of the class
	 */
	public function __construct() {
		tslib_eidtools::connectDB();

		$this->requestArguments = t3lib_div::_GP('tx_sfregister');
	}

	/**
	 * Dispatch the given action and call the output rendering afterwards
	 * 
	 * @return	void
	 */
	public function dispatch() {
		switch ($this->requestArguments['action']) {
			case 'zones':
				$this->getZonesByParent();
				break;

			default:
				$this->status = 'error';
				$this->message = 'unknown action';
				break;
		}

		$this->output();
	}

	/**
	 * Render the status, message and result as json encoded array as response
	 *
	 * @return	string
	 */
	protected function output() {
		$result = array(
			'status' => $this->status,
			'message'=> $this->message,
			'data' => $this->result,
		);

		echo json_encode($result);
	}

	/**
	 * Query the static info table country zones to get all zones for the given parent if any
	 *
	 * @return	void
	 */
	protected function getZonesByParent() {
		$zones = array();
		$parent = strtoupper(preg_replace('/[^A-Za-z]/', '', $this->requestArguments['parent']));

		if (strlen($parent)) {
			$queryResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'zn_code as value, zn_name_local as label',
				'static_country_zones',
				'zn_country_iso_2 = \'' . $parent . '\'',
				'',
				'zn_name_local'
			);

			if (!$GLOBALS['TYPO3_DB']->sql_num_rows($queryResult)) {
				$this->status = 'error';
				$this->message = 'no zones';
			} else {
				while ($rows = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($queryResult)) {
					$zones[] = $rows;
				}
			}
		}

		$this->result = $zones;
	}
}

if (t3lib_div::_GET('eID')) {
    t3lib_div::makeInstance('Tx_SfRegister_Api_Ajax')->dispatch();
}
 
?>