(() => {
  'use strict';
  document.querySelectorAll('[data-password-toggle]').forEach(button => button.addEventListener('click', () => {
    const input=document.getElementById(button.dataset.passwordToggle);
    if(!input)return;
    const visible=input.type==='password';input.type=visible?'text':'password';button.textContent=visible?'Hide':'Show';button.setAttribute('aria-pressed',String(visible));button.setAttribute('aria-label',visible?'Hide password':'Show password');
  }));
  document.querySelectorAll('.signin-form').forEach(form => {
    const password=form.querySelector('[name=password]'), confirmation=form.querySelector('[name=confirm_password]');
    const validate=()=>{
      if(password)password.setCustomValidity(password.value && new TextEncoder().encode(password.value).length>72?'This password is too long. Try a shorter passphrase.':'');
      if(confirmation)confirmation.setCustomValidity(confirmation.value!==password.value?'Your passwords do not match.':'');
    };
    if(password)password.addEventListener('input',validate);if(confirmation)confirmation.addEventListener('input',validate);
    const submit=form.querySelector('button[type=submit]');if(!submit)return;const label=submit.querySelector('.signin-button-label');const original=label?label.textContent:'';
    form.addEventListener('submit',()=>{submit.disabled=true;submit.setAttribute('aria-busy','true');if(label)label.textContent='Please wait…';});
    window.addEventListener('pageshow',()=>{submit.disabled=false;submit.removeAttribute('aria-busy');if(label)label.textContent=original;});
  });
  document.querySelectorAll('.signin-feedback button').forEach(button=>button.addEventListener('click',()=>button.closest('.signin-feedback').remove()));
})();
