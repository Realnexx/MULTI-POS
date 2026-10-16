const assert = require('node:assert/strict'), fs = require('node:fs');
const { chromium } = require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async () => {
 const browser = await chromium.launch({ executablePath: 'C:/Users/MIS-03/AppData/Local/ms-playwright/chromium_headless_shell-1248/chrome-headless-shell-win64/chrome-headless-shell.exe', headless: true });
 try {
  const page = await browser.newPage();
  const errors = []; page.on('pageerror', error => errors.push(error.message));
  const markup = await page.evaluate(html => {
   const modal = new DOMParser().parseFromString(html, 'text/html').getElementById('stockAdjustmentModal');
   modal.querySelectorAll('script,img,link,iframe').forEach(node => node.remove()); return modal.outerHTML;
  }, fs.readFileSync('scratch/product-transfers-rendered.html', 'utf8'));
  await page.setContent(markup, { waitUntil: 'domcontentloaded' });
  await page.addStyleTag({ path: 'assets/admin/libs/bootstrap/dist/css/bootstrap.min.css' });
  await page.addScriptTag({ path: 'assets/admin/libs/jquery/dist/jquery.min.js' });
  await page.evaluate(() => {
   window.fixture = { success: true, product: { type: 'recipe', mode: 'made_to_order', branch_id: 1, physical_stock: '0.0000', global_access: false }, recipe: [{ ingredient_name: 'Bun', quantity: '2', unit: 'pc' }] };
   $.post = () => { const deferred = $.Deferred(); setTimeout(() => deferred.resolve(fixture), 10); return deferred.promise(); };
   $('#stockAdjustmentModal').addClass('show').css('display', 'block');
   $('#adj_prod_id').val('123'); $('#adj_branch_id, #adj_branch_id_hidden').val('1');
  });
  await page.addScriptTag({ path: 'assets/js/recipe-stock-adjustment.js' });
  await page.evaluate(() => $('#stockAdjustmentModal').trigger('shown.bs.modal'));
  await page.waitForFunction(() => !$('#stockAdjustmentForm button[type=submit]').prop('disabled'));
  await page.locator('[name=quantity]').fill('10');
  assert.equal(await page.locator('#adj-recipe-requirements').innerText(), 'Bun: 20 pc');
  assert.equal(await page.locator('#adj-recipe-confirm').getAttribute('required'), '');
  assert.equal(await page.locator('[name=quantity]').getAttribute('step'), '1');
  assert.equal(await page.locator('[name=batch_number]').isDisabled(), true);
  await page.evaluate(() => { window.submitted = 0; $('#stockAdjustmentForm').on('submit', event => { event.preventDefault(); submitted++; }); document.querySelector('[name=remarks]').value = 'Prepare'; document.getElementById('stockAdjustmentForm').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })); });
  assert.equal(await page.evaluate(() => submitted), 0);
  assert.equal(await page.locator('#adj-recipe-confirm').getAttribute('aria-invalid'), 'true');
  assert((await page.locator('[data-adjustment-error]').innerText()).includes('confirm'));
  await page.locator('#adj-recipe-confirm').check();
  await page.evaluate(() => document.getElementById('stockAdjustmentForm').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })));
  assert.equal(await page.evaluate(() => submitted), 1);
  await page.locator('[name=quantity]').fill('1.5');
  await page.evaluate(() => document.getElementById('stockAdjustmentForm').dispatchEvent(new Event('submit', { bubbles: true, cancelable: true })));
  assert.equal(await page.evaluate(() => submitted), 1);
  assert.equal(await page.locator('[name=quantity]').getAttribute('aria-invalid'), 'true');
  assert((await page.locator('[data-adjustment-error]').innerText()).includes('whole'));
  await page.locator('[name=quantity]').fill('10');
  await page.evaluate(() => $('#stockAdjustmentForm').trigger('adjustment:error', ['Bun: need 20.00 pc, available 0.00.', 'quantity']));
  assert.equal(await page.locator('[data-adjustment-error]').innerText(), 'Bun: need 20.00 pc, available 0.00.');
  assert.equal(await page.evaluate(() => document.activeElement.name), 'quantity');
  await page.locator('[name=quantity]').fill('5');
  assert.equal(await page.locator('[data-adjustment-error]').count(), 0);
  await page.evaluate(() => $('#adjTypeSelect').val('waste').trigger('change'));
  assert((await page.locator('#adj-recipe-message').innerText()).includes('not returned'));
  assert.equal(await page.locator('#adj-recipe-confirm').isDisabled(), true);
  await page.evaluate(() => { fixture.product.type = 'simple'; $('#stockAdjustmentModal').trigger('shown.bs.modal'); });
  await page.waitForFunction(() => !$('#stockAdjustmentForm button[type=submit]').prop('disabled'));
  await page.evaluate(() => $('#adjTypeSelect').val('stock_in').trigger('change'));
  assert.equal(await page.locator('#adj-recipe-panel').isVisible(), false);
  assert.equal(await page.locator('[name=quantity]').getAttribute('step'), '0.0001');
  assert.equal(await page.locator('[name=batch_number]').isDisabled(), false);
  assert.deepEqual(errors, []);
  console.log('Recipe adjustment preview, confirmation, validation, reductions and ordinary-stock controls passed.');
 } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exit(1); });
