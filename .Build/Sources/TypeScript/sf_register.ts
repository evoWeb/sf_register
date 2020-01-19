import SfRegister from './SfRegister';

let sfRegister = new SfRegister();
/**
 * Global function needed for invisible recaptcha
 */
window.sfRegister_submitForm = () => {
  return new Promise(function(resolve: Function, reject: Function) {
    if (grecaptcha === undefined) {
      alert('Recaptcha ist nicht definiert');
      reject();
    }

    let captchaField = (document.getElementById('captcha') as HTMLFormElement);
    captchaField.value = grecaptcha.getResponse();
    sfRegister.submitForm();
    resolve();
  });
};
