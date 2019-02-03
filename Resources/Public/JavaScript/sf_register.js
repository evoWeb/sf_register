/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(1);


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/* global define, XMLHttpRequest */
(function (root, factory) {
  if (true) {
    // AMD. Register as an anonymous module.
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [exports, __webpack_require__(2)], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else {}
})(typeof self !== 'undefined' ? self : this, function (exports, PasswordStrengthCalculator) {
  var document = window.document;

  var SfRegister =
  /**
   * @type {boolean}
   */

  /**
   * @type {object}
   */

  /**
   * @type {object|element}
   */

  /**
   * @type {element}
   */

  /**
   * @type {element}
   */

  /**
   * @type {element}
   */
  function SfRegister() {
    var _this = this;

    _classCallCheck(this, SfRegister);

    _defineProperty(this, "loading", false);

    _defineProperty(this, "ajaxRequest", null);

    _defineProperty(this, "barGraph", null);

    _defineProperty(this, "zone", null);

    _defineProperty(this, "zoneEmpty", null);

    _defineProperty(this, "zoneLoading", null);

    _defineProperty(this, "contentLoaded", function () {
      _this.barGraph = document.getElementById('bargraph');
      _this.zone = document.getElementById('sfrZone');
      _this.zoneEmpty = document.getElementById('sfrZone_empty');
      _this.zoneLoading = document.getElementById('sfrZone_loading');

      if (_this.barGraph !== null) {
        _this.barGraph.classList.add('show');

        _this.barGraph.passwordStrengthCalculator = new PasswordStrengthCalculator();

        if (!_this.isInternetExplorer()) {
          _this.attachToElement('sfrpassword', 'keyup', _this.callTestPassword.bind(_this));
        } else {
          _this.loadInternetExplorerPolyfill();
        }
      }

      _this.attachToElement('sfrCountry', 'change', _this.countryChanged.bind(_this));

      _this.attachToElement('sfrCountry', 'keyup', _this.countryChanged.bind(_this));

      _this.attachToElement('uploadButton', 'change', _this.uploadFile);

      _this.attachToElement('removeImageButton', 'click', _this.removeFile.bind(_this));
    });

    _defineProperty(this, "showElement", function (element) {
      element.style.display = 'block';
    });

    _defineProperty(this, "hideElement", function (element) {
      element.style.display = 'none';
    });

    _defineProperty(this, "attachToElement", function (id, eventName, callback) {
      var element = 'object' === _typeof(id) ? id : document.getElementById(id);

      if (element && element.addEventListener) {
        element.addEventListener(eventName, callback, false);
      } else if (element) {
        element.attachEvent('on' + eventName, callback);
      }
    });

    _defineProperty(this, "callTestPassword", function (event) {
      var element = event.target,
          meterResult = _this.barGraph.passwordStrengthCalculator.calculate(element.value);

      if (_this.barGraph.tagName.toLowerCase() === 'meter') {
        _this.barGraph.value = meterResult.score;
      } else {
        var percentScore = Math.min(Math.floor(meterResult.score / 3.4), 10),
            blinds = (_this.barGraph.contentDocument || _this.barGraph.contentWindow.document).getElementsByClassName('blind');

        var _self2 = _this;
        Array.from(blinds).forEach(function (blind, index) {
          _self2[index < percentScore ? 'hideElement' : 'showElement'](blind);
        });
      }
    });

    _defineProperty(this, "isInternetExplorer", function () {
      var ua = navigator.userAgent;
      /* MSIE used to detect old browsers and Trident used to newer ones*/

      return ua.indexOf("MSIE ") > -1 || ua.indexOf("Trident/") > -1;
    });

    _defineProperty(this, "loadInternetExplorerPolyfill", function () {
      var self = _this,
          body = document.getElementsByTagName('body').item(0),
          js = document.createElement('script');
      js.setAttribute('type', 'text/javascript');
      js.setAttribute('src', 'https://unpkg.com/meter-polyfill/dist/meter-polyfill.min.js');

      js.onload = function () {
        meterPolyfill(self.barGraph);
        self.attachToElement('sfrpassword', 'keyup', self.callTestPassword.bind(this));
      };

      body.appendChild(js);
    });

    _defineProperty(this, "countryChanged", function (event) {
      if ((event.type === 'keyup' && (event.keyCode === 40 || event.keyCode === 38) || event.type === 'change') && _this.loading !== true) {
        if (_this.zone) {
          var target = event.target || event.srcElement,
              countrySelectedValue = target.options[target.selectedIndex].value;
          _this.loading = true;
          _this.zone.length = 0;

          _this.hideElement(_this.zone);

          _this.hideElement(_this.zoneEmpty);

          _this.showElement(_this.zoneLoading);

          _this.ajaxRequest = new XMLHttpRequest();
          _this.ajaxRequest.onreadystatechange = _this.xhrReadyStateChanged.bind(_this);

          _this.ajaxRequest.open('POST', 'index.php?eID=sf_register');

          _this.ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

          _this.ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
        }
      }
    });

    _defineProperty(this, "xhrReadyStateChanged", function (stateChanged) {
      var xhrResponse = stateChanged.target;

      if (xhrResponse.readyState === 4 && xhrResponse.status === 200) {
        var xhrResponseData = JSON.parse(xhrResponse.responseText);

        _this.hideElement(_this.zoneLoading);

        if (xhrResponseData.status === 'error' || xhrResponseData.data.length === 0) {
          _this.showElement(zoneEmpty);
        } else {
          _this.addZoneOptions(xhrResponseData.data);
        }
      }

      _this.loading = false;
    });

    _defineProperty(this, "addZoneOptions", function (options) {
      _this.zone.options = [];
      options.forEach(function (option, index) {
        this.options[index] = new Option(option.label, option.value);
      }.bind(_this.zone));

      _this.showElement(_this.zone);
    });

    _defineProperty(this, "uploadFile", function () {
      document.getElementById('uploadFile').value = _this.value;
    });

    _defineProperty(this, "removeFile", function () {
      document.getElementById('removeImage').value = 1;

      _this.submitForm();
    });

    _defineProperty(this, "submitForm", function () {
      document.getElementById('sfregister_form').submit();
    });

    var _self = this; // Attach content loaded element with callback to document


    _self.attachToElement(document, 'DOMContentLoaded', _self.contentLoaded.bind(_self));
  }
  /**
   * Callback after content was loaded
   */
  ;

  var sfRegister = new SfRegister();
  /**
   * Global function needed for invisible recaptcha
   */

  window.sfRegister_submitForm = function () {
    sfRegister.submitForm();
  };

  exports.SfRegister = sfRegister;
});

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

(function PasswordStrengthCalculator(root, factory) {
  if (true) {
    // AMD. Register as an anonymous module.
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else {}
})(this, function () {
  var PasswordStrengthCalculator = function PasswordStrengthCalculator() {
    var _this = this;

    _classCallCheck(this, PasswordStrengthCalculator);

    _defineProperty(this, "verdictLength", function (password) {
      var score = 0,
          log = '',
          length = password.length;

      switch (true) {
        case length > 0 && length < 5:
          log = '3 points for length (' + length + ')';
          score = 3;
          break;

        case length > 4 && length < 8:
          log = '6 points for length (' + length + ')';
          score = 6;
          break;

        case length > 7 && length < 16:
          log = '12 points for length (' + length + ')';
          score = 12;
          break;

        case length > 15:
          log = '18 points for length (' + length + ')';
          score = 18;
          break;
      }

      return {
        score: score,
        log: log
      };
    });

    _defineProperty(this, "verdictLetter", function (password) {
      var score = 0,
          log = '',
          matchLower = password.match(/[a-z]/),
          matchUpper = password.match(/[A-Z]/);

      if (matchLower) {
        if (matchUpper) {
          score = 7;
          log = '7 points for letters are mixed';
        } else {
          score = 5;
          log = '5 point for at least one lower case char';
        }
      } else if (matchUpper) {
        score = 5;
        log = '5 points for at least one upper case char';
      }

      return {
        score: score,
        log: log
      };
    });

    _defineProperty(this, "verdictNumbers", function (password) {
      var score = 0,
          log = '',
          numbers = password.replace(/\D/gi, '');

      if (numbers.length > 1) {
        score = 7;
        log = '7 points for at least three numbers';
      } else if (numbers.length > 0) {
        score = 5;
        log = '5 points for at least one number';
      }

      return {
        score: score,
        log: log
      };
    });

    _defineProperty(this, "verdictSpecialChars", function (password) {
      var score = 0,
          log = '',
          specialCharacters = password.replace(/[\w\s]/gi, '');

      if (specialCharacters.length > 1) {
        score = 10;
        log = '10 points for at least two special chars';
      } else if (specialCharacters.length > 0) {
        score = 5;
        log = '5 points for at least one special char';
      }

      return {
        score: score,
        log: log
      };
    });

    _defineProperty(this, "verdictCombos", function (verdicts) {
      var score = 0,
          log = '';

      if (verdicts.letter === 7 && verdicts.number > 0 && verdicts.special > 0) {
        score = 6;
        log = '6 combo points for letters, numbers and special characters';
      } else if (verdicts.letter > 0 && verdicts.number > 0 && verdicts.special > 0) {
        score = 4;
        log = '4 combo points for letters, numbers and special characters';
      } else if (verdicts.letter === 7 && verdicts.number > 0) {
        score = 2;
        log = '2 combo points for mixed case letters and numbers';
      } else if (verdicts.letter > 0 && verdicts.number > 0) {
        score = 1;
        log = '1 combo points for letters and numbers';
      } else if (verdicts.letter === 7) {
        score = 1;
        log = '1 combo points for mixed case letters';
      }

      return {
        score: score,
        log: log
      };
    });

    _defineProperty(this, "finalVerdict", function (finalScore) {
      var strVerdict = '';

      if (finalScore < 16) {
        strVerdict = 'very weak';
      } else if (finalScore > 15 && finalScore < 25) {
        strVerdict = 'weak';
      } else if (finalScore > 24 && finalScore < 35) {
        strVerdict = 'mediocre';
      } else if (finalScore > 34 && finalScore < 45) {
        strVerdict = 'strong';
      } else {
        strVerdict = 'stronger';
      }

      return strVerdict;
    });

    _defineProperty(this, "calculate", function (password) {
      var lengthVerdict = _this.verdictLength(password);

      var letterVerdict = _this.verdictLetter(password);

      var numberVerdict = _this.verdictNumbers(password);

      var specialVerdict = _this.verdictSpecialChars(password);

      var combosVerdict = _this.verdictCombos({
        letter: letterVerdict.score,
        number: numberVerdict.score,
        special: specialVerdict.score
      });

      var score = lengthVerdict.score + letterVerdict.score + numberVerdict.score + specialVerdict.score + combosVerdict.score;
      var log = [lengthVerdict.log, letterVerdict.log, numberVerdict.log, specialVerdict.log, combosVerdict.log, score + ' points final score'].join("\n");
      return {
        score: score,
        verdict: _this.finalVerdict(score),
        log: log
      };
    });
  };

  return PasswordStrengthCalculator;
});

/***/ })
/******/ ]);
//# sourceMappingURL=sf_register.js.map