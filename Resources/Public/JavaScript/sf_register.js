class PasswordStrengthCalculator {
    /**
     * password length:
     *   level 0 (0 point): less than 4 characters
     *   level 1 (6 points): between 5 and 7 characters
     *   level 2 (12 points): between 8 and 15 characters
     *   level 3 (18 points): 16 or more characters
     */
    verdictLength(password) {
        const length = password.length;
        let score, log;
        switch (true) {
            case length > 0 && length < 5:
                log = `3 points for length (${length})`;
                score = 3;
                break;
            case length > 4 && length < 8:
                log = `6 points for length (${length})`;
                score = 6;
                break;
            case length > 7 && length < 16:
                log = `12 points for length (${length})`;
                score = 12;
                break;
            default:
                log = `18 points for length (${length})`;
                score = 18;
                break;
        }
        return { score: score, log: log };
    }
    /**
     * letters:
     *   level 0 (0 points): no letters
     *   level 1 (5 points): all letters are lower case
     *   level 1 (5 points): all letters are upper case
     *   level 2 (7 points): letters are mixed case
     */
    verdictLetter(password) {
        const matchLower = password.match(/[a-z]/), matchUpper = password.match(/[A-Z]/);
        let score = 0, log = '';
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
    }
    /**
     * numbers:
     *   level 0 (0 points): no numbers exist
     *   level 1 (5 points): one number exists
     *   level 1 (7 points): 3 or more numbers exists
     */
    verdictNumbers(password) {
        const numbers = password.replace(/\D/gi, '');
        let score = 0, log = '';
        if (numbers.length > 1) {
            score = 7;
            log = '7 points for at least three numbers';
        }
        else if (numbers.length > 0) {
            score = 5;
            log = '5 points for at least one number';
        }
        return { score: score, log: log };
    }
    /**
     * special characters:
     *   level 0 (0 points): no special characters
     *   level 1 (5 points): one special character exists
     *   level 2 (10 points): more than one special character exists
     */
    verdictSpecialChars(password) {
        const specialCharacters = password.replace(/[\w\s]/gi, '');
        let score = 0, log = '';
        if (specialCharacters.length > 1) {
            score = 10;
            log = '10 points for at least two special chars';
        }
        else if (specialCharacters.length > 0) {
            score = 5;
            log = '5 points for at least one special char';
        }
        return { score: score, log: log };
    }
    /**
     * combinations:
     *   level 0 (1 points): mixed case letters
     *   level 0 (1 points): letters and numbers
     *   level 1 (2 points): mixed case letters and numbers
     *   level 3 (4 points): letters, numbers and special characters
     *   level 4 (6 points): mixed case letters, numbers and special characters
     */
    verdictCombos(letter, number, special) {
        let score = 0, log = '';
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
    }
    /**
     * final verdict base on final score
     */
    finalVerdict(finalScore) {
        let strVerdict;
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
    }
    calculate(password) {
        const lengthVerdict = this.verdictLength(password), letterVerdict = this.verdictLetter(password), numberVerdict = this.verdictNumbers(password), specialVerdict = this.verdictSpecialChars(password), combosVerdict = this.verdictCombos(letterVerdict.score, numberVerdict.score, specialVerdict.score);
        const score = lengthVerdict.score
            + letterVerdict.score
            + numberVerdict.score
            + specialVerdict.score
            + combosVerdict.score, log = [
            lengthVerdict.log,
            letterVerdict.log,
            numberVerdict.log,
            specialVerdict.log,
            combosVerdict.log,
            score + ' points final score'
        ].join('\n');
        return { score: score, log: log, verdict: this.finalVerdict(score) };
    }
}

