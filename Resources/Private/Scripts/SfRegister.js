/* global define, XMLHttpRequest */
(function (root, factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['exports', './PasswordStrengthCalculator'], factory);
	} else if (typeof exports === 'object' && typeof exports.nodeName !== 'string') {
		// CommonJS
		factory(exports, require(':/PasswordStrengthCalculator'));
	} else {
		// Browser globals
		factory((root.commonJsStrict = {}), root.PasswordStrengthCalculator);
	}
}(typeof self !== 'undefined' ? self : this, function (exports, PasswordStrengthCalculator) {
	let document = window.document;

	class SfRegister {
		/**
		 * @type {boolean}
		 */
		loading = false;

		/**
		 * @type {object}
		 */
		ajaxRequest = null;

		/**
		 * @type {object|element}
		 */
		barGraph = null;

		/**
		 * @type {element}
		 */
		zone = null;

		/**
		 * @type {element}
		 */
		zoneEmpty = null;

		/**
		 * @type {element}
		 */
		zoneLoading = null;

		constructor() {
			let self = this;
			// Attach content loaded element with callback to document
			self.attachToElement(document, 'DOMContentLoaded', self.contentLoaded.bind(self));
		}

		/**
		 * Callback after content was loaded
		 */
		contentLoaded = () => {
			let self = this;

			this.barGraph = document.getElementById('bargraph');
			this.zone = document.getElementById('sfrZone');
			this.zoneEmpty = document.getElementById('sfrZone_empty');
			this.zoneLoading = document.getElementById('sfrZone_loading');

			if (this.barGraph !== null) {
				this.barGraph.classList.add('show');
				this.barGraph.passwordStrengthCalculator = new PasswordStrengthCalculator();
				this.attachToElement('sfrpassword', 'keyup', function () {
					self.callTestPassword(this);
				});
			}

			this.attachToElement('sfrCountry', 'change', function (event) {
				self.countryChanged(event);
			});
			this.attachToElement('sfrCountry', 'keyup', function (event) {
				self.countryChanged(event);
			});
			this.attachToElement('uploadButton', 'change', this.uploadFile);
			this.attachToElement('removeImageButton', 'click', this.removeFile.bind(this));
		};

		/**
		 * Set display to block
		 *
		 * @param {Object} element
		 */
		showElement = (element) => {
			element.style.display = 'block';
		};

		/**
		 * Set display to none
		 *
		 * @param {Object} element
		 */
		hideElement = (element) => {
			element.style.display = 'none';
		};

		/**
		 * Attach an event to an element
		 *
		 * @param {string|Object} id
		 * @param {string} eventName
		 * @param {callback} callback
		 */
		attachToElement = (id, eventName, callback) => {
			let element = 'object' === typeof id ? id : document.getElementById(id);

			if (element && element.addEventListener) {
				element.addEventListener(eventName, callback, false);
			} else if (element) {
				element.attachEvent('on' + eventName, callback);
			}
		};


		/**
		 * Gets password meter element and sets the value with
		 * the result of the calculate password strength function
		 */
		callTestPassword = (element) => {
			let self = this,
				meterResult = this.barGraph.passwordStrengthCalculator.calculate(element.value);

			if (this.barGraph.tagName.toLowerCase() === 'meter') {
				this.barGraph.value = meterResult.score;
			} else {
				let percentScore = Math.min((Math.floor(meterResult.score / 3.4)), 10),
					blinds = (
						this.barGraph.contentDocument || this.barGraph.contentWindow.document
					).getElementsByClassName('blind');

				Array.from(blinds).forEach(function (blind, index) {
					self[index < percentScore ? 'hideElement' : 'showElement'](blind);
				});
			}
		};


		/**
		 * Change value of zone selectbox
		 *
		 * @param {event} event
		 */
		countryChanged = (event) => {
			if (
				(
					(
						event.type === 'keyup'
						&& (event.keyCode === 40 || event.keyCode === 38)
					)
					|| event.type === 'change'
				)
				&& this.loading !== true
			) {
				if (this.zone) {
					let target = event.target || event.srcElement,
						countrySelectedValue = target.options[target.selectedIndex].value;

					this.loading = true;

					this.zone.length = 0;
					this.hideElement(this.zone);
					this.hideElement(this.zoneEmpty);
					this.showElement(this.zoneLoading);

					this.ajaxRequest = new XMLHttpRequest();
					this.ajaxRequest.onreadystatechange = this.xhrReadyStateChanged.bind(this);
					this.ajaxRequest.open('POST', 'index.php?eID=sf_register');
					this.ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
					this.ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
				}
			}
		};

		/**
		 * Process ajax response and display error message or
		 * hand data received to add zone option function
		 */
		xhrReadyStateChanged = (stateChanged) => {
			let xhrResponse = stateChanged.target;

			if (xhrResponse.readyState === 4 && xhrResponse.status === 200) {
				let xhrResponseData = JSON.parse(xhrResponse.responseText);
				this.hideElement(this.zoneLoading);

				if (xhrResponseData.status === 'error' || xhrResponseData.data.length === 0) {
					this.showElement(zoneEmpty);
				} else {
					this.addZoneOptions(xhrResponseData.data);
				}
			}

			this.loading = false;
		};

		/**
		 * Process data received with xhr response
		 *
		 * @param {[]} options
		 */
		addZoneOptions = (options) => {
			this.zone.options = [];
			options.forEach(function (option, index) {
				this.options[index] = new Option(option.label, option.value);
			}.bind(this.zone));

			this.showElement(this.zone);
		};

		/**
		 * Adds a preview information about file to upload in a label
		 */
		uploadFile = () => {
			document.getElementById('uploadFile').value = this.value;
		};

		/**
		 * Handle remove image button clicked
		 */
		removeFile = () => {
			document.getElementById('removeImage').value = 1;
			this.submitForm();
		};

		/**
		 * Selects the form and triggers submit
		 */
		submitForm = () => {
			document.getElementById('sfregister_form').submit();
		};
	}

	let sfRegister = new SfRegister();

	/**
	 * Register global function to be accessible from outside of the module
	 */
	window.sfRegister_submitForm = function () {
		sfRegister.submitForm();
	};

	exports.SfRegister = module;
}));
