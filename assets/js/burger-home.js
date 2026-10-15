(() => {
  'use strict';
  const menuButton = document.querySelector('.menu-toggle');
  const menu = document.getElementById('mobile-nav');
  if (menuButton && menu) {
    const closeMenu = () => { menu.hidden = true; menuButton.setAttribute('aria-expanded', 'false'); menuButton.setAttribute('aria-label', 'Open navigation'); };
    menuButton.addEventListener('click', () => {
      const expanded = menuButton.getAttribute('aria-expanded') === 'true';
      menu.hidden = expanded;
      menuButton.setAttribute('aria-expanded', String(!expanded));
      menuButton.setAttribute('aria-label', expanded ? 'Open navigation' : 'Close navigation');
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') { closeMenu(); } });
    window.matchMedia('(min-width: 801px)').addEventListener('change', closeMenu);
  }
  const toast = document.getElementById('shop-toast');
  let toastTimer;
  function notify(message, error = false) {
    if (!toast) return;
    clearTimeout(toastTimer);
    toast.querySelector('span').textContent = message;
    toast.dataset.error = String(error);
    toast.hidden = false;
    toastTimer = setTimeout(() => { toast.hidden = true; }, 4500);
  }
  if (toast) toast.querySelector('button').addEventListener('click', () => { clearTimeout(toastTimer); toast.hidden = true; });
  const flash = document.getElementById('shop-flash');
  if (flash) notify(flash.dataset.message);
  document.querySelectorAll('[data-online-checkout]').forEach(form => {
    const button = form.querySelector('button[type="submit"]');
    if (!button) return;
    const label = button.textContent;
    const initiallyDisabled = button.disabled;
    form.addEventListener('submit', () => { button.disabled = true; button.textContent = 'Placing order…'; button.setAttribute('aria-busy', 'true'); });
    window.addEventListener('pageshow', () => { button.disabled = initiallyDisabled; button.textContent = label; button.removeAttribute('aria-busy'); });
  });
  document.querySelectorAll('.detail-order').forEach(form => {
    const input = form.querySelector('[name="qty"]');
    const total = form.querySelector('[data-item-total]');
    if (!input || !total) return;
    const updateTotal = () => {
      if (input.value === '') return;
      const quantity = Math.min(Number(input.max), Math.max(1, Math.floor(Number(input.value) || 1)));
      input.value = String(quantity);
      total.textContent = form.dataset.currency + (quantity * Number(form.dataset.unitPrice)).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };
    input.addEventListener('change', updateTotal);
    input.addEventListener('input', updateTotal);
    form.querySelectorAll('[data-quantity-step]').forEach(button => button.addEventListener('click', () => {
      input.value = String((Number(input.value) || 1) + Number(button.dataset.quantityStep));
      updateTotal();
    }));
  });
  document.querySelectorAll('.quick-add').forEach(form => {
    form.addEventListener('submit', async event => {
      event.preventDefault();
      const button = form.querySelector('button[type="submit"]');
      if (button.disabled) return;
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      const label = button.innerHTML;
      button.textContent = 'Adding…';
      try {
        const data = new FormData(form);
        data.set('ajax', '1');
        const response = await fetch(form.action, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        if (!response.ok) throw new Error('We couldn’t add this item. Please try again.');
        const result = await response.json();
        if (result.status !== 'success') throw new Error(result.message || 'This item is currently unavailable.');
        const count = Math.max(0, Number(result.cart_count) || 0);
        document.querySelectorAll('[data-cart-count]').forEach(badge => { badge.textContent = String(count); });
        const floatingCart = document.getElementById('floating-cart');
        if (floatingCart) {
          floatingCart.hidden = count === 0;
          floatingCart.setAttribute('aria-label', `View cart, ${count} ${count === 1 ? 'item' : 'items'}`);
        }
        notify('Added to your cart. Good choice!');
      } catch (error) {
        notify(error instanceof Error ? error.message : 'Please try again.', true);
      } finally {
        button.innerHTML = label;
        button.disabled = false;
        button.removeAttribute('aria-busy');
      }
    });
  });
})();
