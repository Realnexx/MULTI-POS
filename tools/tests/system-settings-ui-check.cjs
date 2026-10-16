const fs=require('fs'),assert=require('node:assert/strict');
const {chromium}=require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{
 const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 try{
  const page=await browser.newPage();await page.route('**/*',r=>r.abort());
  const css=['assets/admin/css/styles.min.css','assets/admin/css/custom.css','assets/css/admin-sidebar.css'].map(p=>fs.readFileSync(p,'utf8')).join('\n');
  for(const tab of ['financial','pos','security','backup']){
   let html=fs.readFileSync('scratch/settings-'+tab+'.html','utf8').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi,'').replace(/<link\b[^>]*>/gi,'');
   html=html.replace('</head>','<style>'+css+'.admin-skeleton{display:none!important}.page-content-area{opacity:1!important}</style></head>');
   await page.setContent(html,{waitUntil:'domcontentloaded'});
   for(const width of [320,390,768,1024,1440]){
    await page.setViewportSize({width,height:1000});
    assert(!(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)),tab+' overflows at '+width);
    assert(await page.locator('#tab-'+tab).isVisible());
    if(tab==='security')for(const input of await page.locator('#tab-security input[type=password]').all())assert.equal(await input.inputValue(),'');
    console.log('PASS '+tab+' '+width);
   }
   await page.screenshot({path:'scratch/settings-'+tab+'-desktop.png',fullPage:true});
  }
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
