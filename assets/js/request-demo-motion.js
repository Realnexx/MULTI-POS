document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-demo-motion]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!root || reduced.matches || !window.gsap) return;
    // Keep validation feedback and previously submitted forms immediately visible.
    if (root.querySelector('.signin-feedback, .demo-request-success')) return;
    const panels = root.querySelectorAll('.contact-layout > *');
    const heading = root.querySelectorAll('.page-intro > *');
    const context = gsap.context(() => {
        const timeline = gsap.timeline({ defaults:{ ease:'power2.out', clearProps:'transform,opacity' } });
        timeline.from(heading, { y:20, opacity:0, duration:.4, stagger:.06 }, 0);
        timeline.from(panels, { y:40, opacity:0, duration:.65, stagger:.13 }, .22);
    }, root);
    const clear = () => context.revert();
    reduced.addEventListener('change', event => { if (event.matches) clear(); });
    root.addEventListener('focusin', clear, { once:true });
});
