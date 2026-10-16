const assert=require('node:assert/strict'),fs=require('node:fs');
const {chromium}=require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{
    const browser=await chromium.launch({executablePath:'C:/Users/MIS-03/AppData/Local/ms-playwright/chromium_headless_shell-1248/chrome-headless-shell-win64/chrome-headless-shell.exe',headless:true});
    try {
        const page=await browser.newPage();const errors=[];page.on('pageerror',error=>errors.push(error.message));
        await page.route('**/*',route=>route.abort());
        const html=fs.readFileSync('scratch/product-transfers-rendered.html','utf8');
        const modals=await page.evaluate(html=>{const doc=new DOMParser().parseFromString(html,'text/html');return ['createProductModal','editProductModal','transferProductModal'].map(id=>{const modal=doc.getElementById(id);modal.querySelectorAll('script,img,iframe,link').forEach(node=>node.remove());return modal.outerHTML;}).join('');},html);
        await page.setContent('<meta name="csrf-token" content="test-token">'+modals+'<button class="transfer-product-btn" data-id="123" data-source-branch="1">Transfer Branch</button>',{waitUntil:'domcontentloaded'});
        await page.addScriptTag({path:'assets/admin/libs/jquery/dist/jquery.min.js'});
        await page.evaluate(()=>{
            window.shown=false;window.bootstrap={Modal:{getOrCreateInstance:()=>({show(){shown=true;}})}};
            window.pending=[];window.requests=[];
            $.ajax=options=>{requests.push(Object.fromEntries(options.data.entries()));const deferred=$.Deferred();pending.push(deferred);return deferred.promise();};
        });
        await page.addScriptTag({path:'assets/js/product-branches.js'});
        await page.evaluate(()=>new Promise(resolve=>$(resolve)));
        await page.evaluate(()=>$('#createProductModal').trigger('shown.bs.modal'));
        assert.deepEqual(await page.locator('#createProductModal [data-stock-summary]').evaluateAll(fields=>fields.map(field=>field.value)),['0.00','10.00','100.00']);
        await page.locator('#edit_prod_stock').evaluate(input=>{input.value='1.2345';});
        await page.locator('#edit_prod_thresh').evaluate(input=>{input.value='10.1';});
        await page.locator('#edit_prod_max_thresh').evaluate(input=>{input.value='100.239';});
        await page.evaluate(()=>$('#editProductModal').trigger('shown.bs.modal'));
        assert.deepEqual(await page.locator('#editProductModal [data-stock-summary]').evaluateAll(fields=>fields.map(field=>field.value)),['1.23','10.10','100.24']);
        await page.locator('#createProductModal [name=low_stock_threshold]').evaluate(input=>{input.value='12.5';$(input).trigger('blur');});
        assert.equal(await page.locator('#createProductModal [name=low_stock_threshold]').inputValue(),'12.50');
        await page.locator('.transfer-product-btn').click();
        assert.equal(await page.evaluate(()=>shown),true);
        assert.equal(await page.locator('#transfer-destination option[value="1"]').isDisabled(),true);
        // The fixture does not load Bootstrap CSS; make the modal form visible for interaction.
        await page.locator('#transferProductModal').evaluate(modal=>{modal.style.display='block';modal.classList.add('show');});
        await page.locator('#transfer-destination').selectOption('2');
        await page.locator('#transfer-product-form [type=submit]').click();
        assert.equal(await page.locator('#transfer-product-form [type=submit]').isDisabled(),true);
        const request=await page.evaluate(()=>requests[0]);
        assert.equal(request.product_id,'123');assert.equal(request.source_branch_id,'1');assert.equal(request.destination_branch_id,'2');assert.equal(request.csrf_token,'test-token');assert.match(request.request_key,/^[a-f0-9]{32}$/);
        await page.evaluate(()=>{pending[0].reject();});
        assert((await page.locator('#transfer-product-feedback').innerText()).includes('Retry this same transfer'));
        await page.locator('#transfer-product-form [type=submit]').click();
        assert.equal(await page.evaluate(()=>requests[1].request_key),request.request_key);
        await page.evaluate(()=>{pending[1].resolve({success:false,message:'This ingredient is used by another branch recipe.'});});
        assert((await page.locator('#transfer-product-feedback').innerText()).includes('another branch recipe'));
        assert.equal(await page.locator('#transfer-product-form [type=submit]').isDisabled(),false);
        assert.deepEqual(errors,[]);
        console.log('PASS: actual add/edit modal two-decimal stock and threshold values, transfer modal, destination filtering, CSRF, loading state, safe retry token and server error feedback.');
    } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1;});
