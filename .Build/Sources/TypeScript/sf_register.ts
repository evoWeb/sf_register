import SfRegister from './SfRegister';

let sfRegister = new SfRegister();
/**
 * Global function needed for invisible recaptcha
 */
window.sfRegister_submitForm = () => {
  sfRegister.submitForm();
};
