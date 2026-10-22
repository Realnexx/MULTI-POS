(function () {
    'use strict';
    const root = document.getElementById('recipe-management');
    if (!root) return;
    const data = JSON.parse(document.getElementById('recipe-definition-data').textContent);
    const rows = document.getElementById('recipe-definition-rows');
    const preview = document.getElementById('recipe-definition-preview');
    const countInput = document.getElementById('recipe-yield-preview');
    const branch = document.getElementById('recipe-branch');
    const nameInput=document.getElementById('recipe-name'),suggestions=document.getElementById('recipe-product-suggestions');
    if(nameInput&&suggestions){
        let suggestionTimer,request=null,generation=0,active=-1;
        const suggestionStatus=document.createElement('p');suggestionStatus.className='recipe-name-hint';suggestionStatus.setAttribute('role','status');suggestionStatus.hidden=true;suggestions.after(suggestionStatus);
        function closeSuggestions(){suggestions.hidden=true;nameInput.setAttribute('aria-expanded','false');nameInput.removeAttribute('aria-activedescendant');active=-1;}
        function highlight(){Array.from(suggestions.children).forEach((option,index)=>{option.setAttribute('aria-selected',index===active?'true':'false');});if(active>=0){const option=suggestions.children[active];nameInput.setAttribute('aria-activedescendant',option.id);option.scrollIntoView({block:'nearest'});}}
        function refreshSuggestions(){
            clearTimeout(suggestionTimer);if(request)request.abort();const serial=++generation;suggestions.replaceChildren();closeSuggestions();suggestionStatus.hidden=false;suggestionStatus.dataset.state='loading';suggestionStatus.textContent='Loading product suggestions…';
            suggestionTimer=setTimeout(async()=>{request=new AbortController();try{
                const url=new URL(document.getElementById('recipe-list').dataset.endpoint,location.href);url.searchParams.set('suggest','products');url.searchParams.set('search',nameInput.value.trim());url.searchParams.set('branch',branch?.value||'');
                const response=await fetch(url,{credentials:'same-origin',signal:request.signal});const result=await response.json();if(serial!==generation)return;if(!response.ok)throw new Error(result.message||'Product suggestions could not be loaded.');
                (result.products||[]).forEach((product,index)=>{
                    const option=document.createElement('button');option.type='button';option.tabIndex=-1;option.id='recipe-product-option-'+index;option.setAttribute('role','option');option.setAttribute('aria-selected','false');
                    const details=document.createElement('span'),title=document.createElement('strong'),scope=document.createElement('small');title.textContent=product.name;scope.textContent=product.branch_name||'Global (All branches)';details.append(title,scope);
                    const badge=document.createElement('span');const configured=Number(product.has_recipe)===1;badge.className='recipe-suggestion-badge'+(configured?' has-recipe':'');badge.textContent=configured?'Has recipe':'No recipe';option.append(details,badge);
                    option.addEventListener('pointerdown',event=>event.preventDefault());option.addEventListener('click',()=>{nameInput.value=product.name;closeSuggestions();nameInput.focus();});suggestions.append(option);
                });
                suggestionStatus.dataset.state=suggestions.children.length?'':'empty';suggestionStatus.textContent=suggestions.children.length?'':'No matching products are available for your branch.';suggestionStatus.hidden=!!suggestions.children.length;
                if(document.activeElement!==nameInput){closeSuggestions();return;}
                suggestions.hidden=!suggestions.children.length;nameInput.setAttribute('aria-expanded',suggestions.children.length?'true':'false');
            }catch(error){if(error.name==='AbortError'||serial!==generation)return;closeSuggestions();suggestionStatus.hidden=false;suggestionStatus.dataset.state='error';suggestionStatus.textContent=error.message||'Could not load product suggestions. Focus this field to retry.';}},200);
        }
        nameInput.addEventListener('focus',refreshSuggestions);nameInput.addEventListener('input',refreshSuggestions);branch?.addEventListener('change',refreshSuggestions);
        nameInput.addEventListener('keydown',event=>{if(suggestions.hidden)return;if(event.key==='ArrowDown'||event.key==='ArrowUp'){event.preventDefault();active=(active+(event.key==='ArrowDown'?1:-1)+suggestions.children.length)%suggestions.children.length;highlight();}else if(event.key==='Enter'&&active>=0){event.preventDefault();suggestions.children[active].click();}else if(event.key==='Escape'){event.preventDefault();event.stopPropagation();closeSuggestions();}});
        nameInput.addEventListener('blur',()=>setTimeout(closeSuggestions,150));
    }
    const factors = {g:1,kg:1000,ml:1,l:1000};
    const canonical = unit => ({pcs:'pc',piece:'pc',pieces:'pc',gram:'g',grams:'g',kilogram:'kg',kilograms:'kg',liter:'l',litre:'l',liters:'l',milliliter:'ml'}[String(unit).toLowerCase()] || String(unit).toLowerCase());
    let sequence = 0;
    function eligibleIngredients() {
        const scope = branch ? branch.value : '';
        if (!scope) return [];
        return data.ingredients.filter(item => !item.branch_id || (scope !== 'global' && String(item.branch_id) === scope));
    }
    function ingredientOptions(select, selected='') {
        const options = eligibleIngredients();
        select.replaceChildren(new Option(branch && !branch.value ? 'Choose a branch first' : (options.length ? 'Choose ingredient' : 'No ingredients for this branch'),''));
        options.forEach(item => select.append(new Option(item.name,item.id)));
        select.value = options.some(item => String(item.id)===String(selected)) ? String(selected) : '';
    }
    function unitOptions(select, base, current) {
        base = canonical(base || 'pc'); current = canonical(current || base);
        const options = [["pc", "Piece (pc)"], ["pcs", "Pieces (pcs)"], ["kg", "Kilogram (kg)"], ["g", "Gram (g)"], ["l", "Liter (l)"], ["ml", "Milliliter (ml)"], ["bot", "Bottle (bot)"], ["pck", "Pack (pck)"], ["bndl", "Bundle (bndl)"], ["sck", "Sack (sck)"], ["cse", "Case (cse)"], ["rl", "Roll (rl)"], ["dz", "Dozen (dz)"], ["m", "Meter (m)"], ["cm", "Centimeter (cm)"], ["in", "Inch (in)"], ["yd", "Yard (yd)"], ["set", "Set"], ["box", "Box"], ["pr", "Pair (pr)"], ["try", "Tray (try)"], ["gal", "Gallon (gal)"], ["tb", "Tub (tb)"], ["jr", "Jar (jr)"], ["cn", "Can (cn)"], ["can", "Can"], ["pl", "Pail (pl)"], ["cp", "Cup (cp)"], ["gls", "Glass (gls)"], ["srv", "Serving (srv)"], ["hr", "Hour (hr)"], ["day", "Day (day)"], ["tab", "Tablet (tab)"], ["cap", "Capsule (cap)"]];
        const units = new Map(options);
        data.ingredients.forEach(item=>{const value=canonical(item.unit_type||'pc');if(!units.has(value))units.set(value,value);});
        if(!units.has(current))units.set(current,current);
        select.replaceChildren(...Array.from(units,([value,label])=>new Option(label,value,false,value===current)));

    }
    function update() {
        const count = Number(countInput.value);
        preview.replaceChildren();
        if (!Number.isInteger(count) || count < 1 || count > 100000) { preview.textContent='Enter a whole number of units from 1 to 100,000.'; return; }
        const totals = new Map(); let cost = 0; let incompatible = false;
        rows.querySelectorAll('.definition-row').forEach(row => {
            const ingredient = data.ingredients.find(item => Number(item.id)===Number(row.querySelector('.definition-ingredient').value));
            const quantity = Number(row.querySelector('.definition-quantity').value);
            const unit = row.querySelector('.definition-unit').value;
            if (!ingredient || !(quantity>0)) return;
            const key=ingredient.id+':'+unit, previous=totals.get(key);
            totals.set(key,{name:ingredient.name,quantity:quantity*count+(previous?previous.quantity:0),unit});
            const base=canonical(ingredient.unit_type);
            if(unit!==base && !(['g','kg'].includes(unit)&&['g','kg'].includes(base)) && !(['ml','l'].includes(unit)&&['ml','l'].includes(base))){incompatible=true;return;}
            cost += quantity*(factors[unit]||1)/(factors[canonical(ingredient.unit_type)]||1)*Number(ingredient.cost_price||0)*count;
        });
        const list=document.createElement('ul'); list.className='mb-2 ps-3';
        totals.forEach(item => { const line=document.createElement('li'); line.textContent=item.name+': '+item.quantity.toLocaleString(undefined,{maximumFractionDigits:4})+' '+item.unit; list.append(line); });
        if (totals.size) {
            preview.append(list);
            const summary=document.createElement('strong'); summary.textContent=incompatible?'Choose compatible units for the selected ingredients before saving.': 'Estimated ingredient cost for '+count.toLocaleString()+' units: ₱'+cost.toFixed(2); preview.append(summary);
        } else preview.textContent='Choose ingredients and quantities to calculate the requirements.';
    }
    function addRow(value={}) {
        const index=sequence++;
        const row=document.createElement('div'); row.className='definition-row row g-2 align-items-end mb-3';
        row.innerHTML='<div class="col-md-6"><label class="form-label small">Ingredient</label><input class="form-control form-control-sm mb-1 definition-search" type="search" aria-label="Search ingredient" placeholder="Search ingredients"><select class="form-select definition-ingredient" required aria-label="Ingredient"></select></div><div class="col-5 col-md-2"><label class="form-label small">Quantity</label><input type="number" min="0.0001" step="0.0001" required class="form-control definition-quantity" aria-label="Quantity per unit"></div><div class="col-5 col-md-2"><label class="form-label small">Unit</label><select class="form-select definition-unit" required aria-label="Ingredient unit"></select></div><div class="col-2"><button type="button" class="btn btn-outline-danger definition-remove" aria-label="Remove ingredient">×</button></div>';
        const ingredientSelect=row.querySelector('.definition-ingredient'), quantity=row.querySelector('.definition-quantity'), unit=row.querySelector('.definition-unit');
        ingredientSelect.name='ingredients['+index+'][ingredient_id]'; quantity.name='ingredients['+index+'][quantity]'; unit.name='ingredients['+index+'][unit]';
        ingredientOptions(ingredientSelect,value.ingredient_id||''); quantity.value=value.quantity||'';
        const ingredient=data.ingredients.find(item => Number(item.id)===Number(ingredientSelect.value));
        unitOptions(unit,ingredient?ingredient.unit_type:'pc',value.unit);
        ingredientSelect.addEventListener('change',()=>{const item=data.ingredients.find(item=>Number(item.id)===Number(ingredientSelect.value));unitOptions(unit,item?item.unit_type:'pc');update();});
        row.querySelector('.definition-remove').addEventListener('click',()=>{row.remove();update();});
        row.querySelector('.definition-search').addEventListener('input',event=>{const term=event.target.value.toLowerCase();Array.from(ingredientSelect.options).forEach(option=>{option.hidden=!!option.value&&!option.text.toLowerCase().includes(term);});});
        row.addEventListener('input',update); row.addEventListener('change',update);
        rows.append(row); update();
    }
    document.getElementById('recipe-add-row').addEventListener('click',()=>addRow());
    countInput.addEventListener('input',update);
    if (branch) branch.addEventListener('change',()=>{
        rows.querySelectorAll('.definition-ingredient').forEach(select=>{
            const previous=select.value;
            ingredientOptions(select,previous);
            if (previous && !select.value) {
                const row=select.closest('.definition-row');
                unitOptions(row.querySelector('.definition-unit'),'pc');
                row.querySelector('.definition-search').value='';
            }
        });
        update();
    });
    (data.rows.length?data.rows:[{}]).forEach(addRow);
    update();
})();
