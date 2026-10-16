$(function () {

    const request = document.getElementById('stock-transfer-request');

    const token = () => Array.from(crypto.getRandomValues(new Uint8Array(16)), b => b.toString(16).padStart(2,'0')).join('');

    if (request) {

        const products = JSON.parse(document.getElementById('stock-transfer-products').textContent);

        const source = document.getElementById('transfer-source-branch'), destination = document.getElementById('transfer-target-branch');

        const rows = document.getElementById('stock-transfer-rows'); let index = 0;

        const branches = JSON.parse(document.getElementById('stock-transfer-branches').textContent);

        const sourceId = document.getElementById('transfer-source-branch-id'), destinationId = document.getElementById('transfer-target-branch-id');

        const branchLabel = b => b.name;

        const normalize = name => name.trim().replace(/\s+/g,' ').toLowerCase();

        const label = p => p.name + ' · ' + p.unit_type;

        const same = (a,b) => normalize(a.name) === normalize(b.name) && a.unit_type === b.unit_type && a.product_type === b.product_type && a.is_ingredient == b.is_ingredient;
        const stock = (p,branch) => Number(p.branch_quantities?.[branch] || 0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) + ' ' + p.unit_type;
        function destinationStock(product) {
            const matches = candidates(destinationId.value,product);
            const local = matches.filter(p => p.branch_id !== null), preferred = local.length ? local : matches;
            return preferred.length === 1 ? stock(preferred[0],destinationId.value) : preferred.length ? 'Multiple matching records' : null;
        }

        function candidates(branch, matching) {

            return products.filter(p => branch && (p.branch_id === null || String(p.branch_id) === branch) && (!matching || same(p,matching)));

        }

        function suggestions(input, list, items, auto) {

            list.replaceChildren();items.forEach(p => {
                const option = new Option(label(p),label(p));
                if (list.hasAttribute('data-source-list')) {
                    const branch = branches.destination.find(b => String(b.id) === destinationId.value);
                    const available = destinationStock(p);
                    option.label = 'Source: ' + stock(p,sourceId.value) + ' · ' + (branch ? available !== null ? '✓ Available at ' + branch.name + ': ' + available : '⚠ Not available at ' + branch.name : 'Choose a destination to check availability');
                } else option.label = 'Available: ' + stock(p,destinationId.value);
                list.append(option);
            });

            if (auto && items.length === 1) input.value = label(items[0]);

            const matching = items.filter(p => label(p) === input.value);
            const local = matching.filter(p => p.branch_id !== null);
            const preferred = local.length ? local : matching;
            const selected = preferred.length === 1 ? preferred[0] : null;

            input.setCustomValidity(input.value && !selected ? preferred.length > 1 ? 'Duplicate product records have the same name and unit. Please resolve the duplicates before transferring.' : 'Choose a product from the suggestions.' : '');

            return selected;

        }

        function refresh() {

            const sourceList = document.getElementById('transfer-source-branches'), targetList = document.getElementById('transfer-target-branches');

            sourceList.replaceChildren();branches.source.forEach(b => sourceList.append(new Option(branchLabel(b),branchLabel(b))));

            const fromBranch = branches.source.find(b => branchLabel(b) === source.value);

            sourceId.value = fromBranch ? fromBranch.id : '';

            source.setCustomValidity(source.value && !fromBranch ? 'Choose a source branch from the suggestions.' : '');

            const availableTargets = branches.destination.filter(b => String(b.id) !== sourceId.value);

            targetList.replaceChildren();availableTargets.forEach(b => targetList.append(new Option(branchLabel(b),branchLabel(b))));

            const toBranch = availableTargets.find(b => branchLabel(b) === destination.value);

            destinationId.value = toBranch ? toBranch.id : '';

            destination.setCustomValidity(destination.value && !toBranch ? 'Choose a different destination branch from the suggestions.' : '');

            rows.querySelectorAll('[data-transfer-row]').forEach(row => {

                const from = row.querySelector('[data-source-item]'), to = row.querySelector('[data-destination-item]');

                const item = suggestions(from,row.querySelector('[data-source-list]'),candidates(sourceId.value),false);

                row.querySelector('[data-source-id]').value = item ? item.id : '';

                const matches = item ? candidates(destinationId.value,item) : [];

                const target = suggestions(to,row.querySelector('[data-target-list]'),matches,true);

                row.querySelector('[data-destination-id]').value = target ? target.id : '';

                to.disabled = !item || !destinationId.value;

                const hint = row.querySelector('[data-match-hint]');

                hint.textContent = !item ? 'Select the source product first.' : !destinationId.value ? 'Choose a destination branch.' : matches.length ? '✓ Available at ' + destination.value + ': ' + destinationStock(item) : '⚠ Not available at ' + destination.value + '. Create the same product there first.';
                hint.className = 'small mt-1 ' + (item && destinationId.value ? matches.length ? 'text-success' : 'transfer-notice-text' : 'text-muted');

                const quantity = row.querySelector('input[type=number]');

                quantity.step = item && ['g','kg','ml','l'].includes(item.unit_type) ? '0.0001' : '1';

                quantity.min = quantity.step === '1' ? '1' : '0.0001';

            });

        }

        function add() {

            const i = index++, row = document.createElement('div'); row.className = 'transfer-item-card'; row.dataset.transferRow = '';

            row.innerHTML = `<div class="d-flex justify-content-between align-items-center mb-3"><span class="transfer-item-label">STOCK ITEM</span><button type="button" class="btn btn-light btn-sm text-danger" aria-label="Remove item"><span class="material-symbols-outlined" aria-hidden="true" style="font-size:18px">delete</span></button></div><div class="row g-3"><div class="col-md-5"><label class="form-label d-block">Product to transfer<input data-source-item class="form-control" type="search" list="transfer-source-list-${i}" placeholder="Search product name…" autocomplete="off" required><datalist id="transfer-source-list-${i}" data-source-list></datalist></label><input type="hidden" data-source-id name="items[${i}][source_product_id]"></div><div class="col-md-5"><label class="form-label d-block">Same product at destination<input data-destination-item class="form-control" type="search" list="transfer-target-list-${i}" placeholder="Same product suggestions…" autocomplete="off" required><datalist id="transfer-target-list-${i}" data-target-list></datalist></label><input type="hidden" data-destination-id name="items[${i}][destination_product_id]"><div class="small text-muted mt-1" data-match-hint></div></div><div class="col-md-2"><label class="form-label d-block">Quantity<input class="form-control" type="number" name="items[${i}][quantity]" min="1" step="1" placeholder="0.00" required></label></div></div>`;

            row.querySelector('button').onclick = () => { row.remove(); request.dispatchEvent(new Event('change', {bubbles:true})); };

            row.querySelector('[data-source-item]').oninput = refresh;

            row.querySelector('[data-destination-item]').oninput = refresh;

            rows.append(row);

            if (i === 0 && rows.dataset.product !== '0') {const initial = products.find(p => String(p.id) === rows.dataset.product);if(initial) row.querySelector('[data-source-item]').value = label(initial);}

            refresh();

        }

        source.oninput = refresh; destination.oninput = refresh; source.onchange = refresh; destination.onchange = refresh; document.getElementById('stock-transfer-add-item').onclick = add; add();

        request.addEventListener('submit',refresh,true);

    }

    document.querySelectorAll('[data-transfer-form]').forEach(form => {

        let busy = false; const feedback = form.querySelector('[data-transfer-feedback]');

        function clear() { form.querySelectorAll('.is-invalid').forEach(field => { field.classList.remove('is-invalid');field.removeAttribute('aria-invalid');field.removeAttribute('aria-describedby'); }); form.querySelectorAll('[data-transfer-error]').forEach(e => e.remove());feedback.classList.add('d-none'); }

        form.addEventListener('input', () => { if(!busy) {clear();form.elements.request_key.value = token();} });

        form.addEventListener('change', () => { if(!busy) {clear();form.elements.request_key.value = token();} });

        form.addEventListener('submit', event => {

            event.preventDefault(); if(busy) return;clear();

            form.querySelectorAll('[data-dispatched]').forEach(reason => {

                const qty = reason.closest('.row').querySelector('input[type=number]'); reason.required = qty.value !== '' && Number(qty.value) !== Number(reason.dataset.dispatched);

            });

            let first = null;

            form.querySelectorAll('input,select,textarea').forEach(field => {

                if(field.willValidate && !field.validity.valid) {

                    field.classList.add('is-invalid');field.setAttribute('aria-invalid','true');

                    const error = document.createElement('div');error.className = 'invalid-feedback d-block';error.dataset.transferError = '';error.id = 'transfer-error-' + token();error.textContent = field.validationMessage;field.setAttribute('aria-describedby',error.id);field.after(error);first = first || field;

                }

            });

            if(first) {first.focus();first.scrollIntoView({block:'center'});return;}

            const data = new FormData(form);data.set('csrf_token',document.querySelector('meta[name=csrf-token]').content);

            const controls = Array.from(form.querySelectorAll('button,input,select,textarea')), states = controls.map(c => c.disabled), button = form.querySelector('[type=submit]'), original = button.innerHTML, originalClass = button.className;

            busy = true;controls.forEach(c => c.disabled = true);form.setAttribute('aria-busy','true');

            button.className = 'btn btn-primary';

            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" style="color:white" aria-hidden="true"></span><span style="color:white" role="status">Saving…</span>';

            let navigating = false;

            $.ajax({url:'?route=ajax/inventory_action',type:'POST',data,processData:false,contentType:false,dataType:'json'})

                .done(result => {

                    if(result.success) {navigating = true;window.location.assign(result.redirect);}

                    else {feedback.textContent = result.message || 'Transfer could not be saved.';feedback.classList.remove('d-none');feedback.focus();feedback.scrollIntoView({block:'center'});}

                }).fail(xhr => {feedback.textContent = xhr.responseJSON?.message || 'No confirmation received. Retry unchanged to safely check the result.';feedback.classList.remove('d-none');feedback.focus();})

                .always(() => {if(!navigating) {busy = false;controls.forEach((c,i) => c.disabled = states[i]);button.innerHTML = original;button.className = originalClass;form.removeAttribute('aria-busy');}});

        });

    });

    $('#stock-transfer-filter').on('change', function () {

        const filter = this.value;let count = 0;

        $('#stock-transfer-list tr').each(function () {

            const status = this.dataset.status;

            const show = filter === 'all' || (filter === 'pending' && ['pending_approval','approved','dispatched'].includes(status)) || (filter === 'completed' && status.startsWith('completed')) || (filter === 'discrepancy' && status === 'completed_discrepancy') || (filter === 'closed' && ['cancelled','rejected'].includes(status));

            $(this).toggle(show);if(show) count++;

        });$('#stock-transfer-empty').prop('hidden',count > 0);

    });

});

