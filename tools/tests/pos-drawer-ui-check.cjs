const assert=require('node:assert/strict'),fs=require('node:fs');
const {chromium}=require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{
 const browser=await chromium.launch({executablePath:'C:/Users/MIS-03/AppData/Local/ms-playwright/chromium_headless_shell-1248/chrome-headless-shell-win64/chrome-headless-shell.exe',headless:true});
 try{
  const markup=fs.readFileSync('scratch/pos-drawer-rendered.html','utf8').replace(/<link\b[^>]*>/g,'').replace(/<script\b[^>]*>[\s\S]*?<\/script>/g,'');
  const html='<html><head><meta charset="utf-8"><meta name="csrf-token" content="test-csrf"><style>'+fs.readFileSync('assets/css/pos-drawer.css','utf8')+'</style></head><body>'+markup+'<button id="fake-pos-action">POS product</button><script>'+fs.readFileSync('assets/js/pos-drawer.js','utf8')+'</script></body></html>';
  let shift=null,delayStatus=false,failStatus=false,scheduleAllowed=true,noRegisters=true;
  let rows=[],previous=null;
  const permissions={open_cash_shift:true,close_cash_shift:true,cash_drawer_movements:true,lock_cash_drawer:true};
  const snapshot=()=>{const s=shift?JSON.parse(JSON.stringify(shift)):null;if(s&&s.locked)delete s.totals;return {shift:s,permissions,schedule_allowed:scheduleAllowed,schedule_message:'Your cashier schedule has ended.',registers:shift||noRegisters?[]:[{id:1,name:'Register A',previous_cash:previous}]};};
  const page=await browser.newPage({viewport:{width:1280,height:900}}),errors=[];page.on('pageerror',e=>errors.push(e.message));page.setDefaultTimeout(5000);
  await page.route('**/*',async route=>{
   const req=route.request();
   if(req.method()==='GET')return route.fulfill({contentType:'text/html',body:html});
   const body=req.postData()||'',field=name=>{const m=body.match(new RegExp('name="'+name+'"\\r\\n\\r\\n([^\\r]*)'));return m?m[1]:'';};
   const action=field('action');let result;
   if(failStatus)result={success:false,message:'Connection unavailable. Please retry.'};
   else if(action==='open'){
    assert.equal(field('amount'),'100');assert.equal(field('register_id'),'1');
    shift={id:1,branch_id:1,branch_name:'Test branch',register_name:'Register A',opened_at:'2026-10-16 08:00:00',locked:false,matches_branch:true,totals:{opening_cents:10000,cash_sales_cents:0,electronic_sales_cents:0,expected_cents:10000}};
   }else if(action==='lock')shift.locked=true;
   else if(action==='unlock'){if(field('secret')==='1234')shift.locked=false;else result={success:false,message:'Incorrect PIN or password. The drawer remains locked.'};}
   else if(action==='move'){assert.equal(field('kind'),'cash_out');assert.equal(field('amount'),'5');shift.totals.expected_cents-=500;}
   else if(action==='close'){
    if(!field('notes'))result={success:false,message:'Explain the overage or shortage before closing.'};
    else{const closing={id:1,expected_cash:'105.00',counted_cash:'104.00',difference:'-1.00',status:'pending_review',register_name:'Register A',branch_name:'Test branch',closed_at:'2026-10-16 16:00:00',close_notes:field('notes')};previous={shift_id:1,amount:'104.00',closed_at:closing.closed_at};shift=null;result={success:true,state:snapshot(),closing};}
   }
   if(!result){result={success:true,state:snapshot()};if(action==='transactions')result.state.transactions=rows;}
   if(action==='status'&&delayStatus){delayStatus=false;await new Promise(r=>setTimeout(r,650));}
   return route.fulfill({contentType:'application/json',body:JSON.stringify(result)});
  });
  await page.goto('http://pos.test/pos/?route=pos');
  await page.getByRole('heading',{name:'Open your POS drawer'}).waitFor();assert(await page.locator('#pos-drawer-dialog').evaluate(e=>e.open));
  await page.getByRole('heading',{name:'No register is available',exact:true}).waitFor();assert.equal(await page.locator('[data-drawer-action="open"]').isVisible(),false);await page.screenshot({path:'scratch/pos-register-unavailable-desktop.png'});
  await page.setViewportSize({width:390,height:844});assert(await page.locator('#pos-drawer-dialog').evaluate(e=>e.scrollWidth<=e.clientWidth));await page.screenshot({path:'scratch/pos-register-unavailable-mobile.png'});await page.setViewportSize({width:1280,height:900});
  noRegisters=false;await page.getByRole('button',{name:'Check availability',exact:true}).click();await page.locator('[data-drawer-action="open"]').waitFor({state:'visible'});assert.equal(await page.locator('#pos-drawer-unavailable').isVisible(),false);
  await page.locator('#pos-drawer-register').selectOption('1');await page.locator('[data-drawer-action="open"] [name="amount"]').fill('100');await page.getByRole('button',{name:'Open drawer & start shift'}).click();
  await page.getByRole('heading',{name:'Your active drawer'}).waitFor();await page.getByRole('button',{name:'Continue to POS'}).click();assert.equal(await page.locator('#pos-drawer-dialog').evaluate(e=>e.open),false);
  shift.totals.cash_sales_cents=1000;shift.totals.expected_cents=11000;rows=[{created_at:'08:10',sale_id:5,kind:'cash_sale',payment_method:'Cash',amount:'10.00',reason:'<img src=x onerror="window.drawerXss=true">'}];
  await page.locator('#pos-drawer-bar [data-drawer-view="transactions"]').click();await page.locator('#pos-drawer-transactions td').first().waitFor();assert((await page.locator('#pos-drawer-transactions').innerText()).includes('Sale #5'));assert.equal(await page.locator('#pos-drawer-transactions img').count(),0);
  await page.locator('#pos-drawer-tabs [data-drawer-view="move"]').click();await page.locator('[data-drawer-action="move"] [name="kind"]').selectOption('cash_out');await page.locator('[data-drawer-action="move"] [name="amount"]').fill('5');await page.locator('[data-drawer-action="move"] [name="notes"]').fill('Safe drop');await page.getByRole('button',{name:'Record cash movement'}).click();await page.getByRole('heading',{name:'Your active drawer'}).waitFor();
  assert((await page.locator('[data-drawer-section="overview"]').innerText()).includes('105.00'));await page.getByRole('button',{name:'Continue to POS'}).click();
  delayStatus=true;await page.evaluate(()=>document.dispatchEvent(new Event('visibilitychange')));await page.locator('#pos-drawer-lock').click();await page.getByRole('heading',{name:'Drawer locked',exact:true}).waitFor();await page.waitForTimeout(750);await page.keyboard.press('Escape');assert(await page.locator('#pos-drawer-dialog').evaluate(e=>e.open));assert(await page.getByRole('heading',{name:'Drawer locked',exact:true}).isVisible());
  await page.locator('[data-drawer-action="unlock"] [name="secret"]').fill('wrong');await page.getByRole('button',{name:'Unlock drawer',exact:true}).click();await page.locator('#pos-drawer-error').filter({hasText:'Incorrect PIN'}).waitFor();assert.equal(await page.locator('[data-drawer-action="unlock"] [name="secret"]').inputValue(),'');
  await page.locator('[data-drawer-action="unlock"] [name="secret"]').fill('1234');await page.getByRole('button',{name:'Unlock drawer',exact:true}).click();await page.getByRole('heading',{name:'Your active drawer'}).waitFor();
  scheduleAllowed=false;await page.evaluate(()=>document.dispatchEvent(new Event('visibilitychange')));await page.getByRole('heading',{name:'Count & close POS',exact:true}).waitFor();await page.keyboard.press('Escape');assert(await page.locator('#pos-drawer-dialog').evaluate(e=>e.open));assert(await page.locator('[data-drawer-action="close"]').isVisible());
  scheduleAllowed=true;await page.evaluate(()=>document.dispatchEvent(new Event('visibilitychange')));await page.waitForTimeout(100);
  await page.locator('#pos-drawer-tabs [data-drawer-view="close"]').click();await page.locator('[data-drawer-action="close"] [name="amount"]').fill('104');await page.getByRole('button',{name:'Confirm count & close POS'}).click();await page.locator('#pos-drawer-error').filter({hasText:'Explain'}).waitFor();
  await page.locator('[data-drawer-action="close"] [name="notes"]').fill('One peso short');await page.getByRole('button',{name:'Confirm count & close POS'}).click();await page.getByRole('heading',{name:'Shift closing summary',exact:true}).waitFor();assert((await page.locator('#pos-drawer-closing-summary').innerText()).includes('-₱1.00'));await page.getByRole('button',{name:'Start another shift'}).click();await page.getByRole('heading',{name:'Open your POS drawer'}).waitFor();
  await page.locator('#pos-drawer-register').selectOption('1');
  assert((await page.locator('#pos-drawer-previous-amount').innerText()).includes('104.00'));
  await page.locator('#pos-drawer-opening-source').selectOption('previous');assert.equal(await page.locator('#pos-drawer-opening-amount').inputValue(),'104.00');assert(await page.locator('#pos-drawer-opening-amount').evaluate(e=>e.readOnly));
  assert.equal(await page.locator('#pos-drawer-cash-confirm input').evaluate(e=>e.checkValidity()),false);await page.locator('#pos-drawer-cash-confirm input').check();assert.equal(await page.locator('#pos-drawer-previous-shift').inputValue(),'1');
  await page.screenshot({path:'scratch/pos-next-shift-desktop.png'});
  await page.setViewportSize({width:390,height:844});assert(await page.locator('#pos-drawer-dialog').evaluate(e=>e.scrollWidth<=e.clientWidth));await page.screenshot({path:'scratch/pos-next-shift-mobile.png'});
  await page.locator('#pos-drawer-opening-source').selectOption('manual');assert.equal(await page.locator('#pos-drawer-opening-amount').inputValue(),'');assert.equal(await page.locator('#pos-drawer-opening-amount').evaluate(e=>e.readOnly),false);
  failStatus=true;await page.goto('http://pos.test/pos/?route=pos');await page.locator('#pos-drawer-error').filter({hasText:'Connection unavailable'}).waitFor();assert(await page.locator('#pos-drawer-dialog').evaluate(e=>e.open));
  assert.deepEqual(errors,[]);console.log('PASS: real-browser drawer opening, transactions, cash movement, lock gate, stale polling, PIN retry, discrepancy closing, next shift and network failure.');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
