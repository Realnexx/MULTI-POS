document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-home-motion]');
    if (!root) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (reduced.matches) return;
    const targets = root.querySelectorAll('.hero-copy > *, .platform-label, .platform-title, .platform-grid article, .platform-footnote');
    if (!window.gsap) return; // Content remains visible without the optional runtime.
    const context = gsap.context(() => {
        gsap.from(targets, {
            y: 44, opacity: 0, duration: .95, stagger: .11,
            ease: 'power3.out', clearProps: 'transform,opacity',
        });
        const panel = root.querySelector('.platform-preview');
        if (panel) gsap.from(panel, { x: 46, scale: .96, duration: 1.15, ease: 'power3.out', clearProps: 'transform' });
        const star = root.querySelector('.platform-star');
        if (star) gsap.from(star, { rotation: -100, scale: .45, duration: 1.4, ease: 'power2.out', clearProps: 'transform' });
    }, root);
    reduced.addEventListener('change', event => { if (event.matches) context.revert(); });
    root.addEventListener('focusin', () => {
        gsap.killTweensOf(targets);
        gsap.set(targets, { clearProps: 'transform,opacity' });
    }, { once: true });
});
