const assert = require('node:assert/strict');
const { chromium } = require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');

(async () => {
    const browser = await chromium.launch({ executablePath: 'C:/Users/MIS-03/AppData/Local/ms-playwright/chromium_headless_shell-1248/chrome-headless-shell-win64/chrome-headless-shell.exe', headless: true });
    try {
        const page = await browser.newPage();
        await page.setContent('<meta name="csrf-token" content="test-token"><div class="modal show"><form method="post" action="http://localhost/save"><label>Name<input name="name" required></label><input name="image" type="file"><button type="submit">Save</button></form></div>');
        await page.evaluate(() => {
            window.messages = [];
            window.showToast = (message, type) => messages.push({ message, type });
            window.requests = [];
            window.fetch = async (url, options) => {
                requests.push({ name: options.body.get('name'), token: options.body.get('csrf_token'), header: options.headers['X-Modal-Form'] });
                return { ok: true, text: async () => JSON.stringify({ success: false, message: 'Add ingredients before saving a burger.' }) };
            };
            window.Swal = { fire: () => { throw new Error('Unexpected error dialog'); } };
        });
        await page.addScriptTag({ path: 'assets/js/modal-forms.js' });
        await page.click('button');
        assert.equal(await page.evaluate(() => requests.length), 0);
        assert.match(await page.evaluate(() => messages[0].message), /Name/);
        await page.fill('[name=name]', 'Burger');
        await page.setInputFiles('[name=image]', { name: 'burger.png', mimeType: 'image/png', buffer: Buffer.from('selected-image') });
        await page.click('button');
        await page.waitForFunction(() => messages.some(m => m.message.includes('Add ingredients')));
        assert.equal(await page.inputValue('[name=name]'), 'Burger');
        assert.equal(await page.locator('.modal.show').count(), 1);
        assert.equal(await page.locator('button').isEnabled(), true);
        assert.equal(await page.locator('[name=image]').evaluate(e => e.files[0].name), 'burger.png');
        assert.deepEqual(await page.evaluate(() => requests[0]), { name: 'Burger', token: 'test-token', header: '1' });
        await page.evaluate(() => {
            Swal.fire({ icon: 'error', text: 'Invalid quantity' });
            fetch = async () => ({ ok: false, text: async () => 'Upload a JPG or PNG image.' });
        });
        await page.click('button');
        await page.waitForFunction(() => messages.some(m => m.message === 'Upload a JPG or PNG image.'));
        assert.equal(await page.evaluate(() => messages.some(m => m.message === 'Invalid quantity' && m.type === 'error')), true);
        await page.evaluate(() => {
            document.querySelector('form').addEventListener('submit', e => e.preventDefault());
            fetch = async () => { throw new Error('Existing AJAX form was submitted twice'); };
        });
        const count = await page.evaluate(() => messages.length);
        await page.click('button');
        assert.equal(await page.evaluate(() => messages.length), count);
        console.log('PASS: validation toasts, retained modal/values/photo, server errors, retry buttons, CSRF and existing AJAX handlers.');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
