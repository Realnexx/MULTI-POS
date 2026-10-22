/* Manual recipe receipts are production, with ingredient consumption handled atomically. */
$(function () {
    const modal = $('#stockAdjustmentModal'), form = $('#stockAdjustmentForm');
    if (!form.length) return;
    const quantity = form.find('[name="quantity"]'), panel = $('#adj-recipe-panel');
    let context = null, rows = [], packageReport = null, loading = false, failed = false, generation = 0;
    const summary = $('<div class="alert alert-danger d-none" role="alert" tabindex="-1">').prependTo(form.find('.modal-body'));
    function clearErrors() {
        form.find('[data-adjustment-error]').remove();
        form.find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid aria-describedby');
        summary.addClass('d-none').text('');
    }
    function fieldError(field, message) {
        if (!field || !field.length || field.attr('type') === 'hidden') {
            summary.removeClass('d-none').text(message); return summary;
        }
        const id = 'adjustment-error-' + field.attr('name');
        field.addClass('is-invalid').attr({ 'aria-invalid': 'true', 'aria-describedby': id });
        $('<div class="invalid-feedback d-block" data-adjustment-error>').attr('id', id).text(message).insertAfter(field);
        return field;
    }
    function focusError(field) {
        field[0].scrollIntoView({ block: 'center', behavior: 'smooth' }); field[0].focus({ preventScroll: true });
    }
    form.on('adjustment:error', function (_, message, name) {
        clearErrors(); focusError(fieldError(name ? form.find('[name="' + name + '"]') : null, message));
    });
    modal.on('show.bs.modal', clearErrors);
    function render() {
        const recipe = context && context.type === 'recipe';
        const combo = context && context.type === 'combo';
        modal.toggleClass('package-stock-mode', !!combo);
        modal.find('.modal-title').text(combo ? 'Package availability' : 'Manual Stock Adjustment');
        modal.find('.modal-header .badge.rounded-pill').text(combo ? 'Live stock check' : 'Stock Ledger');
        quantity.closest('.adj-spec-card').addClass('package-quantity-card');
        $('#adj_branch_id').closest('.adj-spec-card').addClass('package-branch-card');
        $('#adj_branch_id').closest('.col-sm-6').toggleClass('col-sm-12', !!combo);
        modal.find('.modal-footer [type="submit"]').toggleClass('d-none', !!combo);
        modal.find('.modal-footer [data-bs-dismiss]').empty().append($('<span class="material-symbols-outlined inv-button-icon" aria-hidden="true">').text('close'), document.createTextNode(combo ? 'Close' : 'Discard'));
        const direction = $('#adjTypeSelect').closest('.col-sm-6'), expiry = form.find('[name="expiry_date"]').closest('.col-sm-6');
        direction.toggleClass('d-none', !!combo); expiry.toggleClass('d-none', !!combo);
        quantity.closest('.col-sm-6').toggleClass('col-sm-12', !!combo);
        quantity.closest('.col-sm-6').find('label').text(combo ? 'Packages to check' : 'Quantity Units');
        quantity.attr('placeholder', combo ? 'Enter package quantity (e.g. 1)' : 'Enter quantity (e.g. 10)');
        const sectionLabel = quantity.closest('.adj-spec-card').find('.adj-card-label');
        if (!sectionLabel.data('original')) sectionLabel.data('original', sectionLabel.html());
        if (combo) sectionLabel.text('Check your package quantity'); else sectionLabel.html(sectionLabel.data('original'));
        const incoming = $('#adjTypeSelect').val() === 'stock_in';
        panel.toggleClass('d-none', !recipe && !combo && !failed).toggleClass('alert-warning', !!combo).toggleClass('alert-info', !combo);
        panel.find('strong').first().text(combo ? 'Availability breakdown' : 'Recipe stock adjustment');
        quantity.attr({ min: recipe || combo ? 1 : 0.0001, step: recipe || combo ? 1 : 0.0001 });
        $('#batchInfoFields').stop(true, true).toggleClass('d-none', combo || recipe && incoming).toggle(!combo && !recipe && incoming);
        $('#batchInfoFields :input').prop('disabled', combo || recipe || !incoming);
        form.find('[name="remarks"]').closest('.adj-spec-card').toggleClass('d-none', !!combo);
        form.find('[name="remarks"]').prop('disabled', !!combo);
        $('#batchSelectionField').toggleClass('d-none', !!combo || incoming);
        $('#adjTypeSelect').prop('disabled', !!combo);
        form.find('button[type="submit"]').prop('disabled', !!combo || failed || loading);
        const confirmation = recipe && incoming && context.mode === 'made_to_order';
        $('#adj-recipe-confirm-label').toggleClass('d-none', !confirmation);
        $('#adj-recipe-confirm').prop('required', confirmation).prop('disabled', !confirmation);
        $('#adj-recipe-requirements').empty();
        if (combo) {
            $('#adj-recipe-message').text('Availability is based on included products and their ingredients.');
            const count = Math.max(1, Number(quantity.val()) || 1);
            for (const branch of packageReport?.branches || []) {
                const status = $('<div class="package-capacity-summary">').toggleClass('needs-setup', !!branch.problem).appendTo('#adj-recipe-requirements');
                $('<span class="material-symbols-outlined" aria-hidden="true">').text(branch.problem ? 'build_circle' : 'inventory_2').appendTo(status);
                $('<span>').text(branch.problem ? 'Setup required' : branch.capacity + ' packages available').appendTo(status);
                $('<small>').text(branch.branch).appendTo(status);
                if (branch.problem) {
                    const error = $('<div class="package-setup-error" role="alert">').appendTo('#adj-recipe-requirements');
                    $('<strong>').text('Complete the product setup').appendTo(error);
                    $('<p>').text(branch.problem).appendTo(error);
                    $('<p class="package-next-step mb-0">').text(branch.problem.includes('Included product') ? 'Next: Edit the included product → add its recipe ingredients and quantities → save.' : 'Next: Edit this package → Package items → add the included products → save.').appendTo(error);
                }
                for (const item of branch.ingredients) {
                    const needed = Number(item.per_unit) * count, available = Number(item.available), missing = Math.max(0, needed - available);
                    const format = n => n.toLocaleString(undefined, { maximumFractionDigits: 4 });
                    const card = $('<div class="package-stock-item">').toggleClass('is-short', missing > 0).appendTo('#adj-recipe-requirements');
                    $('<strong>').text(item.name).appendTo(card);
                    $('<span class="package-stock-state">').text(missing > 0 ? 'Missing ' + format(missing) + ' ' + item.unit : 'Ready').appendTo(card);
                    $('<small>').text('Required: ' + format(needed) + ' ' + item.unit + ' · Available: ' + format(available) + ' ' + item.unit).appendTo(card);
                }
            }
            return;
        }
        if (!recipe) return;
        let message = incoming
            ? 'Adding finished units consumes the saved ingredients once. Sales then deduct finished stock.'
            : 'Removing finished units reduces product stock only. Already-used ingredients are not returned or deducted again.';
        if (confirmation) message += ' This changes the product from made to order to prepared stock.';
        if (confirmation && context.branch_id === null) message += ' This sales mode change applies to all branches.';
        if (context.mode === 'legacy') message = 'Reconcile this recipe in Edit Product before preparing stock.';
        $('#adj-recipe-message').text(message);
        if (incoming) rows.forEach(row => {
            $('<div>').text((row.ingredient_name || row.name || 'Ingredient') + ': ' +
                (Number(row.quantity) * (Number(quantity.val()) || 0)).toLocaleString(undefined, { maximumFractionDigits: 4 }) +
                ' ' + (row.unit || '')).appendTo('#adj-recipe-requirements');
        });
    }
    function load() {
        const id = $('#adj_prod_id').val(), ticket = ++generation;
        context = null; rows = []; packageReport = null;
        if (!id) return;
        loading = true; failed = false; context = null;
        modal.removeClass('package-stock-mode');
        form.find('button[type="submit"]').prop('disabled', true);
        $.post('ajax/inventory_action.php', { action: 'get_product_recipe', product_id: id,
            branch_id: $('#adj_branch_id_hidden').val(), csrf_token: form.find('[name="csrf_token"]').val()
        }).done(function (response) {
            if (ticket !== generation) return;
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (!result.success || !result.product) throw new Error(result.message || 'Unable to load recipe details.');
                context = result.product; rows = result.recipe || []; packageReport = result.package_report;
                if (context.type === 'combo' && !quantity.val()) quantity.val('1');
                modal.data('current-stock', Number(context.physical_stock));
                $('#adj_branch_id').prop('disabled', !(context.branch_id === null && context.global_access));
                if (context.branch_id !== null) {
                    $('#adj_branch_id, #adj_branch_id_hidden').val(context.branch_id);
                }
            } catch (error) { failed = true; $('#adj-recipe-message').text(error.message); }
        }).fail(function () {
            if (ticket !== generation) return;
            failed = true; $('#adj-recipe-message').text('Unable to load stock details. Reopen this adjustment to retry.');
        }).always(function () {
            if (ticket !== generation) return;
            loading = false; render();
        });
    }
    modal.on('shown.bs.modal', load);
    $(document).on('click', '.product-suggestion-item', function () { setTimeout(load, 0); });
    $('#adj_branch_id').on('change', load);
    $('#adjTypeSelect').on('change', render);
    quantity.on('input', render);
    form.on('input change', ':input:not([name="request_key"])', function () {
        const errorId = this.getAttribute('aria-describedby');
        if (errorId && errorId.startsWith('adjustment-error-')) document.getElementById(errorId)?.remove();
        $(this).removeClass('is-invalid').removeAttr('aria-invalid aria-describedby');
        summary.addClass('d-none').text('');
        const bytes = new Uint8Array(16); crypto.getRandomValues(bytes);
        form.find('[name="request_key"]').val(Array.from(bytes, b => b.toString(16).padStart(2, '0')).join(''));
    });
    form[0].addEventListener('submit', function (event) {
        clearErrors();
        let first = null;
        if (context && context.type === 'combo') {
            event.preventDefault(); event.stopImmediatePropagation();
            focusError(fieldError(null, 'Combo meals use component stock. Replenish the missing items listed below instead of adding a package batch.')); return;
        }
        form.find('input,select,textarea').each(function () {
            if (this.disabled || !this.willValidate || this.validity.valid) return;
            let message = this.validationMessage;
            if (this.validity.valueMissing) message = this.type === 'checkbox' ? 'Please confirm preparation before adding stock.' : this.name === 'quantity' ? 'Enter the quantity to add or remove.' : this.name === 'remarks' ? 'Enter a reason for this stock adjustment.' : 'This field is required.';
            if (this.name === 'quantity' && !this.validity.valueMissing) message = context && context.type === 'recipe' ? 'Enter a positive whole stock quantity.' : 'Enter a positive stock quantity with up to four decimal places.';
            const field = fieldError($(this), message); if (!first) first = field;
        });
        if (!form.find('[name="branch_id"]').val()) first = fieldError($('#adj_branch_id'), 'Select the branch receiving or releasing stock.');
        const remarks = form.find('[name="remarks"]');
        if (!remarks.prop('disabled') && remarks.val() && !remarks.val().trim()) first = fieldError(remarks, 'Enter a reason for this stock adjustment.');
        for (const name of ['cost_price', 'selling_price']) {
            const field = form.find('[name="' + name + '"]');
            if (!field.prop('disabled') && field.val() !== '' && Number(field.val()) < 0) first = fieldError(field, 'Enter an amount of zero or greater.');
        }
        if (loading || failed || !context) first = fieldError(null, loading ? 'Loading stock details. Please wait.' : 'Select a product and reload its stock details before submitting.');
        if (!first) {
            const qty = Number(quantity.val()), current = Number(modal.data('current-stock')), max = Number(modal.data('max-threshold'));
            const incoming = $('#adjTypeSelect').val() === 'stock_in';
            if (incoming && max > 0 && current + qty > max) first = fieldError(quantity, 'Stock would be ' + (current + qty).toFixed(2) + ', exceeding the maximum of ' + max.toFixed(2) + '.');
            if (!incoming && qty > current) first = fieldError(quantity, 'Only ' + current.toFixed(2) + ' finished stock is available.');
        }
        if (first) { event.preventDefault(); event.stopImmediatePropagation(); focusError(first); }
    }, true);
});
