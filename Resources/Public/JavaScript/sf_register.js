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

__webpack_require__(1);
module.exports = __webpack_require__(2);


/***/ }),
/* 1 */
/***/ (function(module, exports) {

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
function testPassword(passwd) {
  var intScore = 0;
  var strVerdict = "weak";
  var strLog = ""; // PASSWORD LENGTH

  if (passwd.length < 5) // length 4 or less
    {
      intScore = intScore + 3;
      strLog = strLog + "3 points for length (" + passwd.length + ")\n";
    } else if (passwd.length > 4 && passwd.length < 8) // length between 5 and 7
    {
      intScore = intScore + 6;
      strLog = strLog + "6 points for length (" + passwd.length + ")\n";
    } else if (passwd.length > 7 && passwd.length < 16) // length between 8 and 15
    {
      intScore = intScore + 12;
      strLog = strLog + "12 points for length (" + passwd.length + ")\n";
    } else if (passwd.length > 15) // length 16 or more
    {
      intScore = intScore + 18;
      strLog = strLog + "18 point for length (" + passwd.length + ")\n";
    } // LETTERS (Not exactly implemented as dictacted above because of my limited understanding of Regex)


  if (passwd.match(/[a-z]/)) // [verified] at least one lower case letter
    {
      intScore = intScore + 1;
      strLog = strLog + "1 point for at least one lower case char\n";
    }

  if (passwd.match(/[A-Z]/)) // [verified] at least one upper case letter
    {
      intScore = intScore + 5;
      strLog = strLog + "5 points for at least one upper case char\n";
    } // NUMBERS


  if (passwd.match(/\d+/)) // [verified] at least one number
    {
      intScore = intScore + 5;
      strLog = strLog + "5 points for at least one number\n";
    }

  if (passwd.match(/(.*[0-9].*[0-9].*[0-9])/)) // [verified] at least three numbers
    {
      intScore = intScore + 5;
      strLog = strLog + "5 points for at least three numbers\n";
    } // SPECIAL CHAR


  if (passwd.match(/.[!,@#$%^&*?_~]/)) // [verified] at least one special character
    {
      intScore = intScore + 5;
      strLog = strLog + "5 points for at least one special char\n";
    } // [verified] at least two special characters


  if (passwd.match(/(.*[!,@#$%^&*?_~].*[!,@#$%^&*?_~])/)) {
    intScore = intScore + 5;
    strLog = strLog + "5 points for at least two special chars\n";
  } // COMBOS


  if (passwd.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) // [verified] both upper and lower case
    {
      intScore = intScore + 2;
      strLog = strLog + "2 combo points for upper and lower letters\n";
    }

  if (passwd.match(/([a-zA-Z])/) && passwd.match(/([0-9])/)) // [verified] both letters and numbers
    {
      intScore = intScore + 2;
      strLog = strLog + "2 combo points for letters and numbers\n";
    } // [verified] letters, numbers, and special characters


  if (passwd.match(/([a-zA-Z0-9].*[!,@#$%^&*?_~])|([!,@#$%^&*?_~].*[a-zA-Z0-9])/)) {
    intScore = intScore + 2;
    strLog = strLog + "2 combo points for letters, numbers and special chars\n";
  }

  if (intScore < 16) {
    strVerdict = "very weak";
  } else if (intScore > 15 && intScore < 25) {
    strVerdict = "weak";
  } else if (intScore > 24 && intScore < 35) {
    strVerdict = "mediocre";
  } else if (intScore > 34 && intScore < 45) {
    strVerdict = "strong";
  } else {
    strVerdict = "stronger";
  } // document.forms.passwordForm.score.value = (intScore)
  // document.forms.passwordForm.verdict.value = (strVerdict)
  // document.forms.passwordForm.matchlog.value = (strLog)


  return {
    intScore: intScore,
    strVerdict: strVerdict,
    strLog: strLog
  };
}

window.testPassword = testPassword;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/* global define, XMLHttpRequest */
(function (factory) {
  if (true) {
    !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__)) : __WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
  } else {}
})(function () {
  var document = window.document,
      module = {},
      loading = false,
      zone,
      zoneEmpty,
      zoneLoading,
      ajaxRequest;
  /**
   * @param {string} id
   *
   * @returns {Object}
   */

  module.getElement = function (id) {
    return 'object' === _typeof(id) ? id : document.getElementById(id);
  };
  /**
   * @param {Object} element
   */


  module.showElement = function (element) {
    element.style.display = 'block';
  };
  /**
   * @param {Object} element
   */


  module.hideElement = function (element) {
    element.style.display = 'none';
  };
  /**
   * Attach an event to an element
   *
   * @param {string|Object} id
   * @param {string} eventName
   * @param {callback} callback
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
   */


  module.callTestPassword = function () {
    var bargraph = module.getElement('bargraph'),
        // calculating percent score for sprite
    meter = window.testPassword(this.value),
        percentScore = Math.min(Math.floor(meter.intScore / 3.4) * 10, 100) / 10,
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
   * @param {event} event
   */


  module.changeZone = function (event) {
    if ((event.type === 'keyup' && (event.keyCode === 40 || event.keyCode === 38) || event.type === 'change') && loading !== true) {
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
   * @param {[]} options
   */


  module.addZoneOptions = function (options) {
    zone.options = [];
    options.forEach(function (option, index) {
      zone.options[index] = {
        test: option.label,
        value: option.value
      };
    });
    module.showElement(zone);
  };
  /**
   * Adds a preview information about file to upload in a label
   */


  module.uploadFile = function () {
    document.getElementById('uploadFile').value = this.value;
  };
  /**
   * Selects the form and triggers submit
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
  /**
   * Register global function to be accessible from outside of the module
   */

  window.sfRegister_submitForm = function () {
    module.submitForm();
  };
});

/***/ })
/******/ ]);
//# sourceMappingURL=sf_register.js.map