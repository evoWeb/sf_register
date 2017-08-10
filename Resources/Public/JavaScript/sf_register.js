(function(factory) {
	('function' === typeof define && define.amd) ?
		define('map', ['window'], factory) :
		factory(window);
})(function(window) {
	var document = window.document;

	var module = {},
		loading = false,
		zone,
		zoneEmpty,
		zoneLoading,
		ajaxRequest;

	/**
	 * Attach an event to an element
	 *
	 * @param {string|object} id
	 * @param {string} eventName
	 * @param {function} callback
	 */
	function attachToElement(id, eventName, callback) {
		var element = 'object' === typeof id ? id : document.getElementById(id);

		if (element && element.addEventListener) {
			element.addEventListener(eventName, callback, false);
		} else if (element) {
			element.attachEvent('on' + eventName, callback);
		}
	}

	function callTestPassword() {
		var bargraph = document.getElementById('bargraph'),
			meter = testPassword(this.value),
			// calculating percent score for sprite
			percentScore = Math.min((Math.floor((meter.intScore / 3.4)) * 10), 100) / 10,

			// displaying the sprite
			count = 0,
			blinds = (bargraph.contentDocument || bargraph.contentWindow.document).getElementsByClassName('blind');

		for (var blindKey in blinds) {
			if (blinds.hasOwnProperty(blindKey)) {
				blinds[blindKey].style.display = count < percentScore ? 'none' : 'inherit';
				count++;
			}
		}
	}

	/**
	 * Change value of zone selectbox
	 *
	 * @param event
	 */
	function changeZone(event) {
		if ((
				(event.type === 'keyup' && event.keyCode === 40)
				|| (event.type === 'keyup' && event.keyCode === 38)
				|| event.type === 'change'
			)
			&& loading !== true
		) {
			loading = true;
			var target = event.target || event.srcElement;
			var countrySelectedValue = target.options[target.selectedIndex].value;

			zone.length = 0;
			zone.style.display = 'none';

			zoneEmpty.style.display = 'none';
			zoneLoading.style.display = 'block';

			ajaxRequest = new XMLHttpRequest();
			ajaxRequest.onreadystatechange = XHRResponse;
			ajaxRequest.open('POST', 'index.php?eID=sf_register');
			ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
			ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
		}
	}

	function XHRResponse() {
		if (ajaxRequest.readyState === 4 && ajaxRequest.status === 200) {
			ajaxRequest.responseJSON = JSON.parse(ajaxRequest.responseText);
			zoneLoading.style.display = 'none';

			if (ajaxRequest.responseJSON.status === 'error' || ajaxRequest.responseJSON.data.length === 0) {
				zoneEmpty.style.display = 'block';
			} else {
				XHRResponseSuccess(ajaxRequest.responseJSON.data);
			}
		}

		loading = false;
	}

	function XHRResponseSuccess(data) {
		for (var pointer = 0; pointer < data.length; pointer++) {
			var option = document.createElement('option');
			option.text = data[pointer].label;
			option.value = data[pointer].value;

			zone.options[pointer] = option;
		}

		zone.style.display = 'block';
	}

	function uploadFile() {
		document.getElementById('uploadFile').value = this.value;
	}

	function attachEvents() {
		attachToElement('sfrpassword', 'keyup', callTestPassword);

		zone = document.getElementById('sfrZone');
		zoneEmpty = document.getElementById('sfrZone_empty');
		zoneLoading = document.getElementById('sfrZone_loading');

		attachToElement('sfrCountry', 'change', changeZone);
		attachToElement('sfrCountry', 'keyup', changeZone);
		attachToElement('uploadButton', 'change', uploadFile);
	}

	/**
	 * Attach content loaded element with callback to document
	 */
	function initialize() {
		attachToElement(document, 'DOMContentLoaded', function () {
			attachEvents();
		});
	}
	initialize();

	return module;
});
