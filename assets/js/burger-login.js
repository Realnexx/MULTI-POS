(() => {
  'use strict';
  const form = document.getElementById('loginForm');
  const password = document.getElementById('password');
  const toggle = document.getElementById('togglePassword');
  const submit = document.getElementById('submitBtn');
  if (password && toggle) {
    toggle.addEventListener('click', () => {
      const visible = password.type === 'password';
      password.type = visible ? 'text' : 'password';
      toggle.textContent = visible ? 'Hide' : 'Show';
      toggle.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
      toggle.setAttribute('aria-pressed', String(visible));
    });
  }
  if (form && submit) {
    const label = submit.querySelector('.signin-button-label');
    form.addEventListener('submit', () => {
      submit.disabled = true;
      submit.setAttribute('aria-busy', 'true');
      label.textContent = 'Signing in…';
    });
    window.addEventListener('pageshow', () => {
      submit.disabled = false;
      submit.removeAttribute('aria-busy');
      label.textContent = 'Sign in';
    });
  }
  document.querySelectorAll('.signin-feedback button').forEach(button => {
    button.addEventListener('click', () => button.closest('.signin-feedback').remove());
  });
})();
