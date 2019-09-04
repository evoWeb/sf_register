(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var PasswordStrengthCalculator = /** @class */ (function () {
    function PasswordStrengthCalculator() {
    }
    /**
     * password length:
     * level 0 (0 point): less than 4 characters
     * level 1 (6 points): between 5 and 7 characters
     * level 2 (12 points): between 8 and 15 characters
     * level 3 (18 points): 16 or more characters
     */
    PasswordStrengthCalculator.prototype.verdictLength = function (password) {
        var score = 0, log = '', length = password.length;
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
        return { score: score, log: log };
    };
    ;
    /**
     * letters:
     * level 0 (0 points): no letters
     * level 1 (5 points): all letters are lower case
     * level 1 (5 points): all letters are upper case
     * level 2 (7 points): letters are mixed case
     */
    PasswordStrengthCalculator.prototype.verdictLetter = function (password) {
        var score = 0, log = '', matchLower = password.match(/[a-z]/), matchUpper = password.match(/[A-Z]/);
        if (matchLower) {
            if (matchUpper) {
                score = 7;
                log = '7 points for letters are mixed';
            }
            else {
                score = 5;
                log = '5 point for at least one lower case char';
            }
        }
        else if (matchUpper) {
            score = 5;
            log = '5 points for at least one upper case char';
        }
        return { score: score, log: log };
    };
    ;
    /**
     * numbers:
     * level 0 (0 points): no numbers exist
     * level 1 (5 points): one number exists
     * level 1 (7 points): 3 or more numbers exists
     */
    PasswordStrengthCalculator.prototype.verdictNumbers = function (password) {
        var score = 0, log = '', numbers = password.replace(/\D/gi, '');
        if (numbers.length > 1) {
            score = 7;
            log = '7 points for at least three numbers';
        }
        else if (numbers.length > 0) {
            score = 5;
            log = '5 points for at least one number';
        }
        return { score: score, log: log };
    };
    ;
    /**
     * special characters:
     * level 0 (0 points): no special characters
     * level 1 (5 points): one special character exists
     * level 2 (10 points): more than one special character exists
     */
    PasswordStrengthCalculator.prototype.verdictSpecialChars = function (password) {
        var score = 0, log = '', specialCharacters = password.replace(/[\w\s]/gi, '');
        if (specialCharacters.length > 1) {
            score = 10;
            log = '10 points for at least two special chars';
        }
        else if (specialCharacters.length > 0) {
            score = 5;
            log = '5 points for at least one special char';
        }
        return { score: score, log: log };
    };
    ;
    /**
     * combinations:
     * level 0 (1 points): mixed case letters
     * level 0 (1 points): letters and numbers
     * level 1 (2 points): mixed case letters and numbers
     * level 3 (4 points): letters, numbers and special characters
     * level 4 (6 points): mixed case letters, numbers and special characters
     */
    PasswordStrengthCalculator.prototype.verdictCombos = function (letter, number, special) {
        var score = 0, log = '';
        if (letter === 7 && number > 0 && special > 0) {
            score = 6;
            log = '6 combo points for letters, numbers and special characters';
        }
        else if (letter > 0 && number > 0 && special > 0) {
            score = 4;
            log = '4 combo points for letters, numbers and special characters';
        }
        else if (letter === 7 && number > 0) {
            score = 2;
            log = '2 combo points for mixed case letters and numbers';
        }
        else if (letter > 0 && number > 0) {
            score = 1;
            log = '1 combo points for letters and numbers';
        }
        else if (letter === 7) {
            score = 1;
            log = '1 combo points for mixed case letters';
        }
        return { score: score, log: log };
    };
    ;
    /**
     * final verdict base on final score
     */
    PasswordStrengthCalculator.prototype.finalVerdict = function (finalScore) {
        var strVerdict = '';
        if (finalScore < 16) {
            strVerdict = 'very weak';
        }
        else if (finalScore > 15 && finalScore < 25) {
            strVerdict = 'weak';
        }
        else if (finalScore > 24 && finalScore < 35) {
            strVerdict = 'mediocre';
        }
        else if (finalScore > 34 && finalScore < 45) {
            strVerdict = 'strong';
        }
        else {
            strVerdict = 'stronger';
        }
        return strVerdict;
    };
    ;
    PasswordStrengthCalculator.prototype.calculate = function (password) {
        var lengthVerdict = this.verdictLength(password);
        var letterVerdict = this.verdictLetter(password);
        var numberVerdict = this.verdictNumbers(password);
        var specialVerdict = this.verdictSpecialChars(password);
        var combosVerdict = this.verdictCombos(letterVerdict.score, numberVerdict.score, specialVerdict.score);
        var score = lengthVerdict.score
            + letterVerdict.score
            + numberVerdict.score
            + specialVerdict.score
            + combosVerdict.score;
        var log = [
            lengthVerdict.log,
            letterVerdict.log,
            numberVerdict.log,
            specialVerdict.log,
            combosVerdict.log,
            score + ' points final score'
        ].join("\n");
        return { score: score, verdict: this.finalVerdict(score), log: log };
    };
    return PasswordStrengthCalculator;
}());
exports.default = PasswordStrengthCalculator;

},{}],2:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var PasswordStrengthCalculator_1 = require("./PasswordStrengthCalculator");
var document = window.document;
var SfRegister = /** @class */ (function () {
    function SfRegister() {
        this.loading = false;
        this.ajaxRequest = null;
        this.barGraph = null;
        this.passwordStrengthCalculator = null;
        this.zone = null;
        this.zoneEmpty = null;
        this.zoneLoading = null;
        // Attach content loaded element with callback to document
        document.addEventListener('DOMContentLoaded', this.contentLoaded.bind(this));
    }
    /**
     * Callback after content was loaded
     */
    SfRegister.prototype.contentLoaded = function () {
        this.zone = document.getElementById('sfrZone');
        this.zoneEmpty = document.getElementById('sfrZone_empty');
        this.zoneLoading = document.getElementById('sfrZone_loading');
        this.barGraph = document.getElementById('bargraph');
        if (this.barGraph) {
            this.barGraph.classList.add('show');
            this.passwordStrengthCalculator = new PasswordStrengthCalculator_1.default();
            if (this.isInternetExplorer()) {
                this.loadInternetExplorerPolyfill();
            }
            else {
                this.attachToElementById('sfrpassword', 'keyup', this.callTestPassword.bind(this));
            }
        }
        this.attachToElementById('sfrCountry', 'change', this.countryChanged.bind(this));
        this.attachToElementById('sfrCountry', 'keyup', this.countryChanged.bind(this));
        this.attachToElementById('uploadButton', 'change', this.uploadFile.bind(this));
        this.attachToElementById('removeImageButton', 'click', this.removeFile.bind(this));
    };
    ;
    /**
     * Add class d-block remove class d-none
     */
    SfRegister.prototype.showElement = function (element) {
        element.classList.remove('d-none');
        element.classList.add('d-block');
    };
    ;
    /**
     * Add class d-none remove class d-block
     */
    SfRegister.prototype.hideElement = function (element) {
        element.classList.remove('d-block');
        element.classList.add('d-none');
    };
    ;
    SfRegister.prototype.attachToElementById = function (id, eventName, callback) {
        var element = document.getElementById(id);
        this.attachToElement(element, eventName, callback);
    };
    SfRegister.prototype.attachToElement = function (element, eventName, callback) {
        if (element) {
            element.addEventListener(eventName, callback);
        }
    };
    ;
    /**
     * Gets password meter element and sets the value with
     * the result of the calculate password strength function
     */
    SfRegister.prototype.callTestPassword = function (event) {
        var element = event.target, meterResult = this.passwordStrengthCalculator.calculate(element.value);
        if (this.barGraph.tagName.toLowerCase() === 'meter') {
            this.barGraph.value = meterResult.score;
        }
        else {
            var barGraph = this.barGraph, percentScore = Math.min((Math.floor(meterResult.score / 3.4)), 10), blinds = (barGraph.contentDocument || barGraph.contentWindow.document).getElementsByClassName('blind');
            for (var index = 0; index < blinds.length; index++) {
                var blind = blinds[index];
                if (index < percentScore) {
                    this.hideElement(blind);
                }
                else {
                    this.showElement(blind);
                }
            }
        }
    };
    ;
    SfRegister.prototype.isInternetExplorer = function () {
        var userAgent = navigator.userAgent;
        /* MSIE used to detect old browsers and Trident used to newer ones*/
        return userAgent.indexOf('MSIE ') > -1 || userAgent.indexOf('Trident/') > -1;
    };
    ;
    SfRegister.prototype.loadInternetExplorerPolyfill = function () {
        var _this = this;
        var body = document.getElementsByTagName('body').item(0), js = document.createElement('script');
        js.setAttribute('type', 'text/javascript');
        js.setAttribute('src', 'https://unpkg.com/meter-polyfill/dist/meter-polyfill.min.js');
        js.onload = function () {
            // @ts-ignore
            meterPolyfill(_this.barGraph);
            _this.attachToElementById('sfrpassword', 'keyup', _this.callTestPassword);
        };
        body.appendChild(js);
    };
    ;
    /**
     * Change value of zone selectbox
     */
    SfRegister.prototype.countryChanged = function (event) {
        if ((event.type === 'change'
            || (event.type === 'keyup' && (event.keyCode === 40 || event.keyCode === 38)))
            && this.loading !== true) {
            if (this.zone) {
                var target = (event.target || event.srcElement), countrySelectedValue = target.options[target.selectedIndex].value;
                this.loading = true;
                this.zone.disabled = true;
                this.hideElement(this.zoneEmpty);
                this.showElement(this.zoneLoading);
                this.ajaxRequest = new XMLHttpRequest();
                this.ajaxRequest.onload = this.xhrReadyOnLoad.bind(this);
                this.ajaxRequest.open('POST', 'index.php?ajax=sf_register');
                this.ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                this.ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
            }
        }
    };
    ;
    /**
     * Process ajax response and display error message or
     * hand data received to add zone option function
     */
    SfRegister.prototype.xhrReadyOnLoad = function (stateChanged) {
        var xhrResponse = stateChanged.target;
        if (xhrResponse.readyState === 4 && xhrResponse.status === 200) {
            var xhrResponseData = JSON.parse(xhrResponse.responseText);
            this.hideElement(this.zoneLoading);
            if (xhrResponseData.status === 'error' || xhrResponseData.data.length === 0) {
                this.showElement(this.zoneEmpty);
            }
            else {
                this.addZoneOptions(xhrResponseData.data);
            }
        }
        this.loading = false;
    };
    ;
    /**
     * Process data received with xhr response
     */
    SfRegister.prototype.addZoneOptions = function (options) {
        var _this = this;
        while (this.zone.length) {
            this.zone.removeChild(this.zone[0]);
        }
        options.forEach(function (option, index) {
            _this.zone.options[index] = new Option(option.label, option.value);
        });
        this.zone.disabled = false;
    };
    ;
    /**
     * Adds a preview information about file to upload in a label
     */
    SfRegister.prototype.uploadFile = function () {
        var information = document.getElementById('uploadFile');
        if (information) {
            information.value = this.value;
        }
    };
    ;
    /**
     * Handle remove image button clicked
     */
    SfRegister.prototype.removeFile = function () {
        var remove = document.getElementById('removeImage');
        if (remove) {
            remove.value = '1';
        }
        this.submitForm();
    };
    ;
    /**
     * Selects the form and triggers submit
     */
    SfRegister.prototype.submitForm = function () {
        var form = document.getElementById('sfrForm');
        if (form) {
            form.reset();
        }
    };
    ;
    return SfRegister;
}());
exports.default = SfRegister;

},{"./PasswordStrengthCalculator":1}],3:[function(require,module,exports){
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var SfRegister_1 = require("./SfRegister");
var sfRegister = new SfRegister_1.default();
/**
 * Global function needed for invisible recaptcha
 */
window.sfRegister_submitForm = function () {
    sfRegister.submitForm();
};

},{"./SfRegister":2}]},{},[3])

