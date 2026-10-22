(function(){
 'use strict';
 const list=document.getElementById('recipe-list');if(!list)return;
 const search=document.getElementById('recipe-search'),size=document.getElementById('recipe-page-size'),info=document.getElementById('recipe-page-info'),pages=document.getElementById('recipe-pagination'),empty=document.getElementById('recipe-search-empty'),error=document.getElementById('recipe-library-error');
 let start=0,serial=0,pending=null,timer;
 function pageButton(label,offset,disabled,current){const button=document.createElement('button');button.type='button';button.textContent=label;button.disabled=disabled;button.className=current?'active':'';if(current)button.setAttribute('aria-current','page');button.addEventListener('click',()=>{start=offset;load();});return button;}
 async function load(){
  const generation=++serial;if(pending)pending.abort();pending=new AbortController();error.hidden=true;empty.classList.add('d-none');list.setAttribute('aria-busy','true');pages.replaceChildren();info.textContent='Loading recipes…';list.replaceChildren();
  for(let i=0;i<6;i++){const skeleton=document.createElement('div');skeleton.className='recipe-card-skeleton';skeleton.setAttribute('aria-hidden','true');list.append(skeleton);}
  try{
   const target=new URL(list.dataset.endpoint,location.href);target.searchParams.set('start',start);target.searchParams.set('length',size.value);target.searchParams.set('search',search.value.trim());
   const response=await fetch(target,{credentials:'same-origin',signal:pending.signal,headers:{Accept:'application/json'}});if(!response.ok)throw new Error('Could not load recipes.');const result=await response.json();if(generation!==serial)return;
   if(!Array.isArray(result.data)||!Number.isFinite(Number(result.recordsFiltered)))throw new Error('Invalid recipe response.');
   start=Number(result.start);const total=Number(result.recordsFiltered),length=Number(result.length),page=Math.floor(start/length),count=Math.ceil(total/length);list.replaceChildren();
   for(const recipe of result.data){const button=document.createElement('button');button.type='submit';button.setAttribute('form','recipe-select-form');button.name='recipe_id';button.value=recipe.id;button.className='list-group-item list-group-item-action'+(String(recipe.id)===list.dataset.selected?' active':'');
    const icon=document.createElement('span');icon.className='recipe-list-icon';const glyph=document.createElement('i');glyph.className='ti ti-chef-hat';glyph.setAttribute('aria-hidden','true');icon.append(glyph);
    const title=document.createElement('strong');title.className='d-block';title.textContent=recipe.name;
    const details=document.createElement('small');details.textContent=Number(recipe.ingredient_count)+' ingredients · '+(recipe.branch_name||'Global (All branches)')+(!Number(recipe.is_sellable)?' · Definition only':'');button.append(icon,title,details);list.append(button);
   }
   empty.textContent=search.value.trim()?'No matching recipes. Try another name.':'No recipes yet. Create a recipe to get started.';empty.classList.toggle('d-none',total>0);
   info.textContent='Showing '+(total?start+1:0)+'–'+Math.min(start+length,total)+' of '+total+' recipes'+(Number(result.recordsTotal)!==total?' (filtered from '+result.recordsTotal+')':'');
   pages.append(pageButton('Previous',Math.max(0,start-length),page===0||!total,false));
   const visible=new Set([0,count-1,page-1,page,page+1]);let previous=-1;
   Array.from(visible).filter(n=>n>=0&&n<count).sort((a,b)=>a-b).forEach(n=>{if(previous>=0&&n-previous>1){const dots=document.createElement('span');dots.textContent='…';pages.append(dots);}pages.append(pageButton(String(n+1),n*length,false,n===page));previous=n;});
   pages.append(pageButton('Next',start+length,!total||page>=count-1,false));
  }catch(e){if(e.name!=='AbortError'&&generation===serial){list.replaceChildren();error.hidden=false;info.textContent='Recipes could not be loaded.';}}
  finally{if(generation===serial)list.setAttribute('aria-busy','false');}
 }
 search.addEventListener('input',()=>{clearTimeout(timer);if(pending)pending.abort();++serial;timer=setTimeout(()=>{start=0;load();},300);});
 size.addEventListener('change',()=>{clearTimeout(timer);start=0;load();});document.getElementById('recipe-library-retry').addEventListener('click',load);load();
})();
