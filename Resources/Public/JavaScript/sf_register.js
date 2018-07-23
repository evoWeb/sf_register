/* ************************************************************
Created: 20060120
Author:  Steve Moitozo <god at zilla dot us> -- geekwisdom.com
Description: This is a quick and dirty password quality meter
		 written in JavaScript so that the password does
		 not pass over the network.
License: MIT License (see below)
Modified: 20060620 - added MIT License
Modified: 20061111 - corrected regex for letters and numbers
                     Thanks to Zack Smith -- zacksmithdesign.com
---------------------------------------------------------------
Copyright (c) 2006 Steve Moitozo <god at zilla dot us>

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or
sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

   The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE
AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
OR OTHER DEALINGS IN THE SOFTWARE.
---------------------------------------------------------------


Password Strength Factors and Weightings

password length:
level 0 (3 point): less than 4 characters
level 1 (6 points): between 5 and 7 characters
level 2 (12 points): between 8 and 15 characters
level 3 (18 points): 16 or more characters

letters:
level 0 (0 points): no letters
level 1 (5 points): all letters are lower case
level 2 (7 points): letters are mixed case

numbers:
level 0 (0 points): no numbers exist
level 1 (5 points): one number exists
level 1 (7 points): 3 or more numbers exists

special characters:
level 0 (0 points): no special characters
level 1 (5 points): one special character exists
level 2 (10 points): more than one special character exists

combinatons:
level 0 (1 points): letters and numbers exist
level 1 (1 points): mixed case letters
level 1 (2 points): letters, numbers and special characters
					exist
level 1 (2 points): mixed case letters, numbers and special
					characters exist


NOTE: Because I suck at regex the code might need work

NOTE: Instead of putting out all the logging information,
	  the score, and the verdict it would be nicer to stretch
	  a graphic as a method of presenting a visual strength
	  guage.

************************************************************ */
function testPassword(passwd)
{
		var intScore   = 0;
		var strVerdict = "weak";
		var strLog     = "";

		// PASSWORD LENGTH
		if (passwd.length<5)                         // length 4 or less
		{
			intScore = (intScore+3);
			strLog   = strLog + "3 points for length (" + passwd.length + ")\n";
		}
		else if (passwd.length>4 && passwd.length<8) // length between 5 and 7
		{
			intScore = (intScore+6);
			strLog   = strLog + "6 points for length (" + passwd.length + ")\n";
		}
		else if (passwd.length>7 && passwd.length<16)// length between 8 and 15
		{
			intScore = (intScore+12);
			strLog   = strLog + "12 points for length (" + passwd.length + ")\n";
		}
		else if (passwd.length>15)                    // length 16 or more
		{
			intScore = (intScore+18);
			strLog   = strLog + "18 point for length (" + passwd.length + ")\n";
		}


		// LETTERS (Not exactly implemented as dictacted above because of my limited understanding of Regex)
		if (passwd.match(/[a-z]/))                              // [verified] at least one lower case letter
		{
			intScore = (intScore+1);
			strLog   = strLog + "1 point for at least one lower case char\n";
		}

		if (passwd.match(/[A-Z]/))                              // [verified] at least one upper case letter
		{
			intScore = (intScore+5);
			strLog   = strLog + "5 points for at least one upper case char\n";
		}

		// NUMBERS
		if (passwd.match(/\d+/))                                 // [verified] at least one number
		{
			intScore = (intScore+5);
			strLog   = strLog + "5 points for at least one number\n";
		}

		if (passwd.match(/(.*[0-9].*[0-9].*[0-9])/))             // [verified] at least three numbers
		{
			intScore = (intScore+5);
			strLog   = strLog + "5 points for at least three numbers\n";
		}


		// SPECIAL CHAR
		if (passwd.match(/.[!,@#$%^&*?_~]/))            // [verified] at least one special character
		{
			intScore = (intScore+5);
			strLog   = strLog + "5 points for at least one special char\n";
		}

									 // [verified] at least two special characters
		if (passwd.match(/(.*[!,@#$%^&*?_~].*[!,@#$%^&*?_~])/))
		{
			intScore = (intScore+5);
			strLog   = strLog + "5 points for at least two special chars\n";
		}


		// COMBOS
		if (passwd.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/))        // [verified] both upper and lower case
		{
			intScore = (intScore+2);
			strLog   = strLog + "2 combo points for upper and lower letters\n";
		}

		if (passwd.match(/([a-zA-Z])/) && passwd.match(/([0-9])/)) // [verified] both letters and numbers
		{
			intScore = (intScore+2);
			strLog   = strLog + "2 combo points for letters and numbers\n";
		}

									// [verified] letters, numbers, and special characters
		if (passwd.match(/([a-zA-Z0-9].*[!,@#$%^&*?_~])|([!,@#$%^&*?_~].*[a-zA-Z0-9])/))
		{
			intScore = (intScore+2);
			strLog   = strLog + "2 combo points for letters, numbers and special chars\n";
		}


		if(intScore < 16)
		{
		   strVerdict = "very weak";
		}
		else if (intScore > 15 && intScore < 25)
		{
		   strVerdict = "weak";
		}
		else if (intScore > 24 && intScore < 35)
		{
		   strVerdict = "mediocre";
		}
		else if (intScore > 34 && intScore < 45)
		{
		   strVerdict = "strong";
		}
		else
		{
		   strVerdict = "stronger";
		}

	// document.forms.passwordForm.score.value = (intScore)
	// document.forms.passwordForm.verdict.value = (strVerdict)
	// document.forms.passwordForm.matchlog.value = (strLog)
	return {intScore: intScore, strVerdict: strVerdict, strLog: strLog};
}
;/* global define, XMLHttpRequest */
(function(factory) {
	if ('function' === typeof define && define.amd) {
		define('map', ['window'], factory);
	} else {
		factory(window);
	}
})(function(window) {
	var document = window.document,
		module = {},

		loading = false,
		zone,
		zoneEmpty,
		zoneLoading,
		ajaxRequest;

	/**
	 * @param {String} id
	 * @returns {Element}
	 */
	module.getElement = function (id) {
		return 'object' === typeof id ? id : document.getElementById(id);
	};

	/**
	 * @param {Element} element
	 */
	module.showElement = function (element) {
		element.style.display = 'block';
	};

	/**
	 * @param {Element} element
	 */
	module.hideElement = function (element) {
		element.style.display = 'none';
	};

	/**
	 * Attach an event to an element
	 *
	 * @param {String|Object} id
	 * @param {String} eventName
	 * @param {Function} callback
	 */
	module.attachToElement = function (id, eventName, callback) {
		var element = module.getElement(id);

		if (element && element.addEventListener) {
			element.addEventListener(eventName, callback, false);
		} else if (element) {
			element.attachEvent('on' + eventName, callback);
		}
	};


	/**
	 * Gets bargraph element and calls test password function
	 * with value entered in field where this callback is attached
	 *
	 * @return void
	 */
	module.callTestPassword = function () {
		var bargraph = module.getElement('bargraph'),

			// calculating percent score for sprite
			meter = window.testPassword(this.value),
			percentScore = Math.min(
				(Math.floor(meter.intScore / 3.4) * 10),
				100
			) / 10,

			// displaying the sprite
			count = 0,
			blinds = (bargraph.contentDocument || bargraph.contentWindow.document).getElementsByClassName('blind');

		for (var blindKey in blinds) {
			if (blinds.hasOwnProperty(blindKey)) {
				if (count < percentScore) {
					module.hideElement(blinds[blindKey]);
				} else {
					module.showElement(blinds[blindKey]);
				}
				count++;
			}
		}
	};


	/**
	 * Change value of zone selectbox
	 *
	 * @param event
	 */
	module.changeZone = function (event) {
		if (
			(
				(
					event.type === 'keyup' &&
					(event.keyCode === 40 || event.keyCode === 38)
				) ||
				event.type === 'change'
			) &&
			loading !== true
		) {
			if (zone) {
				loading = true;
				var target = event.target || event.srcElement;
				var countrySelectedValue = target.options[target.selectedIndex].value;
				
				zone.length = 0;
				module.hideElement(zone);

				module.hideElement(zoneEmpty);
				module.showElement(zoneLoading);

				ajaxRequest = new XMLHttpRequest();
				ajaxRequest.onreadystatechange = module.xhrReadyStateChanged;
				ajaxRequest.open('POST', 'index.php?eID=sf_register');
				ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
			}
		}
	};

	/**
	 * Process ajax response and display error message or
	 * hand data received to add zone option function
	 *
	 * @return void
	 */
	module.xhrReadyStateChanged = function (stateChanged) {
		var xhrResponse = stateChanged.target;

		if (xhrResponse.readyState === 4 && xhrResponse.status === 200) {
			var xhrResponseData = JSON.parse(xhrResponse.responseText);
			module.hideElement(zoneLoading);

			if (xhrResponseData.status === 'error' || xhrResponseData.data.length === 0) {
				module.showElement(zoneEmpty);
			} else {
				module.addZoneOptions(xhrResponseData.data);
			}
		}

		loading = false;
	};

	/**
	 * Process data received with xhr response
	 *
	 * @param {Array} data
	 *
	 * @constructor
	 */
	module.addZoneOptions = function (data) {
		for (var pointer = 0; pointer < data.length; pointer++) {
			var option = document.createElement('option');
			option.text = data[pointer].label;
			option.value = data[pointer].value;

			zone.options[pointer] = option;
		}

		module.showElement(zone);
	};

	/**
	 * Adds a preview information about file to upload in a label
	 *
	 * @return void
	 */
	module.uploadFile = function () {
		document.getElementById('uploadFile').value = this.value;
	};

	/**
	 * Selects the form and triggers submit
	 *
	 * @return void
	 */
	module.submitForm = function () {
		module.getElement('sfrForm').submit();
	};

	/**
	 * Attach content loaded element with callback to document
	 */
	function initialize() {
		module.attachToElement(document, 'DOMContentLoaded', function () {
			var barGraph = module.getElement('bargraph');
			zone = module.getElement('sfrZone');
			zoneEmpty = module.getElement('sfrZone_empty');
			zoneLoading = module.getElement('sfrZone_loading');

			if (barGraph !== null) {
				barGraph.classList.add('show');
			}

			module.attachToElement('sfrpassword', 'keyup', module.callTestPassword);
			module.attachToElement('sfrCountry', 'change', module.changeZone);
			module.attachToElement('sfrCountry', 'keyup', module.changeZone);
			module.attachToElement('uploadButton', 'change', module.uploadFile);
		});
	}
	initialize();

	window.sfRegister_submitForm = function () {
		module.submitForm();
	};
});
