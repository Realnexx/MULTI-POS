const fs=require('fs'),assert=require('node:assert/strict');
const {chromium}=require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{
 const browser=await chromium.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true});
 try{
  const page=await browser.newPage();await page.route('**/*',r=>r.abort());
  const html=fs.readFileSync('scratch/demo-notice.html','utf8').replace(/<link\b[^>]*>/gi,'').replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi,'');
  const css=fs.readFileSync('assets/css/demo-notice.css','utf8'),js=fs.readFileSync('assets/js/demo-notice.js','utf8');
  for(const width of [320,390,768,1024,1440]){
   await page.setViewportSize({width,height:800});await page.setContent(html);await page.addStyleTag({content:css});await page.addScriptTag({content:js});
   const dialog=page.locator('#demo-account-dialog');assert(await dialog.isVisible());assert(await dialog.evaluate(el=>el.open));
   assert(!(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth)),'Page overflow at '+width);
   const box=await dialog.boundingBox();assert(box.x>=0&&box.x+box.width<=width&&box.height<=800,'Dialog outside viewport');
   await page.getByRole('button',{name:'I understand'}).click();assert(!(await dialog.isVisible()));assert(await page.locator('.demo-account-banner').isVisible());
   await page.getByRole('button',{name:'View demo details'}).click();assert(await dialog.isVisible());await page.keyboard.press('Escape');assert(!(await dialog.isVisible()));
   console.log('PASS demo notice '+width);
  }
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
