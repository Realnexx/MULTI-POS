const assert = require('node:assert/strict');
const base='http://127.0.0.1:8876';
let checks=0;
async function login(name, expected) {
 let r=await fetch(base+'/index.php?route=login');
 const cookie=r.headers.get('set-cookie').split(';')[0];
 const html=await r.text();
 assert(!/name="(?:role|branch)"/.test(html)); assert(!html.includes('admin123')); checks+=2;
 const token=html.match(/name="csrf_token" value="([^"]+)"/)[1];
 r=await fetch(base+'/index.php?route=login',{method:'POST',redirect:'manual',headers:{Cookie:cookie},body:new URLSearchParams({username:'rbac_'+name,password:'FixturePassword!42',csrf_token:token,role:'Superadmin',branch:'Test Branch B',branch_id:'502'})});
 assert.equal(r.status,302); assert.equal(r.headers.get('location').split('?')[1], 'route='+expected);checks+=2;
 const renewed=r.headers.get('set-cookie').split(';')[0];assert.notEqual(renewed,cookie);checks++;
 return renewed;
}
(async()=>{
 await login('admin','dashboard');await login('super','dashboard');await login('stock','admin/start');const cashier=await login('cashier','dashboard');await login('customer','home');
 let r=await fetch(base+'/index.php?route=admin/start',{headers:{Cookie:cashier}});let text=await r.text();assert.equal(r.status,200);assert(text.includes('Test Branch A'));assert(text.includes('Open POS'));checks+=3;
 r=await fetch(base+'/index.php?route=inventory/products&branch_id=502',{headers:{Cookie:'PHPSESSID=rbactestviewer'}});assert.equal(r.status,403);text=await r.text();assert(text.includes('access-card'));checks+=2;
 r=await fetch(base+'/ajax/inventory_action.php',{method:'POST',headers:{Cookie:'PHPSESSID=rbacteststock'},body:new URLSearchParams({action:'products_datatable',branch_id:'502',csrf_token:'rbac-fixture-token'})});assert.equal(r.status,403);assert.equal((await r.json()).success,false);checks+=2;
 r=await fetch(base+'/index.php?route=admin/expenses&branch_id=502',{headers:{Cookie:'PHPSESSID=rbactestadmin'}});assert.equal(r.status,200);checks++;
 r=await fetch(base+'/index.php?route=customer_login',{redirect:'manual'});assert.equal(r.status,302);assert(r.headers.get('location').endsWith('?route=login'));checks+=2;
 console.log('PASS '+checks+' unified sign-in, role/branch forgery, session renewal, routing and styled denial checks');
})();

