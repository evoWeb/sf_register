/// <reference types="@types/grecaptcha"/>
import SfRegister from './SfRegister';

const sfRegister = new SfRegister();

/**
 * Global function needed for invisible recaptcha
 */
window.sfRegister_submitForm = () => {
  return new Promise((resolve, reject) => {
    if (grecaptcha === undefined) {
      console.log('Recaptcha ist nicht definiert');
      reject('recaptcha not found');
    }

    const captchaField = document.getElementById('captcha') as HTMLFormElement;
    captchaField.value = grecaptcha.getResponse();
    sfRegister.submitForm();
    resolve('recaptcha found');
  });
};
