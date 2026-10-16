/* Recipe edits are inventory-neutral; opening finished stock consumes its recipe once. */
$(function () {
    const factor = {pc:1, pcs:1, g:1, kg:1000, ml:1, l:1000};
    let productionKey = null;
    const token = () => Array.from(crypto.getRandomValues(new Uint8Array(16)), b => b.toString(16).padStart(2,'0')).join('');
    $('#create_ingredient_shortcut').on('click', function () {
        $('#createProductModal').modal('show');
        $('#create_product_type').val('ingredient').trigger('change');
    });
    $('#stockAdjustmentModal').on('shown.bs.modal', function () {
        $('#stockAdjustmentForm input[name="request_key"]').val(token());
    });
    function recipeStockField() {
        const recipe = $('#create_product_type').val() === 'recipe';
        const field = $('#createProductModal input[name="stock"]');
        field.prop('readOnly', false).attr('step', recipe ? '1' : '0.0001');
        if (recipe) field.attr('max', '100000');
        else field.removeAttr('max');
        const count = $('#createProductModal .recipe-preview-count');
        count.prop('readOnly', recipe);
        if (recipe) {
            count.val(field.val() || '0').attr('max', '100000');
            if (Number(field.val()) > 0) $('#create_recipe_mode').val('prepared_batch');
        } else {
            count.val(Math.max(1, Number(count.val()) || 1)).attr('max', '1000000');
        }
        preview();
    }
    $('#create_product_type').on('change',recipeStockField);
    $('#createProductModal').on('shown.bs.modal',recipeStockField);
    $('#createProductModal input[name="stock"]').on('input change', function () {
        if ($('#create_product_type').val() !== 'recipe') return;
        if (Number(this.value) > 0) $('#create_recipe_mode').val('prepared_batch');
        $('#createProductModal .recipe-preview-count').val(this.value || '0');
        preview();
    });
    $('#create_recipe_mode').on('change', function () {
        if (this.value === 'made_to_order') {
            $('#createProductModal input[name="stock"]').val('0').trigger('change');
        }
    });
    let recipeLoad = 0;
    let recipeButtons = null;
    function finishRecipeLoad() {
        if (recipeButtons) recipeButtons.forEach(([button,disabled]) => { button.disabled=disabled; });
        recipeButtons = null;
    }
    $('#create_recipe_source').on('change', function () {
        const source = this.value, generation = ++recipeLoad;
        const feedback = $('#create_recipe_source_feedback');
        if (!source) { finishRecipeLoad(); feedback.text('You can configure this product’s ingredients below.'); return; }
        if (!recipeButtons) recipeButtons = $('#createProductModal [type="submit"]').toArray().map(button => [button,button.disabled]);
        recipeButtons.forEach(([button]) => { button.disabled=true; });
        feedback.text('Loading recipe…');
        $.post('?route=ajax/inventory_action', {
            action:'get_product_recipe', product_id:source, csrf_token:$('[name="csrf_token"]').val()
        }, function (response) {
            if (generation !== recipeLoad) return;
            if (!response.success || !Array.isArray(response.recipe) || !response.recipe.length) {
                $('#create_recipe_source').val('');
                feedback.text(response.message || 'This recipe has no ingredients. Configure it in Recipe Management first.'); return;
            }
            $(document).trigger('recipe:import', [response.recipe]);
            feedback.text('Recipe loaded. Set Initial Stock to calculate the ingredients required.');
        }, 'json').fail(function () {
            if (generation === recipeLoad) { $('#create_recipe_source').val(''); feedback.text('Could not load this recipe. Please select it again to retry.'); }
        }).always(function () {
            if (generation === recipeLoad) finishRecipeLoad();
        });
    });
    function preview() {
        // Used by both custom recipes and copies loaded from Recipe Management.
        $('.ing-select').each(function () {
            if (!$(this).prev('.ingredient-search').length) {
                $('<input type="search" class="form-control form-control-sm mb-1 ingredient-search" placeholder="Search ingredients" aria-label="Search ingredients">').insertBefore(this);
            }
        });
        $('#createProductModal, #editProductModal').each(function () {
            let cost = 0;
            const form = $(this);
            const countField = form.find('.recipe-preview-count')[0];
            const count = countField ? Number(countField.value) : 1;
            const opening = form.is('#createProductModal') && $('#create_product_type').val() === 'recipe';
            if (countField) countField.min = opening ? '0' : '1';
            const validCount = Number.isInteger(count) && count >= (opening ? 0 : 1) && count <= (opening ? 100000 : 1000000);
            const totals = form.find('.recipe-quantity-totals').empty();
            const quantities = new Map();
            let rows = 0;
            form.find('.ingredient-row').each(function () {
                const row = $(this), selected = row.find('.ing-select option:selected');
                const qty = Number(row.find('.ing-qty').val());
                const from = String(row.find('.ing-unit').val() || '').toLowerCase();
                const to = String(selected.data('unit') || '').toLowerCase();
                if (qty > 0 && selected.val()) {
                    cost += qty * (factor[from] || 1)/(factor[to] || 1)*Number(selected.data('cost') || 0);
                    rows++;
                    const key = selected.val() + ':' + from;
                    const existing = quantities.get(key);
                    quantities.set(key, { name: selected.text().trim(), unit: from, quantity: qty + (existing ? existing.quantity : 0) });
                }
            });
            form.find('.recipe-cost-preview').text(rows ? 'Estimated ingredient cost per stock unit: ₱' + cost.toFixed(2) + ' (current base-unit costs)' : 'Create Raw Ingredient products first, then select ingredients and quantities per stock unit.');
            if (!validCount) {
                totals.text(opening ? 'Enter a whole initial stock quantity from 0 to 100,000.' : 'Enter a whole number of stock units from 1 to 1,000,000.');
            } else if (opening && count === 0) {
                totals.text('Initial stock is 0. Saving this recipe will not deduct any ingredients.');
            } else if (!rows) {
                totals.text('Add an ingredient and its quantity per stock unit to see the totals.');
            } else {
                $('<p class="fw-bold mb-1">').text('Required for ' + count.toLocaleString() + (count === 1 ? ' stock unit:' : ' stock units:')).appendTo(totals);
                const list = $('<ul class="mb-2 ps-3">').appendTo(totals);
                quantities.forEach(item => {
                    $('<li>').text(item.name + ': ' + (item.quantity * count).toLocaleString(undefined, { maximumFractionDigits: 4 }) + ' ' + item.unit).appendTo(list);
                });
                $('<p class="mb-0 fw-bold">').text('Estimated total ingredient cost: ₱' + (cost * count).toFixed(2)).appendTo(totals);
                if (opening) $('<p class="mb-0 mt-1">').text('Saving will deduct these ingredients once and create ' + count.toLocaleString() + ' finished stock units.').appendTo(totals);
            }
        });
    }
    $(document).on('input', '.ingredient-search', function () {
        const term = this.value.toLowerCase();
        $(this).next('select').find('option').each(function () { this.hidden = !!this.value && !this.textContent.toLowerCase().includes(term); });
    });
    $(document).on('input change', '.ing-select, .ing-qty, .ing-unit, .recipe-preview-count', preview);
    $(document).on('recipe:changed shown.bs.modal', preview);
    $(document).on('click', '.remove-ingredient', () => setTimeout(preview,0));
    $('#prepare_recipe_batch').on('click', function () {
        if ($('#edit_recipe_mode').val() !== 'prepared_batch') {
            alert('Select and save Prepared batch mode before preparing stock.'); return;
        }
        const quantity = $('#prepare_recipe_quantity').val();
        if (!confirm('Prepare ' + quantity + ' units now? This consumes ingredients and creates finished stock.')) return;
        const button = $(this).prop('disabled',true);
        productionKey = productionKey || token();
        $.post('?route=ajax/inventory_action', {
            action:'prepare_recipe_batch', product_id:$('#edit_product_id').val(), quantity,
            branch_id:$('#edit_prod_branch').val() || '', request_key:productionKey,
            csrf_token:$('[name="csrf_token"]').val()
        }, function (response) {
            alert(response.message || 'Batch request completed.');
            if (response.success) { productionKey = null; window.location.reload(); }
        }, 'json').fail(() => alert('No confirmation received. Retry without changing the batch to safely check the same request.'))
          .always(() => button.prop('disabled',false));
    });
    $('#prepare_recipe_quantity, #edit_product_id, #edit_prod_branch').on('change', () => { productionKey = null; });
    let reversalKey = null;
    let reviewedReturn = null;
    let returnPending = false;
    function showReturnMessage(message) {
        $('#return_stock_message_text').text(message || 'Stock return could not be completed. Please try again.');
        $('#return_stock_message').prop('hidden',false).trigger('focus');
    }
    function showReturnEntry() {
        $('#return_stock_message').prop('hidden',true);
        reviewedReturn = null;
        $('#return_stock_entry').prop('hidden',false);
        $('#return_stock_confirmation').prop('hidden',true);
        $('#returnStockTitle').text('Return stock from a recorded sale');
        $('#return_step_select').addClass('is-current').attr('aria-current','step');
        $('#return_step_confirm').removeClass('is-current').removeAttr('aria-current');
    }
    $('#returnStockModal').on('hidden.bs.modal', showReturnEntry);
    $('#returnStockModal').on('hide.bs.modal', function (event) {
        if (returnPending) event.preventDefault();
    });
    $('#cancel_return_stock').on('click', function () {
        if (returnPending) return;
        showReturnEntry();
        $('#return_sale_stock').trigger('focus');
    });
    $('#return_stock_sale, #return_stock_policy').on('change', () => { reversalKey = null; });
    $('#return_sale_stock').on('click', function () {
        if (returnPending) return;
        const sale = document.getElementById('return_stock_sale');
        sale.required = true;
        if (!sale.reportValidity()) return;
        reviewedReturn = {sale_id:sale.value, policy:$('#return_stock_policy').val()};
        $('#return_stock_confirm_sale').text('#' + reviewedReturn.sale_id);
        $('#return_stock_confirm_policy').text($('#return_stock_policy option:selected').text());
        $('#return_stock_entry').prop('hidden',true);
        $('#return_stock_confirmation').prop('hidden',false);
        $('#returnStockTitle').text('Confirm stock return');
        $('#return_step_select').removeClass('is-current').removeAttr('aria-current');
        $('#return_step_confirm').addClass('is-current').attr('aria-current','step');
        $('#cancel_return_stock').trigger('focus');
    });
    $('#confirm_return_stock').on('click', function () {
        if (!reviewedReturn || returnPending) return;
        $('#return_stock_message').prop('hidden',true);
        returnPending = true;
        const button = $(this).prop('disabled',true).text('Returning stock…');
        $('#cancel_return_stock').prop('disabled',true);
        reversalKey = reversalKey || token();
        $.post('?route=ajax/inventory_action', {
            action:'reverse_sale_stock', sale_id:reviewedReturn.sale_id, policy:reviewedReturn.policy,
            request_key:reversalKey, csrf_token:$('[name="csrf_token"]').val()
        }, response => {
            if (response.success) { reversalKey=null; window.location.reload(); }
            else showReturnMessage(response.message);
        }, 'json').fail(xhr => showReturnMessage(xhr.responseJSON && xhr.responseJSON.message || 'No confirmation received. Retry this same return to safely check its result.'))
          .always(() => {
              returnPending = false;
              button.prop('disabled',false).text('Confirm and return stock');
              $('#cancel_return_stock').prop('disabled',false);
          });
    });
    preview();
});
