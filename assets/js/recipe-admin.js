/* Recipe setup is separate from stock consumption. */
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
        field.prop('readOnly',recipe);
        if (recipe) field.val('0');
    }
    $('#create_product_type').on('change',recipeStockField);
    $('#createProductModal').on('shown.bs.modal',recipeStockField);
    function preview() {
        $('.ing-select').each(function () {
            if (!$(this).prev('.ingredient-search').length) {
                $('<input type="search" class="form-control form-control-sm mb-1 ingredient-search" placeholder="Search ingredients" aria-label="Search ingredients">').insertBefore(this);
            }
        });
        $('#createProductModal, #editProductModal').each(function () {
            let cost = 0;
            const form = $(this);
            let rows = 0;
            form.find('.ingredient-row').each(function () {
                const row = $(this), selected = row.find('.ing-select option:selected');
                const qty = Number(row.find('.ing-qty').val());
                const from = String(row.find('.ing-unit').val() || '').toLowerCase();
                const to = String(selected.data('unit') || '').toLowerCase();
                if (qty > 0 && selected.val()) {
                    cost += qty * (factor[from] || 1)/(factor[to] || 1)*Number(selected.data('cost') || 0);
                    rows++;
                }
            });
            form.find('.recipe-cost-preview').text(rows ? 'Estimated ingredient cost per burger: ₱' + cost.toFixed(2) + ' (current base-unit costs)' : 'Create Raw Ingredient products first, then select ingredients and quantities per burger.');
        });
    }
    $(document).on('input', '.ingredient-search', function () {
        const term = this.value.toLowerCase();
        $(this).next('select').find('option').each(function () { this.hidden = !!this.value && !this.textContent.toLowerCase().includes(term); });
    });
    $(document).on('input change', '.ing-select, .ing-qty, .ing-unit', preview);
    $(document).on('recipe:changed shown.bs.modal', preview);
    $(document).on('click', '.remove-ingredient', () => setTimeout(preview,0));
    $('#prepare_recipe_batch').on('click', function () {
        if ($('#edit_recipe_mode').val() !== 'prepared_batch') {
            alert('Select and save Prepared batch mode before preparing stock.'); return;
        }
        const quantity = $('#prepare_recipe_quantity').val();
        if (!confirm('Prepare ' + quantity + ' burgers now? This consumes ingredients and creates finished stock.')) return;
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
    $('#return_stock_sale, #return_stock_policy').on('change', () => { reversalKey = null; });
    $('#return_sale_stock').on('click', function () {
        if (!confirm('Confirm the selected stock is usable and this return policy is correct. Prepared food must not be returned as raw ingredients.')) return;
        const button = $(this).prop('disabled',true);
        reversalKey = reversalKey || token();
        $.post('?route=ajax/inventory_action', {
            action:'reverse_sale_stock', sale_id:$('#return_stock_sale').val(), policy:$('#return_stock_policy').val(),
            request_key:reversalKey, csrf_token:$('[name="csrf_token"]').val()
        }, response => {
            alert(response.message);
            if (response.success) { reversalKey=null; window.location.reload(); }
        }, 'json').fail(() => alert('No confirmation received. Retry this same return to safely check its result.'))
          .always(() => button.prop('disabled',false));
    });
    preview();
});
