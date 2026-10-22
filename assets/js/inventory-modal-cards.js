/* Label existing cells after AJAX redraws without cloning controls or changing row data. */
(() => {
    function labelTable(table) {
        const header = table.tHead?.rows[table.tHead.rows.length - 1];
        if (!header) return;
        const labels = Array.from(header.cells, cell => cell.textContent.trim().replace(/\s+/g, ' '));
        table.classList.add('inventory-modal-cards');
        for (const section of [...table.tBodies, ...(table.tFoot ? [table.tFoot] : [])]) {
            for (const row of section.rows) {
                let column = 0;
                for (const cell of row.cells) {
                    const spanning = cell.colSpan > 1;
                    cell.dataset.cardLabel = spanning ? '' : labels[column] || '';
                    cell.classList.toggle('modal-card-wide', spanning);
                    cell.classList.toggle('modal-card-blank', !cell.textContent.trim() && !cell.querySelector('input,select,button,textarea'));
                    column += cell.colSpan;
                }
            }
        }
    }
    function init() {
        if (!document.getElementById('inventory-directory')) return;
        document.querySelectorAll('.modal').forEach(modal => {
            let scheduled = false;
            const refresh = () => {
                if (scheduled) return;
                scheduled = true;
                requestAnimationFrame(() => {
                    scheduled = false;
                    modal.querySelectorAll('table').forEach(labelTable);
                });
            };
            new MutationObserver(refresh).observe(modal, { childList: true, subtree: true, characterData: true });
            refresh();
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
