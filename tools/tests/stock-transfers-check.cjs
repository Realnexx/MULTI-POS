const assert=require('node:assert/strict'),fs=require('node:fs');

const {chromium}=require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');

(async()=>{

 const browser=await chromium.launch({executablePath:'C:/Users/MIS-03/AppData/Local/ms-playwright/chromium_headless_shell-1248/chrome-headless-shell-win64/chrome-headless-shell.exe',headless:true});

 try {

  const page=await browser.newPage({viewport:{width:1440,height:1000}}),errors=[];page.on('pageerror',error=>errors.push(error.message));

  async function load(mode){

   const markup=await page.evaluate(html=>{const doc=new DOMParser().parseFromString(html,'text/html');doc.querySelectorAll('script:not([type="application/json"]),link,img,iframe').forEach(node=>node.remove());return doc.body.innerHTML;},fs.readFileSync('scratch/'+mode+'.html','utf8'));

   await page.setContent('<meta name="csrf-token" content="test-csrf">'+markup,{waitUntil:'domcontentloaded'});

   await page.addStyleTag({path:'assets/admin/libs/bootstrap/dist/css/bootstrap.min.css'});

   await page.addScriptTag({path:'assets/admin/libs/jquery/dist/jquery.min.js'});

   await page.evaluate(()=>{window.requests=[];window.pending=[];$.ajax=options=>{const deferred=$.Deferred();requests.push(Object.fromEntries(options.data.entries()));pending.push(deferred);return deferred.promise();};});

   await page.addScriptTag({path:'assets/js/stock-transfers.js'});

   await page.waitForFunction(()=>document.querySelector('[data-transfer-row]'));

  }

  await load('render_transfers');

  await page.evaluate(()=>document.querySelector('details').open=true);

  assert.equal(await page.locator('#transfer-source-branches option').count(),1);

  await page.locator('#stock-transfer-request [type=submit]').click();

  assert.equal(await page.locator('#transfer-target-branch').getAttribute('aria-invalid'),'true');

  assert.equal(await page.evaluate(()=>requests.length),0);

  await page.locator('#transfer-target-branch').fill('Other branch');
  assert.equal(await page.locator('#transfer-source-branch').inputValue(),'Test branch');
  assert.equal(await page.locator('#transfer-source-branches option').first().getAttribute('value'),'Test branch');
  assert((await page.locator('[data-source-list] option').allTextContents()).every(text=>!text.includes('(#')));
  assert(await page.locator('[data-source-list] option').evaluateAll(options=>options.some(option=>option.label.includes('Not available at Other branch'))));
  assert.equal(await page.locator('#transfer-target-branch').evaluate(field=>field.tagName),'INPUT');
  assert.equal(await page.locator('[data-source-item]').evaluate(field=>field.tagName),'INPUT');
  await page.locator('[data-source-item]').fill('Unknown product');
  await page.locator('#stock-transfer-request [type=submit]').click();
  assert.equal(await page.locator('[data-source-item]').getAttribute('aria-invalid'),'true');
  await page.waitForFunction(()=>getComputedStyle(document.querySelector('[data-source-item]')).borderTopColor==='rgb(220, 165, 45)');
  assert.equal(await page.evaluate(()=>requests.length),0);

  const products=await page.evaluate(()=>JSON.parse(document.getElementById('stock-transfer-products').textContent));

  const source=products.find(p=>p.name==='Workflow ingredient source' && String(p.branch_id)==='1'),destination=products.find(p=>p.name==='Workflow ingredient source' && String(p.branch_id)==='2');

  await page.locator('[data-source-item]').fill(source.name+' · '+source.unit_type);

  assert.equal(await page.locator('[data-destination-item]').inputValue(),destination.name+' · '+destination.unit_type);
  assert.equal(await page.locator('[data-target-list] option').count(),1);
  assert.equal(await page.locator('[data-destination-id]').inputValue(),String(destination.id));
  assert((await page.locator('[data-match-hint]').innerText()).includes('Available at Other branch: 12.75 g'));
  assert.equal(await page.locator('[data-match-hint]').evaluate(field=>field.classList.contains('text-success')),true);
  assert((await page.locator('[data-source-list] option').evaluateAll(options=>options.map(option=>option.label))).some(label=>label.includes('✓ Available at Other branch: 12.75 g') && label.includes('Source: 5.50 g')));

  await page.locator('#stock-transfer-request [type=number]').fill('1.25');
  await page.locator('.transfer-request-card').screenshot({path:'scratch/stock-transfer-request-preview.png'});

  await page.locator('#stock-transfer-add-item').click();assert.equal(await page.locator('[data-transfer-row]').count(),2);

  await page.locator('[data-transfer-row]').last().locator('button').click();

  await page.locator('#stock-transfer-request [type=submit]').click();

  assert.equal(await page.locator('#stock-transfer-request [type=submit]').isDisabled(),true);

  assert((await page.locator('#stock-transfer-request [type=submit]').innerText()).includes('Saving'));

  await page.evaluate(()=>document.getElementById('stock-transfer-request').dispatchEvent(new Event('submit',{bubbles:true,cancelable:true})));

  assert.equal(await page.evaluate(()=>requests.length),1);

  assert.equal(await page.evaluate(()=>requests[0].csrf_token),'test-csrf');

  await page.evaluate(()=>pending.shift().resolve({success:false,message:'Test stock transfer validation'}));

  assert.equal(await page.locator('#stock-transfer-request [data-transfer-feedback]').innerText(),'Test stock transfer validation');

  assert.equal(await page.locator('#stock-transfer-request [type=number]').inputValue(),'1.25');

  await page.locator('#stock-transfer-request [type=submit]').click();

  assert.equal(await page.evaluate(()=>requests[0].request_key===requests[1].request_key),true);

  await page.evaluate(()=>pending.shift().resolve({success:false,message:'Retryable'}));

  await page.locator('#stock-transfer-request [type=number]').fill('2');

  assert.equal(await page.evaluate(()=>document.querySelector('#stock-transfer-request [name=request_key]').value===requests[1].request_key),false);

  await page.locator('#stock-transfer-filter').selectOption('discrepancy');

  assert(await page.locator('#stock-transfer-list tr:visible').count()>0);

  assert((await page.locator('#stock-transfer-list tr:visible').allTextContents()).every(text=>text.includes('completed discrepancy')));

  await load('render_transfers_receiver');

  const receive=page.locator('form[data-transfer-form]').filter({has:page.locator('input[value=stock_transfer_receive]')});

  assert.equal(await receive.count(),1);

  assert.equal(await page.locator('input[value=stock_transfer_dispatch]').count(),0);

  await receive.locator('[type=submit]').click();assert.equal(await receive.locator('[type=number]').getAttribute('aria-invalid'),'true');

  await receive.locator('[type=number]').fill('0.5');await receive.locator('[type=submit]').click();

  assert.equal(await receive.locator('[data-dispatched]').getAttribute('aria-invalid'),'true');

  await receive.locator('[data-dispatched]').fill('Damaged on arrival');await receive.locator('[type=submit]').click();

  assert.equal(await page.evaluate(()=>requests[0].action),'stock_transfer_receive');

  assert.equal(await page.evaluate(()=>requests.length),1);

  await load('render_transfers_admin');

  assert.equal(await page.locator('input[value=stock_transfer_approve]').count(),1);

  assert.equal(await page.locator('input[value=stock_transfer_reject]').count(),1);

  assert.equal(await page.locator('input[value=stock_transfer_receive]').count(),0);

  assert.deepEqual(errors,[]);

  console.log('Stock transfer request, branch filtering, inline validation, rows, retry keys, saving state, status filters, approval and receiving UI passed.');

 }finally{await browser.close();}

})().catch(error=>{console.error(error);process.exit(1);});

