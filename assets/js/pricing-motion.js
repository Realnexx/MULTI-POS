document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-pricing-motion]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!root || reduced.matches || !window.gsap) return;
    const targets = root.querySelectorAll('.page-intro > *, .pricing-card');
    const context = gsap.context(() => {
        const timeline = gsap.timeline({ defaults:{ ease:'power2.out', clearProps:'transform,opacity' } });
        timeline.from(root.querySelectorAll('.page-intro > *'), {
            y:20, opacity:0, duration:.4, stagger:.06,
        }, 0);
        timeline.from(root.querySelectorAll('.pricing-card'), {
            y:52, opacity:0, scale:.97, duration:.65, stagger:.13,
        }, .22);
    }, root);
    reduced.addEventListener('change', event => { if (event.matches) context.revert(); });
    root.addEventListener('focusin', () => {
        gsap.killTweensOf(targets);
        gsap.set(targets, { clearProps:'transform,opacity' });
    }, { once:true });
});
