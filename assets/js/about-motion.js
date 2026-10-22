document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-about-motion]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!root || reduced.matches || !window.gsap) return;
    const targets = root.querySelectorAll('.page-intro > *');
    const context = gsap.context(() => {
        gsap.from(targets, { y:44, opacity:0, duration:.95, stagger:.14,
            ease:'power3.out', clearProps:'transform,opacity' });
    }, root);
    reduced.addEventListener('change', event => { if (event.matches) context.revert(); });
    root.addEventListener('focusin', () => {
        gsap.killTweensOf(targets);
        gsap.set(targets, { clearProps:'transform,opacity' });
    }, { once:true });
});
