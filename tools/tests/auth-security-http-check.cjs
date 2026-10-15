const assert=require('node:assert/strict'),fs=require('node:fs');const base='http://127.0.0.1:8877/pos/';let n=0;
class Client{constructor(cookie=''){this.cookie=cookie;}async request(route,data){const r=await fetch(base+'?route='+route,{method:data?'POST':'GET',redirect:'manual',headers:this.cookie?{Cookie:this.cookie}:{},body:data?new URLSearchParams(data):undefined});const cookie=r.headers.get('set-cookie');if(cookie)this.cookie=cookie.split(';')[0];const text=await r.text();return {r,text};}async token(route){const {r,text}=await this.request(route);assert.equal(r.status,200);return text.match(/name="csrf_token" value="([^"]+)"/)[1];}}
(async()=>{
 const c=new Client();let token=await c.token('register');let x=await c.request('register',{csrf_token:token,username:'x',email:'bad',password:'short',confirm_password:'short'});assert.equal(x.r.status,422);n++;
 x=await c.request('register',{csrf_token:token,username:'newcustomer',email:'not-email',password:'CustomerPassword!42',confirm_password:'CustomerPassword!42'});assert.equal(x.r.status,422);n++;
 x=await c.request('register',{csrf_token:token,username:'newcustomer',email:'newcustomer@tests.invalid',password:'short',confirm_password:'short'});assert.equal(x.r.status,422);n++;
 x=await c.request('register',{csrf_token:token,username:'newcustomer',email:'newcustomer@tests.invalid',password:'CustomerPassword!42',confirm_password:'CustomerPassword!42','roles[]':'35',branch_id:'502'});assert.equal(x.r.status,302);assert(x.r.headers.get('location').includes('setup_profile'));n+=2;
 x=await c.request('dashboard');assert.equal(x.r.status,403);n++;
 for(const who of ['idle','absolute','revoked']){const client=new Client('PHPSESSID=rbactest'+who);x=await client.request('dashboard');assert.equal(x.r.status,302,who);assert(x.r.headers.get('location').endsWith('?route=login'));n+=2;}
 const forgot=new Client();token=await forgot.token('forgot_password');x=await forgot.request('forgot_password',{csrf_token:token,email:'customer@rbac.invalid'});assert.equal(x.r.status,302);const known=(await forgot.request('forgot_password')).text;
 x=await forgot.request('forgot_password',{csrf_token:token,email:'unknown@rbac.invalid'});assert.equal(x.r.status,302);const unknown=(await forgot.request('forgot_password')).text;assert(known.includes('If an account uses that email'));assert(unknown.includes('If an account uses that email'));n+=4;
 const capture=fs.readFileSync('scratch/auth-smtp-capture.jsonl','utf8').trim().split('\n').map(JSON.parse);assert.equal(capture.length,1);const resetToken=capture[0].tokens[0];assert(resetToken);n+=2;
 const reset=new Client();const route='reset_password&token='+resetToken;token=await reset.token(route);x=await reset.request(route,{csrf_token:token,password:'NewCustomerPassword!42',confirm_password:'NewCustomerPassword!42'});assert.equal(x.r.status,302);assert(x.r.headers.get('location').endsWith('?route=login'));n+=2;
 x=await reset.request(route);assert(x.text.includes('already used'));n++;
 x=await new Client('PHPSESSID=rbactestcustomer').request('home');assert.equal(x.r.status,302);n++;
 token=await reset.token('login');x=await reset.request('login',{csrf_token:token,username:'rbac_customer',password:'FixturePassword!42'});assert.equal(x.r.status,422);n++;
 x=await reset.request('login',{csrf_token:token,username:'rbac_customer',password:'NewCustomerPassword!42'});assert.equal(x.r.status,302);assert(x.r.headers.get('location').endsWith('?route=home'));n+=2;
 // Fresh cookies cannot reset an account's failed attempts; username/email share its bucket.
 for(let i=0;i<5;i++){const attacker=new Client();const csrf=await attacker.token('login');x=await attacker.request('login',{csrf_token:csrf,username:i%2?'admin@rbac.invalid':'rbac_admin',password:'WrongPassword!42'});assert.equal(x.r.status,422);n++;}
 const blocked=new Client();token=await blocked.token('login');x=await blocked.request('login',{csrf_token:token,username:'rbac_admin',password:'FixturePassword!42'});assert.equal(x.r.status,429);assert(Number(x.r.headers.get('retry-after'))>0);n+=2;
 const noCsrf=new Client();x=await noCsrf.request('forgot_password',{email:'customer@rbac.invalid'});assert.equal(x.r.status,419);n++;
 const page=await new Client().request('login');assert(page.text.includes('rel="icon" href="/pos/assets/img/default.png"'));assert(page.r.headers.get('cache-control').includes('no-store'));assert.equal(page.r.headers.get('referrer-policy'),'no-referrer');n+=3;
 console.log('PASS '+n+' registration, CSRF, database favicon, cookie-independent/alias-aware throttling, idle/absolute/revoked sessions, local PHPMailer delivery, generic recovery responses, single-use reset and new-password login checks');
})();
