document.addEventListener('DOMContentLoaded', () => {
    const cards = Array.from(document.querySelectorAll('.do-summaries .do-metric, .kpi-card, .rpt-kpi-card, .his-kpi-card, .cat-kpi-card'));
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (reduced.matches) return;
    if (window.AOS) {
        cards.forEach((card, index) => {
            card.setAttribute('data-aos', 'fade-up');
            card.setAttribute('data-aos-delay', String((index % 4) * 65));
        });
        AOS.init({ duration: 450, easing: 'ease-out-cubic', once: true, offset: 24, disable: () => reduced.matches });
    } else if ('IntersectionObserver' in window) {
        // Keep cards visible if the animation library cannot load.
        const observer = new IntersectionObserver(entries => entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            if (!reduced.matches && entry.target.animate) entry.target.animate(
                [{ opacity: .4, transform: 'translateY(12px)' }, { opacity: 1, transform: 'translateY(0)' }],
                { duration: 350, easing: 'ease-out' }
            );
            observer.unobserve(entry.target);
        }), { threshold: .15 });
        cards.forEach(card => observer.observe(card));
    }
    reduced.addEventListener?.('change', event => {
        if (event.matches) cards.forEach(card => { card.removeAttribute('data-aos'); card.getAnimations?.().forEach(animation => animation.cancel()); });
    });
});
