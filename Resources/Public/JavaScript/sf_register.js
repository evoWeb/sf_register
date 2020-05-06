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
            form.submit();
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
    return new Promise(function (resolve, reject) {
        if (grecaptcha === undefined) {
            alert('Recaptcha ist nicht definiert');
            reject();
        }
        var captchaField = document.getElementById('captcha');
        captchaField.value = grecaptcha.getResponse();
        sfRegister.submitForm();
        resolve();
    });
};

},{"./SfRegister":2}]},{},[3])
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJTb3VyY2VzL1R5cGVTY3JpcHQvUGFzc3dvcmRTdHJlbmd0aENhbGN1bGF0b3IudHMiLCJTb3VyY2VzL1R5cGVTY3JpcHQvU2ZSZWdpc3Rlci50cyIsIlNvdXJjZXMvVHlwZVNjcmlwdC9zZl9yZWdpc3Rlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7O0FDTUE7SUFBQTtJQWlMQSxDQUFDO0lBaExDOzs7Ozs7T0FNRztJQUNILGtEQUFhLEdBQWIsVUFBYyxRQUFnQjtRQUM1QixJQUFJLEtBQUssR0FBRyxDQUFDLEVBQ1gsR0FBRyxHQUFHLEVBQUUsRUFDUixNQUFNLEdBQUcsUUFBUSxDQUFDLE1BQU0sQ0FBQztRQUMzQixRQUFRLElBQUksRUFBRTtZQUNaLEtBQUssTUFBTSxHQUFHLENBQUMsSUFBSSxNQUFNLEdBQUcsQ0FBQztnQkFDM0IsR0FBRyxHQUFHLHVCQUF1QixHQUFHLE1BQU0sR0FBRyxHQUFHLENBQUM7Z0JBQzdDLEtBQUssR0FBRyxDQUFDLENBQUM7Z0JBQ1YsTUFBTTtZQUVSLEtBQUssTUFBTSxHQUFHLENBQUMsSUFBSSxNQUFNLEdBQUcsQ0FBQztnQkFDM0IsR0FBRyxHQUFHLHVCQUF1QixHQUFHLE1BQU0sR0FBRyxHQUFHLENBQUM7Z0JBQzdDLEtBQUssR0FBRyxDQUFDLENBQUM7Z0JBQ1YsTUFBTTtZQUVSLEtBQUssTUFBTSxHQUFHLENBQUMsSUFBSSxNQUFNLEdBQUcsRUFBRTtnQkFDNUIsR0FBRyxHQUFHLHdCQUF3QixHQUFHLE1BQU0sR0FBRyxHQUFHLENBQUM7Z0JBQzlDLEtBQUssR0FBRyxFQUFFLENBQUM7Z0JBQ1gsTUFBTTtZQUVSLEtBQUssTUFBTSxHQUFHLEVBQUU7Z0JBQ2QsR0FBRyxHQUFHLHdCQUF3QixHQUFHLE1BQU0sR0FBRyxHQUFHLENBQUM7Z0JBQzlDLEtBQUssR0FBRyxFQUFFLENBQUM7Z0JBQ1gsTUFBTTtTQUNUO1FBQ0Qs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QUFPLEVBQUUsQ0FBQztJQUNaLENBQUMsQ0FBQyxDQUFDO0FBQ0wsQ0FBQyxDQUFDIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24oKXtmdW5jdGlvbiByKGUsbix0KXtmdW5jdGlvbiBvKGksZil7aWYoIW5baV0pe2lmKCFlW2ldKXt2YXIgYz1cImZ1bmN0aW9uXCI9PXR5cGVvZiByZXF1aXJlJiZyZXF1aXJlO2lmKCFmJiZjKXJldHVybiBjKGksITApO2lmKHUpcmV0dXJuIHUoaSwhMCk7dmFyIGE9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitpK1wiJ1wiKTt0aHJvdyBhLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsYX12YXIgcD1uW2ldPXtleHBvcnRzOnt9fTtlW2ldWzBdLmNhbGwocC5leHBvcnRzLGZ1bmN0aW9uKHIpe3ZhciBuPWVbaV1bMV1bcl07cmV0dXJuIG8obnx8cil9LHAscC5leHBvcnRzLHIsZSxuLHQpfXJldHVybiBuW2ldLmV4cG9ydHN9Zm9yKHZhciB1PVwiZnVuY3Rpb25cIj09dHlwZW9mIHJlcXVpcmUmJnJlcXVpcmUsaT0wO2k8dC5sZW5ndGg7aSsrKW8odFtpXSk7cmV0dXJuIG99cmV0dXJuIHJ9KSgpIiwiaW50ZXJmYWNlIFZlcmRpY3Qge1xuICBzY29yZTogbnVtYmVyLFxuICBsb2c6IHN0cmluZyxcbiAgdmVyZGljdD86IHN0cmluZ1xufVxuXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBQYXNzd29yZFN0cmVuZ3RoQ2FsY3VsYXRvciB7XG4gIC8qKlxuICAgKiBwYXNzd29yZCBsZW5ndGg6XG4gICAqIGxldmVsIDAgKDAgcG9pbnQpOiBsZXNzIHRoYW4gNCBjaGFyYWN0ZXJzXG4gICAqIGxldmVsIDEgKDYgcG9pbnRzKTogYmV0d2VlbiA1IGFuZCA3IGNoYXJhY3RlcnNcbiAgICogbGV2ZWwgMiAoMTIgcG9pbnRzKTogYmV0d2VlbiA4IGFuZCAxNSBjaGFyYWN0ZXJzXG4gICAqIGxldmVsIDMgKDE4IHBvaW50cyk6IDE2IG9yIG1vcmUgY2hhcmFjdGVyc1xuICAgKi9cbiAgdmVyZGljdExlbmd0aChwYXNzd29yZDogU3RyaW5nKTogVmVyZGljdCB7XG4gICAgbGV0IHNjb3JlID0gMCxcbiAgICAgIGxvZyA9ICcnLFxuICAgICAgbGVuZ3RoID0gcGFzc3dvcmQubGVuZ3RoO1xuICAgIHN3aXRjaCAodHJ1ZSkge1xuICAgICAgY2FzZSBsZW5ndGggPiAwICYmIGxlbmd0aCA8IDU6XG4gICAgICAgIGxvZyA9ICczIHBvaW50cyBmb3IgbGVuZ3RoICgnICsgbGVuZ3RoICsgJyknO1xuICAgICAgICBzY29yZSA9IDM7XG4gICAgICAgIGJyZWFrO1xuXG4gICAgICBjYXNlIGxlbmd0aCA+IDQgJiYgbGVuZ3RoIDwgODpcbiAgICAgICAgbG9nID0gJzYgcG9pbnRzIGZvciBsZW5ndGggKCcgKyBsZW5ndGggKyAnKSc7XG4gICAgICAgIHNjb3JlID0gNjtcbiAgICAgICAgYnJlYWs7XG5cbiAgICAgIGNhc2UgbGVuZ3RoID4gNyAmJiBsZW5ndGggPCAxNjpcbiAgICAgICAgbG9nID0gJzEyIHBvaW50cyBmb3IgbGVuZ3RoICgnICsgbGVuZ3RoICsgJyknO1xuICAgICAgICBzY29yZSA9IDEyO1xuICAgICAgICBicmVhaztcblxuICAgICAgY2FzZSBsZW5ndGggPiAxNTpcbiAgICAgICAgbG9nID0gJzE4IHBvaW50cyBmb3IgbGVuZ3RoICgnICsgbGVuZ3RoICsgJyknO1xuICAgICAgICBzY29yZSA9IDE4O1xuICAgICAgICBicmVhaztcbiAgICB9XG4gICAgcmV0dXJuIHtzY29yZTogc2NvcmUsIGxvZzogbG9nfTtcbiAgfTtcblxuICAvKipcbiAgICogbGV0dGVyczpcbiAgICogbGV2ZWwgMCAoMCBwb2ludHMpOiBubyBsZXR0ZXJzXG4gICAqIGxldmVsIDEgKDUgcG9pbnRzKTogYWxsIGxldHRlcnMgYXJlIGxvd2VyIGNhc2VcbiAgICogbGV2ZWwgMSAoNSBwb2ludHMpOiBhbGwgbGV0dGVycyBhcmUgdXBwZXIgY2FzZVxuICAgKiBsZXZlbCAyICg3IHBvaW50cyk6IGxldHRlcnMgYXJlIG1peGVkIGNhc2VcbiAgICovXG4gIHZlcmRpY3RMZXR0ZXIocGFzc3dvcmQ6IFN0cmluZyk6IFZlcmRpY3Qge1xuICAgIGxldCBzY29yZSA9IDAsXG4gICAgICBsb2cgPSAnJyxcbiAgICAgIG1hdGNoTG93ZXIgPSBwYXNzd29yZC5tYXRjaCgvW2Etel0vKSxcbiAgICAgIG1hdGNoVXBwZXIgPSBwYXNzd29yZC5tYXRjaCgvW0EtWl0vKTtcbiAgICBpZiAobWF0Y2hMb3dlcikge1xuICAgICAgaWYgKG1hdGNoVXBwZXIpIHtcbiAgICAgICAgc2NvcmUgPSA3O1xuICAgICAgICBsb2cgPSAnNyBwb2ludHMgZm9yIGxldHRlcnMgYXJlIG1peGVkJztcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHNjb3JlID0gNTtcbiAgICAgICAgbG9nID0gJzUgcG9pbnQgZm9yIGF0IGxlYXN0IG9uZSBsb3dlciBjYXNlIGNoYXInO1xuICAgICAgfVxuICAgIH0gZWxzZSBpZiAobWF0Y2hVcHBlcikge1xuICAgICAgc2NvcmUgPSA1O1xuICAgICAgbG9nID0gJzUgcG9pbnRzIGZvciBhdCBsZWFzdCBvbmUgdXBwZXIgY2FzZSBjaGFyJztcbiAgICB9XG4gICAgcmV0dXJuIHtzY29yZTogc2NvcmUsIGxvZzogbG9nfTtcbiAgfTtcblxuICAvKipcbiAgICogbnVtYmVyczpcbiAgICogbGV2ZWwgMCAoMCBwb2ludHMpOiBubyBudW1iZXJzIGV4aXN0XG4gICAqIGxldmVsIDEgKDUgcG9pbnRzKTogb25lIG51bWJlciBleGlzdHNcbiAgICogbGV2ZWwgMSAoNyBwb2ludHMpOiAzIG9yIG1vcmUgbnVtYmVycyBleGlzdHNcbiAgICovXG4gIHZlcmRpY3ROdW1iZXJzKHBhc3N3b3JkOiBTdHJpbmcpOiBWZXJkaWN0IHtcbiAgICBsZXQgc2NvcmUgPSAwLFxuICAgICAgbG9nID0gJycsXG4gICAgICBudW1iZXJzID0gcGFzc3dvcmQucmVwbGFjZSgvXFxEL2dpLCAnJyk7XG4gICAgaWYgKG51bWJlcnMubGVuZ3RoID4gMSkge1xuICAgICAgc2NvcmUgPSA3O1xuICAgICAgbG9nID0gJzcgcG9pbnRzIGZvciBhdCBsZWFzdCB0aHJlZSBudW1iZXJzJztcbiAgICB9IGVsc2UgaWYgKG51bWJlcnMubGVuZ3RoID4gMCkge1xuICAgICAgc2NvcmUgPSA1O1xuICAgICAgbG9nID0gJzUgcG9pbnRzIGZvciBhdCBsZWFzdCBvbmUgbnVtYmVyJztcbiAgICB9XG4gICAgcmV0dXJuIHtzY29yZTogc2NvcmUsIGxvZzogbG9nfTtcbiAgfTtcblxuICAvKipcbiAgICogc3BlY2lhbCBjaGFyYWN0ZXJzOlxuICAgKiBsZXZlbCAwICgwIHBvaW50cyk6IG5vIHNwZWNpYWwgY2hhcmFjdGVyc1xuICAgKiBsZXZlbCAxICg1IHBvaW50cyk6IG9uZSBzcGVjaWFsIGNoYXJhY3RlciBleGlzdHNcbiAgICogbGV2ZWwgMiAoMTAgcG9pbnRzKTogbW9yZSB0aGFuIG9uZSBzcGVjaWFsIGNoYXJhY3RlciBleGlzdHNcbiAgICovXG4gIHZlcmRpY3RTcGVjaWFsQ2hhcnMocGFzc3dvcmQ6IFN0cmluZyk6IFZlcmRpY3Qge1xuICAgIGxldCBzY29yZSA9IDAsXG4gICAgICBsb2cgPSAnJyxcbiAgICAgIHNwZWNpYWxDaGFyYWN0ZXJzID0gcGFzc3dvcmQucmVwbGFjZSgvW1xcd1xcc10vZ2ksICcnKTtcbiAgICBpZiAoc3BlY2lhbENoYXJhY3RlcnMubGVuZ3RoID4gMSkge1xuICAgICAgc2NvcmUgPSAxMDtcbiAgICAgIGxvZyA9ICcxMCBwb2ludHMgZm9yIGF0IGxlYXN0IHR3byBzcGVjaWFsIGNoYXJzJztcbiAgICB9IGVsc2UgaWYgKHNwZWNpYWxDaGFyYWN0ZXJzLmxlbmd0aCA+IDApIHtcbiAgICAgIHNjb3JlID0gNTtcbiAgICAgIGxvZyA9ICc1IHBvaW50cyBmb3IgYXQgbGVhc3Qgb25lIHNwZWNpYWwgY2hhcic7XG4gICAgfVxuICAgIHJldHVybiB7c2NvcmU6IHNjb3JlLCBsb2c6IGxvZ307XG4gIH07XG5cbiAgLyoqXG4gICAqIGNvbWJpbmF0aW9uczpcbiAgICogbGV2ZWwgMCAoMSBwb2ludHMpOiBtaXhlZCBjYXNlIGxldHRlcnNcbiAgICogbGV2ZWwgMCAoMSBwb2ludHMpOiBsZXR0ZXJzIGFuZCBudW1iZXJzXG4gICAqIGxldmVsIDEgKDIgcG9pbnRzKTogbWl4ZWQgY2FzZSBsZXR0ZXJzIGFuZCBudW1iZXJzXG4gICAqIGxldmVsIDMgKDQgcG9pbnRzKTogbGV0dGVycywgbnVtYmVycyBhbmQgc3BlY2lhbCBjaGFyYWN0ZXJzXG4gICAqIGxldmVsIDQgKDYgcG9pbnRzKTogbWl4ZWQgY2FzZSBsZXR0ZXJzLCBudW1iZXJzIGFuZCBzcGVjaWFsIGNoYXJhY3RlcnNcbiAgICovXG4gIHZlcmRpY3RDb21ib3MobGV0dGVyOiBudW1iZXIsIG51bWJlcjogbnVtYmVyLCBzcGVjaWFsOiBudW1iZXIpOiBWZXJkaWN0IHtcbiAgICBsZXQgc2NvcmUgPSAwLFxuICAgICAgbG9nID0gJyc7XG4gICAgaWYgKGxldHRlciA9PT0gNyAmJiBudW1iZXIgPiAwICYmIHNwZWNpYWwgPiAwKSB7XG4gICAgICBzY29yZSA9IDY7XG4gICAgICBsb2cgPSAnNiBjb21ibyBwb2ludHMgZm9yIGxldHRlcnMsIG51bWJlcnMgYW5kIHNwZWNpYWwgY2hhcmFjdGVycyc7XG4gICAgfSBlbHNlIGlmIChsZXR0ZXIgPiAwICYmIG51bWJlciA+IDAgJiYgc3BlY2lhbCA+IDApIHtcbiAgICAgIHNjb3JlID0gNDtcbiAgICAgIGxvZyA9ICc0IGNvbWJvIHBvaW50cyBmb3IgbGV0dGVycywgbnVtYmVycyBhbmQgc3BlY2lhbCBjaGFyYWN0ZXJzJztcbiAgICB9IGVsc2UgaWYgKGxldHRlciA9PT0gNyAmJiBudW1iZXIgPiAwKSB7XG4gICAgICBzY29yZSA9IDI7XG4gICAgICBsb2cgPSAnMiBjb21ibyBwb2ludHMgZm9yIG1peGVkIGNhc2UgbGV0dGVycyBhbmQgbnVtYmVycyc7XG4gICAgfSBlbHNlIGlmIChsZXR0ZXIgPiAwICYmIG51bWJlciA+IDApIHtcbiAgICAgIHNjb3JlID0gMTtcbiAgICAgIGxvZyA9ICcxIGNvbWJvIHBvaW50cyBmb3IgbGV0dGVycyBhbmQgbnVtYmVycyc7XG4gICAgfSBlbHNlIGlmIChsZXR0ZXIgPT09IDcpIHtcbiAgICAgIHNjb3JlID0gMTtcbiAgICAgIGxvZyA9ICcxIGNvbWJvIHBvaW50cyBmb3IgbWl4ZWQgY2FzZSBsZXR0ZXJzJztcbiAgICB9XG4gICAgcmV0dXJuIHtzY29yZTogc2NvcmUsIGxvZzogbG9nfTtcbiAgfTtcblxuICAvKipcbiAgICogZmluYWwgdmVyZGljdCBiYXNlIG9uIGZpbmFsIHNjb3JlXG4gICAqL1xuICBmaW5hbFZlcmRpY3QoZmluYWxTY29yZTogbnVtYmVyKTogc3RyaW5nIHtcbiAgICBsZXQgc3RyVmVyZGljdCA9ICcnO1xuICAgIGlmIChmaW5hbFNjb3JlIDwgMTYpIHtcbiAgICAgIHN0clZlcmRpY3QgPSAndmVyeSB3ZWFrJztcbiAgICB9IGVsc2UgaWYgKGZpbmFsU2NvcmUgPiAxNSAmJiBmaW5hbFNjb3JlIDwgMjUpIHtcbiAgICAgIHN0clZlcmRpY3QgPSAnd2Vhayc7XG4gICAgfSBlbHNlIGlmIChmaW5hbFNjb3JlID4gMjQgJiYgZmluYWxTY29yZSA8IDM1KSB7XG4gICAgICBzdHJWZXJkaWN0ID0gJ21lZGlvY3JlJztcbiAgICB9IGVsc2UgaWYgKGZpbmFsU2NvcmUgPiAzNCAmJiBmaW5hbFNjb3JlIDwgNDUpIHtcbiAgICAgIHN0clZlcmRpY3QgPSAnc3Ryb25nJztcbiAgICB9IGVsc2Uge1xuICAgICAgc3RyVmVyZGljdCA9ICdzdHJvbmdlcic7XG4gICAgfVxuICAgIHJldHVybiBzdHJWZXJkaWN0O1xuICB9O1xuXG4gIGNhbGN1bGF0ZShwYXNzd29yZDogc3RyaW5nKTogVmVyZGljdCB7XG4gICAgbGV0IGxlbmd0aFZlcmRpY3QgPSB0aGlzLnZlcmRpY3RMZW5ndGgocGFzc3dvcmQpO1xuICAgIGxldCBsZXR0ZXJWZXJkaWN0ID0gdGhpcy52ZXJkaWN0TGV0dGVyKHBhc3N3b3JkKTtcbiAgICBsZXQgbnVtYmVyVmVyZGljdCA9IHRoaXMudmVyZGljdE51bWJlcnMocGFzc3dvcmQpO1xuICAgIGxldCBzcGVjaWFsVmVyZGljdCA9IHRoaXMudmVyZGljdFNwZWNpYWxDaGFycyhwYXNzd29yZCk7XG4gICAgbGV0IGNvbWJvc1ZlcmRpY3QgPSB0aGlzLnZlcmRpY3RDb21ib3MobGV0dGVyVmVyZGljdC5zY29yZSwgbnVtYmVyVmVyZGljdC5zY29yZSwgc3BlY2lhbFZlcmRpY3Quc2NvcmUpO1xuXG4gICAgbGV0IHNjb3JlID1cbiAgICAgIGxlbmd0aFZlcmRpY3Quc2NvcmVcbiAgICAgICsgbGV0dGVyVmVyZGljdC5zY29yZVxuICAgICAgKyBudW1iZXJWZXJkaWN0LnNjb3JlXG4gICAgICArIHNwZWNpYWxWZXJkaWN0LnNjb3JlXG4gICAgICArIGNvbWJvc1ZlcmRpY3Quc2NvcmU7XG5cbiAgICBsZXQgbG9nID0gW1xuICAgICAgbGVuZ3RoVmVyZGljdC5sb2csXG4gICAgICBsZXR0ZXJWZXJkaWN0LmxvZyxcbiAgICAgIG51bWJlclZlcmRpY3QubG9nLFxuICAgICAgc3BlY2lhbFZlcmRpY3QubG9nLFxuICAgICAgY29tYm9zVmVyZGljdC5sb2csXG4gICAgICBzY29yZSArICcgcG9pbnRzIGZpbmFsIHNjb3JlJ1xuICAgIF0uam9pbihcIlxcblwiKTtcblxuICAgIHJldHVybiB7c2NvcmU6IHNjb3JlLCB2ZXJkaWN0OiB0aGlzLmZpbmFsVmVyZGljdChzY29yZSksIGxvZzogbG9nfTtcbiAgfVxufVxuIiwiaW1wb3J0IFBhc3N3b3JkU3RyZW5ndGhDYWxjdWxhdG9yIGZyb20gJy4vUGFzc3dvcmRTdHJlbmd0aENhbGN1bGF0b3InO1xuXG5pbnRlcmZhY2UgU2VsZWN0T3B0aW9uIHtcbiAgbGFiZWw6IHN0cmluZyxcbiAgdmFsdWU6IHN0cmluZyxcbn1cblxubGV0IGRvY3VtZW50ID0gd2luZG93LmRvY3VtZW50O1xuXG5leHBvcnQgZGVmYXVsdCBjbGFzcyBTZlJlZ2lzdGVyIHtcbiAgcHVibGljIGxvYWRpbmc6IGJvb2xlYW4gPSBmYWxzZTtcbiAgcHVibGljIGFqYXhSZXF1ZXN0OiBYTUxIdHRwUmVxdWVzdCA9IG51bGw7XG4gIHB1YmxpYyBiYXJHcmFwaDpIVE1MTWV0ZXJFbGVtZW50ID0gbnVsbDtcbiAgcHVibGljIHBhc3N3b3JkU3RyZW5ndGhDYWxjdWxhdG9yOlBhc3N3b3JkU3RyZW5ndGhDYWxjdWxhdG9yID0gbnVsbDtcbiAgcHVibGljIHpvbmU6SFRNTFNlbGVjdEVsZW1lbnQgPSBudWxsO1xuICBwdWJsaWMgem9uZUVtcHR5OkhUTUxFbGVtZW50ID0gbnVsbDtcbiAgcHVibGljIHpvbmVMb2FkaW5nOkhUTUxFbGVtZW50ID0gbnVsbDtcblxuICBjb25zdHJ1Y3RvcigpIHtcbiAgICAvLyBBdHRhY2ggY29udGVudCBsb2FkZWQgZWxlbWVudCB3aXRoIGNhbGxiYWNrIHRvIGRvY3VtZW50XG4gICAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignRE9NQ29udGVudExvYWRlZCcsIHRoaXMuY29udGVudExvYWRlZC5iaW5kKHRoaXMpKTtcbiAgfVxuXG4gIC8qKlxuICAgKiBDYWxsYmFjayBhZnRlciBjb250ZW50IHdhcyBsb2FkZWRcbiAgICovXG4gIGNvbnRlbnRMb2FkZWQodGhpczogU2ZSZWdpc3Rlcikge1xuICAgIHRoaXMuem9uZSA9IChkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2ZyWm9uZScpIGFzIEhUTUxTZWxlY3RFbGVtZW50KTtcbiAgICB0aGlzLnpvbmVFbXB0eSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzZnJab25lX2VtcHR5Jyk7XG4gICAgdGhpcy56b25lTG9hZGluZyA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdzZnJab25lX2xvYWRpbmcnKTtcblxuICAgIHRoaXMuYmFyR3JhcGggPSAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2JhcmdyYXBoJykgYXMgSFRNTE1ldGVyRWxlbWVudCk7XG4gICAgaWYgKHRoaXMuYmFyR3JhcGgpIHtcbiAgICAgIHRoaXMuYmFyR3JhcGguY2xhc3NMaXN0LmFkZCgnc2hvdycpO1xuICAgICAgdGhpcy5wYXNzd29yZFN0cmVuZ3RoQ2FsY3VsYXRvciA9IG5ldyBQYXNzd29yZFN0cmVuZ3RoQ2FsY3VsYXRvcigpO1xuICAgICAgaWYgKHRoaXMuaXNJbnRlcm5ldEV4cGxvcmVyKCkpIHtcbiAgICAgICAgdGhpcy5sb2FkSW50ZXJuZXRFeHBsb3JlclBvbHlmaWxsKCk7XG4gICAgICB9IGVsc2Uge1xuICAgICAgICB0aGlzLmF0dGFjaFRvRWxlbWVudEJ5SWQoJ3NmcnBhc3N3b3JkJywgJ2tleXVwJywgdGhpcy5jYWxsVGVzdFBhc3N3b3JkLmJpbmQodGhpcykpO1xuICAgICAgfVxuICAgIH1cblxuICAgIHRoaXMuYXR0YWNoVG9FbGVtZW50QnlJZCgnc2ZyQ291bnRyeScsICdjaGFuZ2UnLCB0aGlzLmNvdW50cnlDaGFuZ2VkLmJpbmQodGhpcykpO1xuICAgIHRoaXMuYXR0YWNoVG9FbGVtZW50QnlJZCgnc2ZyQ291bnRyeScsICdrZXl1cCcsIHRoaXMuY291bnRyeUNoYW5nZWQuYmluZCh0aGlzKSk7XG4gICAgdGhpcy5hdHRhY2hUb0VsZW1lbnRCeUlkKCd1cGxvYWRCdXR0b24nLCAnY2hhbmdlJywgdGhpcy51cGxvYWRGaWxlLmJpbmQodGhpcykpO1xuICAgIHRoaXMuYXR0YWNoVG9FbGVtZW50QnlJZCgncmVtb3ZlSW1hZ2VCdXR0b24nLCAnY2xpY2snLCB0aGlzLnJlbW92ZUZpbGUuYmluZCh0aGlzKSk7XG4gIH07XG5cbiAgLyoqXG4gICAqIEFkZCBjbGFzcyBkLWJsb2NrIHJlbW92ZSBjbGFzcyBkLW5vbmVcbiAgICovXG4gIHNob3dFbGVtZW50KGVsZW1lbnQ6IEhUTUxFbGVtZW50KSB7XG4gICAgZWxlbWVudC5jbGFzc0xpc3QucmVtb3ZlKCdkLW5vbmUnKTtcbiAgICBlbGVtZW50LmNsYXNzTGlzdC5hZGQoJ2QtYmxvY2snKTtcbiAgfTtcblxuICAvKipcbiAgICogQWRkIGNsYXNzIGQtbm9uZSByZW1vdmUgY2xhc3MgZC1ibG9ja1xuICAgKi9cbiAgaGlkZUVsZW1lbnQoZWxlbWVudDogSFRNTEVsZW1lbnQpIHtcbiAgICBlbGVtZW50LmNsYXNzTGlzdC5yZW1vdmUoJ2QtYmxvY2snKTtcbiAgICBlbGVtZW50LmNsYXNzTGlzdC5hZGQoJ2Qtbm9uZScpO1xuICB9O1xuXG4gIGF0dGFjaFRvRWxlbWVudEJ5SWQoaWQ6IHN0cmluZywgZXZlbnROYW1lOiBzdHJpbmcsIGNhbGxiYWNrOiBFdmVudExpc3RlbmVyT3JFdmVudExpc3RlbmVyT2JqZWN0KSB7XG4gICAgbGV0IGVsZW1lbnQgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZChpZCk7XG4gICAgdGhpcy5hdHRhY2hUb0VsZW1lbnQoZWxlbWVudCwgZXZlbnROYW1lLCBjYWxsYmFjayk7XG4gIH1cblxuICBhdHRhY2hUb0VsZW1lbnQoZWxlbWVudDogSFRNTEVsZW1lbnQsIGV2ZW50TmFtZTogc3RyaW5nLCBjYWxsYmFjazogRXZlbnRMaXN0ZW5lck9yRXZlbnRMaXN0ZW5lck9iamVjdCkge1xuICAgIGlmIChlbGVtZW50KSB7XG4gICAgICBlbGVtZW50LmFkZEV2ZW50TGlzdGVuZXIoZXZlbnROYW1lLCBjYWxsYmFjayk7XG4gICAgfVxuICB9O1xuXG4gIC8qKlxuICAgKiBHZXRzIHBhc3N3b3JkIG1ldGVyIGVsZW1lbnQgYW5kIHNldHMgdGhlIHZhbHVlIHdpdGhcbiAgICogdGhlIHJlc3VsdCBvZiB0aGUgY2FsY3VsYXRlIHBhc3N3b3JkIHN0cmVuZ3RoIGZ1bmN0aW9uXG4gICAqL1xuICBjYWxsVGVzdFBhc3N3b3JkKHRoaXM6IFNmUmVnaXN0ZXIsIGV2ZW50OiBFdmVudCkge1xuICAgIGxldCBlbGVtZW50ID0gKGV2ZW50LnRhcmdldCBhcyBIVE1MSW5wdXRFbGVtZW50KSxcbiAgICAgIG1ldGVyUmVzdWx0ID0gdGhpcy5wYXNzd29yZFN0cmVuZ3RoQ2FsY3VsYXRvci5jYWxjdWxhdGUoZWxlbWVudC52YWx1ZSk7XG5cbiAgICBpZiAodGhpcy5iYXJHcmFwaC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgPT09ICdtZXRlcicpIHtcbiAgICAgIHRoaXMuYmFyR3JhcGgudmFsdWUgPSBtZXRlclJlc3VsdC5zY29yZTtcbiAgICB9IGVsc2Uge1xuICAgICAgbGV0IGJhckdyYXBoID0gKHRoaXMuYmFyR3JhcGggYXMgdW5rbm93biBhcyBIVE1MSUZyYW1lRWxlbWVudCksXG4gICAgICAgIHBlcmNlbnRTY29yZSA9IE1hdGgubWluKChNYXRoLmZsb29yKG1ldGVyUmVzdWx0LnNjb3JlIC8gMy40KSksIDEwKSxcbiAgICAgICAgYmxpbmRzID0gKFxuICAgICAgICAgIGJhckdyYXBoLmNvbnRlbnREb2N1bWVudCB8fCBiYXJHcmFwaC5jb250ZW50V2luZG93LmRvY3VtZW50XG4gICAgICAgICkuZ2V0RWxlbWVudHNCeUNsYXNzTmFtZSgnYmxpbmQnKTtcblxuICAgICAgZm9yIChsZXQgaW5kZXggPSAwOyBpbmRleCA8IGJsaW5kcy5sZW5ndGg7IGluZGV4KyspIHtcbiAgICAgICAgbGV0IGJsaW5kID0gKGJsaW5kc1tpbmRleF0gYXMgSFRNTEVsZW1lbnQpO1xuICAgICAgICBpZiAoaW5kZXggPCBwZXJjZW50U2NvcmUpIHtcbiAgICAgICAgICB0aGlzLmhpZGVFbGVtZW50KGJsaW5kKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICB0aGlzLnNob3dFbGVtZW50KGJsaW5kKTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfTtcblxuICBpc0ludGVybmV0RXhwbG9yZXIoKTogYm9vbGVhbiB7XG4gICAgbGV0IHVzZXJBZ2VudCA9IG5hdmlnYXRvci51c2VyQWdlbnQ7XG4gICAgLyogTVNJRSB1c2VkIHRvIGRldGVjdCBvbGQgYnJvd3NlcnMgYW5kIFRyaWRlbnQgdXNlZCB0byBuZXdlciBvbmVzKi9cbiAgICByZXR1cm4gdXNlckFnZW50LmluZGV4T2YoJ01TSUUgJykgPiAtMSB8fCB1c2VyQWdlbnQuaW5kZXhPZignVHJpZGVudC8nKSA+IC0xO1xuICB9O1xuXG4gIGxvYWRJbnRlcm5ldEV4cGxvcmVyUG9seWZpbGwodGhpczogU2ZSZWdpc3Rlcikge1xuICAgIGxldCBib2R5ID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoJ2JvZHknKS5pdGVtKDApLFxuICAgICAganMgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdzY3JpcHQnKTtcbiAgICBqcy5zZXRBdHRyaWJ1dGUoJ3R5cGUnLCAndGV4dC9qYXZhc2NyaXB0Jyk7XG4gICAganMuc2V0QXR0cmlidXRlKCdzcmMnLCAnaHR0cHM6Ly91bnBrZy5jb20vbWV0ZXItcG9seWZpbGwvZGlzdC9tZXRlci1wb2x5ZmlsbC5taW4uanMnKTtcbiAgICBqcy5vbmxvYWQgPSAoKSA9PiB7XG4gICAgICAvLyBAdHMtaWdub3JlXG4gICAgICBtZXRlclBvbHlmaWxsKHRoaXMuYmFyR3JhcGgpO1xuICAgICAgdGhpcy5hdHRhY2hUb0VsZW1lbnRCeUlkKCdzZnJwYXNzd29yZCcsICdrZXl1cCcsIHRoaXMuY2FsbFRlc3RQYXNzd29yZCk7XG4gICAgfTtcbiAgICBib2R5LmFwcGVuZENoaWxkKGpzKTtcbiAgfTtcblxuXG4gIC8qKlxuICAgKiBDaGFuZ2UgdmFsdWUgb2Ygem9uZSBzZWxlY3Rib3hcbiAgICovXG4gIGNvdW50cnlDaGFuZ2VkKHRoaXM6IFNmUmVnaXN0ZXIsIGV2ZW50OiBLZXlib2FyZEV2ZW50KSB7XG4gICAgaWYgKFxuICAgICAgKFxuICAgICAgICBldmVudC50eXBlID09PSAnY2hhbmdlJ1xuICAgICAgICB8fCAoZXZlbnQudHlwZSA9PT0gJ2tleXVwJyAmJiAoZXZlbnQua2V5Q29kZSA9PT0gNDAgfHwgZXZlbnQua2V5Q29kZSA9PT0gMzgpKVxuICAgICAgKVxuICAgICAgJiYgdGhpcy5sb2FkaW5nICE9PSB0cnVlXG4gICAgKSB7XG4gICAgICBpZiAodGhpcy56b25lKSB7XG4gICAgICAgIGxldCB0YXJnZXQgPSAoKGV2ZW50LnRhcmdldCB8fCBldmVudC5zcmNFbGVtZW50KSBhcyBIVE1MU2VsZWN0RWxlbWVudCksXG4gICAgICAgICAgY291bnRyeVNlbGVjdGVkVmFsdWUgPSB0YXJnZXQub3B0aW9uc1t0YXJnZXQuc2VsZWN0ZWRJbmRleF0udmFsdWU7XG5cbiAgICAgICAgdGhpcy5sb2FkaW5nID0gdHJ1ZTtcblxuICAgICAgICB0aGlzLnpvbmUuZGlzYWJsZWQgPSB0cnVlO1xuICAgICAgICB0aGlzLmhpZGVFbGVtZW50KHRoaXMuem9uZUVtcHR5KTtcbiAgICAgICAgdGhpcy5zaG93RWxlbWVudCh0aGlzLnpvbmVMb2FkaW5nKTtcblxuICAgICAgICB0aGlzLmFqYXhSZXF1ZXN0ID0gbmV3IFhNTEh0dHBSZXF1ZXN0KCk7XG4gICAgICAgIHRoaXMuYWpheFJlcXVlc3Qub25sb2FkID0gdGhpcy54aHJSZWFkeU9uTG9hZC5iaW5kKHRoaXMpO1xuICAgICAgICB0aGlzLmFqYXhSZXF1ZXN0Lm9wZW4oJ1BPU1QnLCAnaW5kZXgucGhwP2FqYXg9c2ZfcmVnaXN0ZXInKTtcbiAgICAgICAgdGhpcy5hamF4UmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKCdDb250ZW50LVR5cGUnLCAnYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkOyBjaGFyc2V0PVVURi04Jyk7XG4gICAgICAgIHRoaXMuYWpheFJlcXVlc3Quc2VuZCgndHhfc2ZyZWdpc3RlclthY3Rpb25dPXpvbmVzJnR4X3NmcmVnaXN0ZXJbcGFyZW50XT0nICsgY291bnRyeVNlbGVjdGVkVmFsdWUpO1xuICAgICAgfVxuICAgIH1cbiAgfTtcblxuICAvKipcbiAgICogUHJvY2VzcyBhamF4IHJlc3BvbnNlIGFuZCBkaXNwbGF5IGVycm9yIG1lc3NhZ2Ugb3JcbiAgICogaGFuZCBkYXRhIHJlY2VpdmVkIHRvIGFkZCB6b25lIG9wdGlvbiBmdW5jdGlvblxuICAgKi9cbiAgeGhyUmVhZHlPbkxvYWQodGhpczogU2ZSZWdpc3Rlciwgc3RhdGVDaGFuZ2VkOiBQcm9ncmVzc0V2ZW50KTogYW55IHtcbiAgICBsZXQgeGhyUmVzcG9uc2UgPSAoc3RhdGVDaGFuZ2VkLnRhcmdldCBhcyBYTUxIdHRwUmVxdWVzdCk7XG5cbiAgICBpZiAoeGhyUmVzcG9uc2UucmVhZHlTdGF0ZSA9PT0gNCAmJiB4aHJSZXNwb25zZS5zdGF0dXMgPT09IDIwMCkge1xuICAgICAgbGV0IHhoclJlc3BvbnNlRGF0YSA9IEpTT04ucGFyc2UoeGhyUmVzcG9uc2UucmVzcG9uc2VUZXh0KTtcbiAgICAgIHRoaXMuaGlkZUVsZW1lbnQodGhpcy56b25lTG9hZGluZyk7XG5cbiAgICAgIGlmICh4aHJSZXNwb25zZURhdGEuc3RhdHVzID09PSAnZXJyb3InIHx8IHhoclJlc3BvbnNlRGF0YS5kYXRhLmxlbmd0aCA9PT0gMCkge1xuICAgICAgICB0aGlzLnNob3dFbGVtZW50KHRoaXMuem9uZUVtcHR5KTtcbiAgICAgIH0gZWxzZSB7XG4gICAgICAgIHRoaXMuYWRkWm9uZU9wdGlvbnMoeGhyUmVzcG9uc2VEYXRhLmRhdGEpO1xuICAgICAgfVxuICAgIH1cblxuICAgIHRoaXMubG9hZGluZyA9IGZhbHNlO1xuICB9O1xuXG4gIC8qKlxuICAgKiBQcm9jZXNzIGRhdGEgcmVjZWl2ZWQgd2l0aCB4aHIgcmVzcG9uc2VcbiAgICovXG4gIGFkZFpvbmVPcHRpb25zKHRoaXM6IFNmUmVnaXN0ZXIsIG9wdGlvbnM6IEFycmF5PE9iamVjdD4pIHtcbiAgICB3aGlsZSAodGhpcy56b25lLmxlbmd0aCkge1xuICAgICAgdGhpcy56b25lLnJlbW92ZUNoaWxkKHRoaXMuem9uZVswXSk7XG4gICAgfVxuXG4gICAgb3B0aW9ucy5mb3JFYWNoKChvcHRpb246IFNlbGVjdE9wdGlvbiwgaW5kZXg6IG51bWJlcikgPT4ge1xuICAgICAgdGhpcy56b25lLm9wdGlvbnNbaW5kZXhdID0gbmV3IE9wdGlvbihvcHRpb24ubGFiZWwsIG9wdGlvbi52YWx1ZSk7XG4gICAgfSk7XG5cbiAgICB0aGlzLnpvbmUuZGlzYWJsZWQgPSBmYWxzZTtcbiAgfTtcblxuXG4gIC8qKlxuICAgKiBBZGRzIGEgcHJldmlldyBpbmZvcm1hdGlvbiBhYm91dCBmaWxlIHRvIHVwbG9hZCBpbiBhIGxhYmVsXG4gICAqL1xuICB1cGxvYWRGaWxlKHRoaXM6IEhUTUxJbnB1dEVsZW1lbnQpIHtcbiAgICBsZXQgaW5mb3JtYXRpb24gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgndXBsb2FkRmlsZScpO1xuICAgIGlmIChpbmZvcm1hdGlvbikge1xuICAgICAgKGluZm9ybWF0aW9uIGFzIEhUTUxJbnB1dEVsZW1lbnQpLnZhbHVlID0gdGhpcy52YWx1ZTtcbiAgICB9XG4gIH07XG5cbiAgLyoqXG4gICAqIEhhbmRsZSByZW1vdmUgaW1hZ2UgYnV0dG9uIGNsaWNrZWRcbiAgICovXG4gIHJlbW92ZUZpbGUoKSB7XG4gICAgbGV0IHJlbW92ZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdyZW1vdmVJbWFnZScpO1xuICAgIGlmIChyZW1vdmUpIHtcbiAgICAgIChyZW1vdmUgYXMgSFRNTElucHV0RWxlbWVudCkudmFsdWUgPSAnMSc7XG4gICAgfVxuICAgIHRoaXMuc3VibWl0Rm9ybSgpO1xuICB9O1xuXG4gIC8qKlxuICAgKiBTZWxlY3RzIHRoZSBmb3JtIGFuZCB0cmlnZ2VycyBzdWJtaXRcbiAgICovXG4gIHN1Ym1pdEZvcm0oKSB7XG4gICAgbGV0IGZvcm0gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnc2ZyRm9ybScpO1xuICAgIGlmIChmb3JtKSB7XG4gICAgICAoZm9ybSBhcyBIVE1MRm9ybUVsZW1lbnQpLnN1Ym1pdCgpO1xuICAgIH1cbiAgfTtcbn1cbiIsImltcG9ydCBTZlJlZ2lzdGVyIGZyb20gJy4vU2ZSZWdpc3Rlcic7XG5cbmxldCBzZlJlZ2lzdGVyID0gbmV3IFNmUmVnaXN0ZXIoKTtcbi8qKlxuICogR2xvYmFsIGZ1bmN0aW9uIG5lZWRlZCBmb3IgaW52aXNpYmxlIHJlY2FwdGNoYVxuICovXG53aW5kb3cuc2ZSZWdpc3Rlcl9zdWJtaXRGb3JtID0gKCkgPT4ge1xuICByZXR1cm4gbmV3IFByb21pc2UoZnVuY3Rpb24ocmVzb2x2ZTogRnVuY3Rpb24sIHJlamVjdDogRnVuY3Rpb24pIHtcbiAgICBpZiAoZ3JlY2FwdGNoYSA9PT0gdW5kZWZpbmVkKSB7XG4gICAgICBhbGVydCgnUmVjYXB0Y2hhIGlzdCBuaWNodCBkZWZpbmllcnQnKTtcbiAgICAgIHJlamVjdCgpO1xuICAgIH1cblxuICAgIGxldCBjYXB0Y2hhRmllbGQgPSAoZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2NhcHRjaGEnKSBhcyBIVE1MRm9ybUVsZW1lbnQpO1xuICAgIGNhcHRjaGFGaWVsZC52YWx1ZSA9IGdyZWNhcHRjaGEuZ2V0UmVzcG9uc2UoKTtcbiAgICBzZlJlZ2lzdGVyLnN1Ym1pdEZvcm0oKTtcbiAgICByZXNvbHZlKCk7XG4gIH0pO1xufTtcbiJdfQ==
