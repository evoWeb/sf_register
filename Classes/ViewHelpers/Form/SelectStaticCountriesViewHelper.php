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

class Tx_SfRegister_ViewHelpers_Form_SelectStaticCountriesViewHelper extends Tx_SfRegister_ViewHelpers_Form_SelectStaticViewHelper {
	/**
	 * @var Tx_SfRegister_Domain_Repository_StaticCountryRepository
	 */
	protected $countryRepository;

	/**
	 * Initialize arguments. Cant be moved to parent because of "private $argumentDefinitions = array();"
	 *
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('multiple', 'string', 'if set, multiple select field');
		$this->registerTagAttribute('size', 'string', 'Size of input field');
		$this->registerTagAttribute('disabled', 'string', 'Specifies that the input element should be disabled when the page loads');
		$this->registerArgument('name', 'string', 'Name of input tag');
		$this->registerArgument('value', 'mixed', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. If used in conjunction with <f:form object="...">, "name" and "value" properties will be ignored.');
		$this->registerArgument('optionValueField', 'string', 'If specified, will call the appropriate getter on each object to determine the value.', FALSE, 'cnIso2');
		$this->registerArgument('optionLabelField', 'string', 'If specified, will call the appropriate getter on each object to determine the label.', FALSE, 'cnOfficialNameEn');
		$this->registerArgument('sortByOptionLabel', 'boolean', 'If true, List will be sorted by label.', FALSE, TRUE);
		$this->registerArgument('selectAllByDefault', 'boolean', 'If specified options are selected if none was set before.', FALSE, FALSE);
		$this->registerArgument('errorClass', 'string', 'CSS class to set if there are errors for this view helper', FALSE, 'f3-form-error');
	}

	/**
	 * Injects the country repository
	 *
	 * @param Tx_SfRegister_Domain_Repository_StaticCountryRepository $countryRepository
	 * @return void
	 */
	public function injectCountryRepository(Tx_SfRegister_Domain_Repository_StaticCountryRepository $countryRepository) {
		$this->countryRepository = $countryRepository;
	}

	/**
	 * @return void
	 */
	public function initialize() {
		parent::initialize();
		$this->options = $this->countryRepository->findAll();
	}
}

?>