const document = window.document;
class SfRegister {
    constructor() {
        this.loading = false;
        this.ajaxEndpoint = '/index.php?ajax=sf_register';
        this.ajaxRequest = null;
        this.passwordStrengthCalculator = null;
        this.form = null;
        this.barGraph = null;
        this.zone = null;
        this.zoneEmpty = null;
        this.zoneLoading = null;
        this.fileInformation = null;
        this.removeImage = null;
        if (document.readyState === 'loading') {
            // Attach content loaded element with callback to document
            document.addEventListener('DOMContentLoaded', () => this.initialize());
        }
        else {
            this.initialize();
        }
    }
    /**
     * Callback after content was loaded
     */
    initialize() {
        this.initializeElements();
        this.initializePasswordStrengthCalculator();
        this.initializeEvents();
    }
    initializeElements() {
        this.form = document.getElementById('sfrForm');
        this.zone = document.getElementById('sfrZone');
        this.zoneEmpty = document.getElementById('sfrZone_empty');
        this.zoneLoading = document.getElementById('sfrZone_loading');
        this.barGraph = document.getElementById('bargraph');
        this.fileInformation = document.getElementById('uploadFile');
        this.removeImage = document.getElementById('removeImage');
    }
    initializePasswordStrengthCalculator() {
        if (this.barGraph !== null) {
            this.barGraph.classList.add('show');
            this.passwordStrengthCalculator = new PasswordStrengthCalculator();
        }
    }
    initializeEvents() {
        this.attachToElementById('sfrCountry', 'change', (event) => this.countryChanged(event));
        this.attachToElementById('sfrCountry', 'keyup', (event) => this.countryChanged(event));
        this.attachToElementById('uploadButton', 'change', (event) => this.uploadFile(event));
        this.attachToElementById('removeImageButton', 'click', () => this.removeFile());
        this.attachToElementById('sfrPassword', 'keyup', (event) => this.checkPasswordOnChange(event));
    }
    /**
     * Add class d-block remove class d-none
     */
    showElement(element) {
        element.classList.remove('d-none');
        element.classList.add('d-block');
    }
    /**
     * Add class d-none remove class d-block
     */
    hideElement(element) {
        element.classList.remove('d-block');
        element.classList.add('d-none');
    }
    attachToElementById(id, eventName, callback) {
        const element = document.getElementById(id);
        if (element !== null) {
            this.attachToElement(element, eventName, callback);
        }
    }
    attachToElement(element, eventName, callback) {
        if (element) {
            element.addEventListener(eventName, callback);
        }
    }
    /**
     * Gets password meter element and sets the value with
     * the result of the calculate password strength function
     */
    checkPasswordOnChange(event) {
        const element = event.target, meterResult = this.passwordStrengthCalculator.calculate(element.value);
        this.barGraph.value = meterResult.score;
    }
    loadCountryZonesByCountry(countrySelectedValue) {
        if (this.zone !== null) {
            this.loading = true;
            this.zone.disabled = true;
            this.hideElement(this.zoneEmpty);
            this.showElement(this.zoneLoading);
            this.ajaxRequest = new XMLHttpRequest();
            this.ajaxRequest.onload = (event) => this.xhrReadyOnLoad(event);
            this.ajaxRequest.open('POST', this.ajaxEndpoint);
            this.ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            this.ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
        }
    }
    /**
     * Change value of zone select box
     */
    countryChanged(event) {
        if (this.loading !== true
            && ((event instanceof KeyboardEvent && (event.key === 'ArrowDown' || event.key === 'ArrowUp'))
                || event.type === 'change')) {
            if (this.zone) {
                const target = event.target, countrySelectedValue = target.options[target.selectedIndex].value;
                this.loadCountryZonesByCountry(countrySelectedValue);
            }
        }
    }
    /**
     * Process ajax response and display error message or
     * hand data received to add zone option function
     */
    xhrReadyOnLoad(stateChanged) {
        const xhrResponse = stateChanged.target;
        if (xhrResponse.readyState === 4 && xhrResponse.status === 200) {
            const xhrResponseData = JSON.parse(xhrResponse.responseText);
            this.hideElement(this.zoneLoading);
            if (xhrResponseData.status === 'error' || xhrResponseData.data.length === 0) {
                this.showElement(this.zoneEmpty);
            }
            else {
                this.addZoneOptions(xhrResponseData.data);
            }
        }
        this.loading = false;
    }
    /**
     * Process data received with xhr response
     */
    addZoneOptions(options) {
        while (this.zone.length) {
            this.zone.removeChild(this.zone[0]);
        }
        options.forEach((option, index) => {
            this.zone.options[index] = new Option(option.label, option.value);
        });
        this.zone.disabled = false;
    }
    /**
     * Adds a preview information about file to upload in a label
     */
    uploadFile(event) {
        const upload = event.target;
        if (this.fileInformation !== null) {
            this.fileInformation.value = upload.value;
        }
    }
    /**
     * Handle remove image button clicked
     */
    removeFile() {
        if (this.removeImage !== null) {
            this.removeImage.value = '1';
            this.form.submit();
        }
    }
}

new SfRegister();
//# sourceMappingURL=sf_register.js.map
