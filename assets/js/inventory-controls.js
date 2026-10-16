document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('inventoryControlModal');
    if (!modal || !window.bootstrap) return;
    const params = new URLSearchParams(window.location.search);
    const allowed = ['inventory_count', 'inventory_loss', 'inventory_reorder', 'inventory_history'];
    const requested = params.get('control_tab');
    if (allowed.includes(requested)) {
        bootstrap.Tab.getOrCreateInstance(document.getElementById(requested + '-tab')).show();
    }
    modal.addEventListener('shown.bs.tab', function (event) {
        const field = modal.querySelector('input[name="control_tab"]');
        if (field) field.value = event.target.id.replace(/-tab$/, '');
    });
    const branchTab = modal.querySelector('input[name="control_tab"]');
    if (branchTab && allowed.includes(requested)) branchTab.value = requested;
    if (params.has('controls')) bootstrap.Modal.getOrCreateInstance(modal).show();
});
