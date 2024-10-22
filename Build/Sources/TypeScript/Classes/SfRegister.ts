import PasswordStrengthCalculator from './PasswordStrengthCalculator';

const document = window.document;

export default class SfRegister {
  protected loading: boolean = false;

  protected ajaxEndpoint: string = '/index.php?ajax=sf_register';

  protected ajaxRequest: XMLHttpRequest = null;

  protected passwordStrengthCalculator: PasswordStrengthCalculator = null;

  protected form: HTMLFormElement|null = null;

  protected barGraph: HTMLMeterElement|null = null;

  protected zone: HTMLSelectElement|null = null;

  protected zoneEmpty: HTMLDivElement|null = null;

  protected zoneLoading: HTMLDivElement|null = null;

  protected fileInformation: HTMLInputElement|null = null;

  protected removeImage: HTMLInputElement|null = null;

  constructor() {
    if (document.readyState === 'loading') {
      // Attach content loaded element with callback to document
      document.addEventListener('DOMContentLoaded', () => this.initialize());
    } else {
      this.initialize();
    }
  }

  /**
   * Callback after content was loaded
   */
  initialize(this: SfRegister): void {
    this.initializeElements();
    this.initializePasswordStrengthCalculator();
    this.initializeEvents();
  }

  initializeElements(this: SfRegister): void {
    this.form = document.getElementById('sfrForm') as HTMLFormElement|null;

    this.zone = document.getElementById('sfrZone') as HTMLSelectElement|null;
    this.zoneEmpty = document.getElementById('sfrZone_empty') as HTMLDivElement|null;
    this.zoneLoading = document.getElementById('sfrZone_loading') as HTMLDivElement|null;

    this.barGraph = document.getElementById('bargraph') as HTMLMeterElement|null;

    this.fileInformation = document.getElementById('uploadFile') as HTMLInputElement|null;
    this.removeImage = document.getElementById('removeImage') as HTMLInputElement|null;
  }

  initializePasswordStrengthCalculator(this: SfRegister): void {
    if (this.barGraph !== null) {
      this.barGraph.classList.add('show');
      this.passwordStrengthCalculator = new PasswordStrengthCalculator();
    }
  }

  initializeEvents(this: SfRegister): void {
    this.attachToElementById('sfrCountry', 'change', (event) => this.countryChanged(event));
    this.attachToElementById('sfrCountry', 'keyup', (event: KeyboardEvent) => this.countryChanged(event));
    this.attachToElementById('uploadButton', 'change', (event) => this.uploadFile(event));
    this.attachToElementById('removeImageButton', 'click', () => this.removeFile());
    this.attachToElementById('sfrPassword', 'keyup', (event: KeyboardEvent) => this.checkPasswordOnChange(event));
  }

  /**
   * Add class d-block remove class d-none
   */
  showElement(element: HTMLElement): void {
    element.classList.remove('d-none');
    element.classList.add('d-block');
  }

  /**
   * Add class d-none remove class d-block
   */
  hideElement(element: HTMLElement): void {
    element.classList.remove('d-block');
    element.classList.add('d-none');
  }

  attachToElementById(id: string, eventName: string, callback: EventListenerOrEventListenerObject): void {
    const element = document.getElementById(id) as HTMLElement|null;
    if (element !== null) {
      this.attachToElement(element, eventName, callback);
    }
  }

  attachToElement(element: HTMLElement, eventName: string, callback: EventListenerOrEventListenerObject): void {
    if (element) {
      element.addEventListener(eventName, callback);
    }
  }

  /**
   * Gets password meter element and sets the value with
   * the result of the calculate password strength function
   */
  checkPasswordOnChange(this: SfRegister, event: KeyboardEvent): void {
    const element = event.target as HTMLInputElement,
      meterResult = this.passwordStrengthCalculator.calculate(element.value);
    this.barGraph.value = meterResult.score;
  }

  loadCountryZonesByCountry(countrySelectedValue: string): void {
    if (this.zone !== null) {
      this.loading = true;

      this.zone.disabled = true;
      this.hideElement(this.zoneEmpty);
      this.showElement(this.zoneLoading);

      this.ajaxRequest = new XMLHttpRequest();
      this.ajaxRequest.onload = (event: ProgressEvent) => this.xhrReadyOnLoad(event);
      this.ajaxRequest.open('POST', this.ajaxEndpoint);
      this.ajaxRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
      this.ajaxRequest.send('tx_sfregister[action]=zones&tx_sfregister[parent]=' + countrySelectedValue);
    }
  }

  /**
   * Change value of zone select box
   */
  countryChanged(this: SfRegister, event: KeyboardEvent|Event): void {
    if (
      this.loading !== true
      && (
        (event instanceof KeyboardEvent && (event.key === 'ArrowDown' || event.key === 'ArrowUp'))
        || event.type === 'change'
      )
    ) {
      if (this.zone) {
        const target = event.target as HTMLSelectElement,
          countrySelectedValue = target.options[target.selectedIndex].value;

        this.loadCountryZonesByCountry(countrySelectedValue);
      }
    }
  }

  /**
   * Process ajax response and display error message or
   * hand data received to add zone option function
   */
  xhrReadyOnLoad(this: SfRegister, stateChanged: ProgressEvent): void {
    const xhrResponse = (stateChanged.target as XMLHttpRequest);

    if (xhrResponse.readyState === 4 && xhrResponse.status === 200) {
      const xhrResponseData = JSON.parse(xhrResponse.responseText);
      this.hideElement(this.zoneLoading);

      if (xhrResponseData.status === 'error' || xhrResponseData.data.length === 0) {
        this.showElement(this.zoneEmpty);
      } else {
        this.addZoneOptions(xhrResponseData.data);
      }
    }

    this.loading = false;
  }

  /**
   * Process data received with xhr response
   */
  addZoneOptions(this: SfRegister, options: HTMLOptionElement[]): void {
    while (this.zone.length) {
      this.zone.removeChild(this.zone[0]);
    }

    options.forEach((option: HTMLOptionElement, index: number) => {
      this.zone.options[index] = new Option(option.label, option.value);
    });

    this.zone.disabled = false;
  }

  /**
   * Adds a preview information about file to upload in a label
   */
  uploadFile(this: SfRegister, event: Event): void {
    const upload = event.target as HTMLInputElement;
    if (this.fileInformation !== null) {
      this.fileInformation.value = upload.value;
    }
  }

  /**
   * Handle remove image button clicked
   */
  removeFile(this: SfRegister): void {
    if (this.removeImage !== null) {
      this.removeImage.value = '1';
      this.form.submit();
    }
  }
}
