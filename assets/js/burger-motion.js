(() => {
  'use strict';
  const preference = window.matchMedia('(prefers-reduced-motion: reduce)');
  let initialized = false;
  function initializeMotion() {
    if (initialized || preference.matches || !window.AOS || !document.querySelector('.burger-home')) return;
    const reveal = (element, delay = 0) => {
      if (element.hasAttribute('data-aos')) return;
      element.setAttribute('data-aos', 'fade-up');
      element.setAttribute('data-aos-delay', String(delay));
    };
    const staggerChildren = (parent, selector = ':scope > *') => {
      Array.from(parent.querySelectorAll(selector)).forEach((element, index) => {
        if (!element.matches('[aria-hidden="true"], .story-star, script, form, .signin-feedback')) reveal(element, Math.min(index * 65, 200));
      });
    };
    document.querySelectorAll('.hero-copy, .page-intro, .story-copy, .about-copy').forEach(parent => staggerChildren(parent));
    document.querySelectorAll('.hero-visual, .section-heading, .story-photo, .editorial-photo, .detail-photo, .contact-details, .contact-aside > img, .public-callout, .menu-empty, .last-call .eyebrow, .last-call h2, .last-call .button, .footer-top, .footer-bottom, .detail-copy > .eyebrow, .detail-copy > h1, .detail-copy > .body-copy, .detail-price, .cart-summary > h2, .signin-art, .signin-panel > .eyebrow, .signin-panel > h1, .signin-panel > .signin-description, .auth-recovery > .eyebrow, .auth-recovery > h1').forEach(element => reveal(element));
    // Animate wrappers so card hover transforms and sticky panels stay independent.
    document.querySelectorAll('.product-grid, .branch-grid, .location-grid, .cart-items, .order-list').forEach(grid => {
      const columns = Math.max(1, getComputedStyle(grid).gridTemplateColumns.split(' ').length);
      Array.from(grid.children).forEach((card, index) => {
        if (!card.matches('.product-card, .branch-card, .location-card, .cart-item, .order-list-card')) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'motion-reveal';
        card.before(wrapper);
        wrapper.appendChild(card);
        reveal(wrapper, Math.min(index % columns * 75, 150));
      });
    });
    try {
      document.body.classList.add('motion-ready');
      window.AOS.init({ duration: 650, easing: 'ease-out-cubic', offset: 35, delay: 0, once: true, mirror: false, anchorPlacement: 'top-bottom' });
      initialized = true;
    } catch (error) {
      document.body.classList.remove('motion-ready');
      return;
    }
    let refreshFrame;
    const refresh = () => {
      if (refreshFrame) cancelAnimationFrame(refreshFrame);
      refreshFrame = requestAnimationFrame(() => { window.AOS.refresh(); refreshFrame = null; });
    };
    document.addEventListener('load', event => { if (event.target instanceof HTMLImageElement) refresh(); }, true);
    window.addEventListener('load', refresh, { once: true });
    window.addEventListener('pageshow', refresh);
    if (document.fonts) document.fonts.ready.then(refresh);
    // Keyboard navigation should reveal a focused control immediately.
    document.addEventListener('focusin', event => {
      const target = event.target.closest('[data-aos]');
      if (target) target.classList.add('aos-animate', 'motion-focused');
    });
    document.querySelectorAll('.signin-feedback button').forEach(button => button.addEventListener('click', refresh));
    refresh();
  }
  preference.addEventListener('change', initializeMotion);
  initializeMotion();
})();
