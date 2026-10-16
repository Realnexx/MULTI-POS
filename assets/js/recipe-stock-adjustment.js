/* Manual recipe receipts are production, with ingredient consumption handled atomically. */
$(function () {
    const modal = $('#stockAdjustmentModal'), form = $('#stockAdjustmentForm');
    if (!form.length) return;
    const quantity = form.find('[name="quantity"]'), panel = $('#adj-recipe-panel');
    let context = null, rows = [], loading = false, failed = false, generation = 0;
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
        const incoming = $('#adjTypeSelect').val() === 'stock_in';
        panel.toggleClass('d-none', !recipe && !failed);
        quantity.attr({ min: recipe ? 1 : 0.0001, step: recipe ? 1 : 0.0001 });
        $('#batchInfoFields').stop(true, true).toggleClass('d-none', recipe && incoming).toggle(!recipe && incoming);
        $('#batchInfoFields :input').prop('disabled', recipe || !incoming);
        const confirmation = recipe && incoming && context.mode === 'made_to_order';
        $('#adj-recipe-confirm-label').toggleClass('d-none', !confirmation);
        $('#adj-recipe-confirm').prop('required', confirmation).prop('disabled', !confirmation);
        $('#adj-recipe-requirements').empty();
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
        context = null; rows = [];
        if (!id) return;
        loading = true; failed = false; context = null;
        form.find('button[type="submit"]').prop('disabled', true);
        $.post('ajax/inventory_action.php', { action: 'get_product_recipe', product_id: id,
            branch_id: $('#adj_branch_id_hidden').val(), csrf_token: form.find('[name="csrf_token"]').val()
        }).done(function (response) {
            if (ticket !== generation) return;
            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (!result.success || !result.product) throw new Error(result.message || 'Unable to load recipe details.');
                context = result.product; rows = result.recipe || [];
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
            loading = false; render(); form.find('button[type="submit"]').prop('disabled', failed);
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
        form.find('input,select,textarea').each(function () {
            if (this.disabled || !this.willValidate || this.validity.valid) return;
            let message = this.validationMessage;
            if (this.validity.valueMissing) message = this.type === 'checkbox' ? 'Please confirm preparation before adding stock.' : 'This field is required.';
            if (this.name === 'quantity' && !this.validity.valueMissing) message = context && context.type === 'recipe' ? 'Enter a positive whole stock quantity.' : 'Enter a positive stock quantity with up to four decimal places.';
            const field = fieldError($(this), message); if (!first) first = field;
        });
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
