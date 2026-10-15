(function () {
    'use strict';

    // Native validation runs before submit, including external form buttons.
    let validationToastPending = false;
    document.addEventListener('invalid', function (event) {
        const field = event.target;
        if (!field.form || !field.form.closest('.modal')) return;
        event.preventDefault();
        if (validationToastPending) return;
        validationToastPending = true;
        const label = field.labels && field.labels[0];
        showToast((label ? label.textContent.trim() + ': ' : '') + field.validationMessage, 'error');
        if (field.getClientRects().length) field.focus();
        setTimeout(function () { validationToastPending = false; }, 0);
    }, true);

    // Existing AJAX handlers preventDefault; only take over ordinary POST forms.
    window.addEventListener('submit', async function (event) {
        const form = event.target;
        const modal = form.closest('.modal');
        if (!modal || event.defaultPrevented || form.method.toLowerCase() !== 'post') return;
        event.preventDefault();
        if (form.dataset.saving === '1') return;
        form.dataset.saving = '1';
        const buttons = Array.from(modal.querySelectorAll('button[type="submit"], input[type="submit"]'));
        const states = buttons.map(button => button.disabled);
        const data = new FormData(form);
        if (event.submitter && event.submitter.name) data.append(event.submitter.name, event.submitter.value);
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) data.set('csrf_token', token.content);
        buttons.forEach(button => { button.disabled = true; });
        try {
            const response = await fetch(form.action, {
                method: 'POST', body: data, credentials: 'same-origin',
                headers: { 'X-Modal-Form': '1', 'Accept': 'application/json' }
            });
            const text = await response.text();
            let result;
            try { result = JSON.parse(text); } catch (_) {
                throw new Error(text && text.length < 500 && !text.includes('<')
                    ? text : 'Unable to save. Please check your connection or refresh and try again.');
            }
            if (!response.ok || result.success !== true) throw new Error(result.message || 'Unable to save. Please check the entered values.');
            window.location.assign(result.redirect || window.location.href);
        } catch (error) {
            showToast(error.message || 'Unable to save. Please try again.', 'error');
        } finally {
            delete form.dataset.saving;
            buttons.forEach((button, index) => { button.disabled = states[index]; });
        }
    });

    // Keep validation feedback consistent for older AJAX forms using SweetAlert.
    if (window.Swal) {
        const originalFire = Swal.fire.bind(Swal);
        Swal.fire = function (options) {
            if (options && typeof options === 'object' && options.icon === 'error' && document.querySelector('.modal.show')) {
                showToast(options.text || options.title || 'Unable to save.', 'error');
                return Promise.resolve({ isConfirmed: false, isDismissed: true });
            }
            return originalFire.apply(null, arguments);
        };
    }
})();
