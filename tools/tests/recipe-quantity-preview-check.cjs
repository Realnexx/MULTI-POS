const assert = require('node:assert/strict');
const { chromium } = require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async () => {
    const browser = await chromium.launch({ executablePath: 'C:/Users/MIS-03/AppData/Local/ms-playwright/chromium_headless_shell-1248/chrome-headless-shell-win64/chrome-headless-shell.exe', headless: true });
    try {
        const page = await browser.newPage();
        const fixture = id => `<div id="${id}"><form><input class="recipe-preview-count" type="number" value="1"><div class="recipe-quantity-totals"></div><div class="recipe-cost-preview"></div><div class="ingredient-row"><select class="ing-select"><option value="1" data-unit="kg" data-cost="200">Beef</option></select><input class="ing-qty" name="recipe[0][quantity]" value="100"><select class="ing-unit"><option>g</option></select></div></form></div>`;
        await page.setContent(fixture('createProductModal') + fixture('editProductModal'));
        await page.addScriptTag({ path: 'assets/admin/libs/jquery/dist/jquery.min.js' });
        await page.addScriptTag({ path: 'assets/js/recipe-admin.js' });
        await page.waitForFunction(() => document.querySelector('.recipe-quantity-totals').textContent.includes('100 g'));
        for (const count of [2, 3, 10]) {
            await page.locator('#createProductModal .recipe-preview-count').fill(String(count));
            const text = await page.locator('#createProductModal .recipe-quantity-totals').innerText();
            assert(text.includes(`${(100 * count).toLocaleString()} g`));
            assert(text.includes(`₱${(20 * count).toFixed(2)}`));
            assert.equal(await page.locator('#createProductModal .ing-qty').inputValue(), '100');
        }
        assert((await page.locator('#editProductModal .recipe-quantity-totals').innerText()).includes('100 g'));
        await page.locator('#createProductModal .recipe-preview-count').fill('1.5');
        assert((await page.locator('#createProductModal .recipe-quantity-totals').innerText()).includes('whole number'));
        await page.locator('#editProductModal .recipe-preview-count').fill('3');
        await page.locator('#editProductModal .ing-qty').fill('125.5');
        assert((await page.locator('#editProductModal .recipe-quantity-totals').innerText()).includes('376.5 g'));
        assert.equal(await page.locator('[name="recipe[0][quantity]"]').first().inputValue(), '100');
        await page.locator('#createProductModal form').evaluate(form => {
            form.insertAdjacentHTML('afterbegin', '<select id="create_product_type"><option value="recipe">Recipe</option><option value="simple">Simple</option></select><select id="create_recipe_mode"><option value="made_to_order">Made to order</option><option value="prepared_batch">Finished stock</option></select><input name="stock" type="number" value="10">');
        });
        // Rebind the create-form handlers for the complete opening-stock fixture.
        await page.addScriptTag({ path: 'assets/js/recipe-admin.js' });
        await page.waitForFunction(() => document.querySelector('#createProductModal .recipe-preview-count').min === '0');
        await page.evaluate(() => $('#create_product_type').trigger('change'));
        assert.equal(await page.locator('#createProductModal [name="stock"]').isEditable(), true);
        assert.equal(await page.locator('#create_recipe_mode').inputValue(), 'prepared_batch');
        assert.equal(await page.locator('#createProductModal .recipe-preview-count').inputValue(), '10');
        assert.equal(await page.locator('#createProductModal .recipe-preview-count').isEditable(), false);
        assert((await page.locator('#createProductModal .recipe-quantity-totals').innerText()).includes('1,000 g'));
        await page.locator('#createProductModal [name="stock"]').fill('20');
        assert((await page.locator('#createProductModal .recipe-quantity-totals').innerText()).includes('2,000 g'));
        assert.equal(await page.locator('#createProductModal .ing-qty').inputValue(), '100');
        await page.locator('#create_recipe_mode').selectOption('made_to_order');
        assert.equal(await page.locator('#createProductModal [name="stock"]').inputValue(), '0');
        assert((await page.locator('#createProductModal .recipe-quantity-totals').innerText()).includes('will not deduct'));
        await page.locator('#createProductModal [name="stock"]').fill('2');
        assert.equal(await page.locator('#create_recipe_mode').inputValue(), 'prepared_batch');
        await page.locator('#createProductModal .ing-qty').fill('200');
        assert((await page.locator('#createProductModal .recipe-quantity-totals').innerText()).includes('400 g'));
        await page.locator('#createProductModal [name="stock"]').fill('1.5');
        assert((await page.locator('#createProductModal .recipe-quantity-totals').innerText()).includes('whole initial stock'));
        await page.locator('#createProductModal [name="stock"]').fill('0');
        await page.locator('#create_product_type').selectOption('simple');
        assert.equal(await page.locator('#createProductModal .recipe-preview-count').inputValue(), '1');
        assert.equal(await page.locator('#createProductModal .recipe-preview-count').evaluate(input => input.validity.valid), true);
        console.log('PASS: create/edit cost and quantity previews, opening-stock synchronization, zero stock, B1T1 quantities, mode selection, invalid stock, product-type switching, and unchanged per-unit recipes.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
