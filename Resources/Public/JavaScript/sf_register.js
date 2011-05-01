function attachKeyupEvent() {
	var element = document.getElementById('sfrPassword');

	if (element && element.addEventListener) {
		element.addEventListener('keyup', callTestPassword, false);
	} else if (element) {
		element.attachEvent('onkeyup', callTestPassword);
	}
}

function callTestPassword() {
	var meter = testPassword(this.value);

		// calculating percent score for sprite
	percentScore = Math.min((Math.floor((meter.intScore / 3.4)) * 10), 100);

		// displaying the sprite
	document.getElementById("bargraph").className = 'is' + percentScore;

	console.log(this.value + " " + meter.intScore);
}

attachKeyupEvent();