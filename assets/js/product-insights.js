/* Read-only catalog breakdowns; ignore responses from previously selected products. */
(() => {
    let generation = 0;
    window.loadProductInsightBreakdown = function (data, row) {
        const ticket = ++generation, type = row?.product_type || data.type;
        const section = document.getElementById('view_recipe_section');
        const list = document.getElementById('view_ingredients_list');
        const supported = type === 'recipe' || type === 'combo';
        section.classList.toggle('d-none', !supported);
        list.replaceChildren();
        if (!supported) return;
        const combo = type === 'combo';
        document.getElementById('view_breakdown_title').textContent = combo ? 'Package breakdown' : 'Ingredient breakdown';
        document.getElementById('view_breakdown_item_heading').textContent = combo ? 'Included product' : 'Ingredient';
        document.getElementById('view_breakdown_detail_heading').textContent = combo ? 'Preparation station' : 'Unit';
        const note = document.getElementById('view_breakdown_note');
        note.textContent = 'Loading breakdown…';
        const selectedBranch = document.getElementById('branchFilter')?.value || '';
        const request = { action: 'get_product_recipe', product_id: data.id,
            branch_id: /^\d+$/.test(selectedBranch) ? selectedBranch : '',
            csrf_token: document.querySelector('[name="csrf_token"]').value };
        $.post('?route=ajax/inventory_action', request, null, 'json').done(result => {
            if (ticket !== generation) return;
            if (!result.success) { note.textContent = result.message || 'Unable to load the breakdown.'; return; }
            const items = combo ? result.package_items || [] : result.recipe || [];
            note.textContent = combo
                ? 'Included quantities per package. Each product consumes its own stock or recipe ingredients.'
                : result.product.mode === 'made_to_order'
                    ? 'Ingredients per finished unit. These quantities are deducted when the product is sold.'
                    : 'Ingredients per finished unit. Prepared stock consumes these ingredients during production.';
            if (!items.length) { note.textContent = combo ? 'No package items configured. Add included products in Edit Product → Package items.' : 'No ingredients configured. Add the recipe ingredients in Edit Product.'; return; }
            for (const item of items) {
                const tr = document.createElement('tr');
                const values = [combo ? item.name : item.ingredient_name,
                    Number(item.quantity).toLocaleString(undefined, { maximumFractionDigits: 4 }),
                    combo ? item.station || 'General' : item.unit];
                values.forEach((value, index) => {
                    const cell = document.createElement('td');
                    cell.className = 'py-2 border-0 ' + (index === 0 ? 'ps-3' : index === 1 ? 'text-center' : 'pe-3 text-end');
                    cell.textContent = value; tr.append(cell);
                });
                list.append(tr);
            }
        }).fail(() => {
            if (ticket === generation) note.textContent = 'Unable to load the breakdown. Close and reopen Product Insights to retry.';
        });
    };
})();
