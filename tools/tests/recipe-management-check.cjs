const assert = require('node:assert/strict');
const fs = require('node:fs');
const { chromium } = require('C:/Users/MIS-03/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');

(async () => {
    const browser = await chromium.launch({ executablePath:'C:/Users/MIS-03/AppData/Local/ms-playwright/chromium_headless_shell-1248/chrome-headless-shell-win64/chrome-headless-shell.exe',headless:true });
    try {
        const page = await browser.newPage();
        const errors=[];page.on('pageerror',error=>errors.push(error.message));
        const html=fs.readFileSync('scratch/recipe-management-rendered.html','utf8');
        const section=html.match(/<section id="recipe-management">[\s\S]*?<\/section>/)[0];
        await page.setContent(section);
        await page.addScriptTag({path:'assets/js/recipe-management.js'});
        const ids=await page.evaluate(()=>{
            const data=JSON.parse(document.getElementById('recipe-definition-data').textContent);
            return {weight:data.ingredients.find(item=>item.name==='Lettuce').id,volume:data.ingredients.find(item=>item.name==='Sauce').id};
        });
        assert.equal(await page.locator('.definition-row').count(),1);
        await page.locator('.definition-ingredient').selectOption(String(ids.weight));
        await page.locator('.definition-unit').selectOption('kg');
        await page.locator('.definition-quantity').fill('0.025');
        await page.locator('#recipe-yield-preview').fill('4');
        assert((await page.locator('#recipe-definition-preview').innerText()).includes('0.1 kg'));
        assert((await page.locator('#recipe-definition-preview').innerText()).includes('₱100.00'));
        assert.equal(await page.locator('.definition-quantity').inputValue(),'0.025');
        await page.locator('#recipe-add-row').click();
        await page.locator('.definition-ingredient').nth(1).selectOption(String(ids.volume));
        await page.locator('.definition-quantity').nth(1).fill('20');
        assert((await page.locator('#recipe-definition-preview').innerText()).includes('80 ml'));
        await page.locator('.definition-remove').nth(1).click();
        assert.equal(await page.locator('.definition-row').count(),1);
        assert(!(await page.locator('#recipe-definition-preview').innerText()).includes('Sauce'));
        await page.locator('#recipe-yield-preview').fill('1.5');
        assert((await page.locator('#recipe-definition-preview').innerText()).includes('whole number'));
        await page.locator('#recipe-search').fill('Iced tea reusable');
        assert.equal(await page.locator('[data-recipe-name]:visible').count(),1);
        await page.locator('#recipe-search').fill('not a recipe name');
        assert.equal(await page.locator('[data-recipe-name]:visible').count(),0);
        assert.equal(await page.locator('#recipe-search-empty').isVisible(),true);
        assert.equal(await page.locator('[name="ingredients[0][quantity]"]').inputValue(),'0.025');
        assert.deepEqual(errors,[]);
        console.log('PASS: rendered recipe page, dynamic ingredient rows, weight/volume units, scaled quantities/costs, removal, search and inventory-neutral preview.');

        const globalHtml=fs.readFileSync('scratch/recipe-management-global-rendered.html','utf8');
        await page.setContent(globalHtml.match(/<section id="recipe-management">[\s\S]*?<\/section>/)[0]);
        await page.addScriptTag({path:'assets/js/recipe-management.js'});
        assert.equal(await page.locator('.definition-ingredient option').count(),1);
        await page.locator('#recipe-branch').selectOption('1');
        assert((await page.locator('.definition-ingredient').innerText()).includes('Shared ingredient'));
        assert(!(await page.locator('.definition-ingredient').innerText()).includes('Branch two ingredient'));
        const globalIds=await page.evaluate(()=>{
            const items=JSON.parse(document.getElementById('recipe-definition-data').textContent).ingredients;
            return {first:items.find(item=>item.name==='Lettuce').id,shared:items.find(item=>item.name==='Shared ingredient').id};
        });
        await page.locator('.definition-ingredient').selectOption(String(globalIds.first));
        await page.locator('.definition-quantity').fill('10');
        await page.locator('#recipe-branch').selectOption('2');
        assert.equal(await page.locator('.definition-ingredient').inputValue(),'');
        assert(!(await page.locator('.definition-ingredient').innerText()).includes('Lettuce'));
        assert((await page.locator('.definition-ingredient').innerText()).includes('Branch two ingredient'));
        await page.locator('#recipe-branch').selectOption('global');
        assert.equal(await page.locator('.definition-ingredient option').count(),2);
        assert((await page.locator('.definition-ingredient').innerText()).includes('Shared ingredient'));
        await page.locator('.definition-ingredient').selectOption(String(globalIds.shared));
        await page.locator('#recipe-branch').selectOption('1');
        assert.equal(await page.locator('.definition-ingredient').inputValue(),String(globalIds.shared));
        await page.locator('#recipe-add-row').click();
        assert(!(await page.locator('.definition-ingredient').nth(1).innerText()).includes('Branch two ingredient'));
        await page.locator('#recipe-branch').selectOption('');
        assert.equal(await page.locator('.definition-ingredient option').count(),2);
        assert.deepEqual(errors,[]);
        console.log('PASS: ingredient lists follow the selected branch, shared ingredients work in every branch, global scope excludes branch-only ingredients, and changing branch clears incompatible selections.');

        await page.setContent('<meta name="csrf-token" content="test"><div id="createProductModal"><form><input name="csrf_token" value="test"><select id="create_recipe_source"><option value="">Custom</option><option value="1">Iced tea</option><option value="2">Pasta</option></select><p id="create_recipe_source_feedback"></p><button type="submit">Save</button></form></div>');
        await page.addScriptTag({path:'assets/admin/libs/jquery/dist/jquery.min.js'});
        await page.evaluate(()=>{
            window.pending=[];window.imported=[];
            $.post=()=>{const deferred=$.Deferred();pending.push(deferred);return deferred.promise();};
            // Match jQuery's success-callback behavior while retaining manual completion order.
            const original=$.post;$.post=(url,data,success)=>{const promise=original();promise.done(success);return promise;};
            $(document).on('recipe:import',(event,rows)=>imported.push(rows));
        });
        await page.addScriptTag({path:'assets/js/recipe-admin.js'});
        await page.evaluate(()=>new Promise(resolve=>$(resolve)));
        await page.locator('#create_recipe_source').selectOption('1');
        assert.equal(await page.locator('[type=submit]').isDisabled(),true);
        await page.locator('#create_recipe_source').selectOption('2');
        await page.evaluate(()=>pending[0].resolve({success:true,recipe:[{ingredient_product_id:1,quantity:'10',unit:'ml'}]}));
        assert.equal(await page.evaluate(()=>imported.length),0);
        await page.evaluate(()=>pending[1].resolve({success:true,recipe:[{ingredient_product_id:2,quantity:'100',unit:'g'}]}));
        assert.equal(await page.evaluate(()=>imported[0][0].ingredient_product_id),2);
        assert.equal(await page.locator('[type=submit]').isDisabled(),false);
        await page.locator('#create_recipe_source').selectOption('1');
        await page.evaluate(()=>{pending[2].reject();});
        assert.equal(await page.locator('#create_recipe_source').inputValue(),'');
        assert.equal(await page.locator('[type=submit]').isDisabled(),false);
        assert((await page.locator('#create_recipe_source_feedback').innerText()).includes('retry'));
        assert.deepEqual(errors,[]);
        console.log('PASS: saved-recipe import, submit lock during loading, stale-response protection, copy event, and failure/retry recovery.');
    } finally { await browser.close(); }
})().catch(error=>{console.error(error);process.exitCode=1;});
