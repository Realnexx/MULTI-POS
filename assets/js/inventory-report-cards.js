document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.inventory-reports');
    if (!root) return;
    const form = document.getElementById('reportFilterForm');
    const modal = document.getElementById('reportsFiltersModal');
    const mobile = window.matchMedia('(max-width: 767.98px)');
    function placeFilters() {
        if (!form || !modal) return;
        if (!mobile.matches) bootstrap.Modal.getInstance(modal)?.hide();
        const slot = document.getElementById(mobile.matches ? 'reportsFilterMobileSlot' : 'reportsFilterDesktopSlot');
        if (slot && form.parentElement !== slot) slot.append(form);
    }
    mobile.addEventListener('change', placeFilters);
    placeFilters();
    function labelTables() {
        root.querySelectorAll('.report-table').forEach(table => {
            const labels = Array.from(table.querySelectorAll('thead th'), cell => cell.textContent.trim());
            table.classList.toggle('report-summary-cards', labels.length === 0);
            table.querySelectorAll('tbody tr').forEach(row => {
                const cells = Array.from(row.cells);
                row.classList.toggle('report-empty-card', cells.some(cell => cell.colSpan > 1));
                cells.forEach((cell, index) => {
                    if (labels[index] && cell.colSpan === 1) cell.dataset.label = labels[index];
                });
            });
        });
    }
    let pending = false;
    new MutationObserver(() => {
        if (pending) return;
        pending = true;
        requestAnimationFrame(() => { pending = false; labelTables(); });
    }).observe(root, { childList: true, subtree: true });
    labelTables();
});
