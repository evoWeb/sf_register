function attachToElement(id, event, func) {
	var element = document.getElementById(id);

	if (element && element.addEventListener) {
		element.addEventListener(event, func, false);
	} else if (element) {
		element.attachEvent('on' + event, func);
	}
}

function callTestPassword() {
	var meter = testPassword(this.value);

		// calculating percent score for sprite
	percentScore = Math.min((Math.floor((meter.intScore / 3.4)) * 10), 100);

		// displaying the sprite
	document.getElementById("bargraph").className = 'is' + percentScore;
}

var loading = false,
	zone,
	zoneEmpty,
	zoneLoading,
	ajaxRequest;

function changeZone(event) {
	if (((event.type == 'keyup' && event.keyCode == 40) ||
			(event.type == 'keyup' && event.keyCode == 38) ||
			event.type == 'change') &&
			loading != true) {
		loading = true;
		var countrySelectedValue = event.srcElement.options[event.srcElement.selectedIndex].value;

		zone.length = 0;
		zone.style.display = 'none';

		zoneLoading.style.display = 'block';

		ajaxRequest = new XMLHttpRequest();
		ajaxRequest.onreadystatechange = XHRResponse;
		ajaxRequest.open('POST', 'index.php?eID=sf_register');
		ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
	}
}

function XHRResponse() {
	if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200) {
		ajaxRequest.responseJSON = JSON.parse(ajaxRequest.responseText);
		zoneLoading.style.display = 'none';

		if (ajaxRequest.responseJSON.status == 'error' || ajaxRequest.responseJSON.data.length == 0) {
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

function attachEvents() {
	attachToElement('sfrPassword', 'keyup', callTestPassword);

	zone = document.getElementById('sfrZone');
	zoneEmpty = document.getElementById('sfrZone_empty');
	zoneLoading = document.getElementById('sfrZone_loading');
	attachToElement('sfrCountry', 'change', changeZone);
	attachToElement('sfrCountry', 'keyup', changeZone);
}
attachEvents();