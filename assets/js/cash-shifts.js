document.addEventListener('click',function(event){
    if(event.target.closest('[data-print-shift]')) window.print();
    const shortcut=event.target.closest('[data-shift-tab]');
    if(shortcut && window.bootstrap){
        const tab=document.getElementById('shift-tab-'+shortcut.dataset.shiftTab);
        if(tab) bootstrap.Tab.getOrCreateInstance(tab).show();
    }
});

function updateScheduleMode(form){
 const mode=form.querySelector('[data-schedule-mode]');if(!mode)return;
 const weekly=mode.value==='weekly';
 form.querySelectorAll('[data-weekly-field]').forEach(el=>{el.hidden=!weekly;el.querySelectorAll('input').forEach(input=>{input.disabled=!weekly;if(input.name==='date_to')input.required=weekly;});});
}
document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('[data-schedule-mode]').forEach(select=>{updateScheduleMode(select.form);select.addEventListener('change',()=>updateScheduleMode(select.form));}));

function enhanceCashierPicker(select){
 const host=document.createElement('div');host.className='dropdown schedule-cashier-picker';select.insertAdjacentElement('afterend',host);
 const trigger=document.createElement('button');trigger.type='button';trigger.className='form-select schedule-cashier-trigger';trigger.dataset.bsToggle='dropdown';trigger.setAttribute('aria-expanded','false');trigger.setAttribute('aria-label','Choose cashier');host.append(trigger);
 const menu=document.createElement('div');menu.className='dropdown-menu schedule-cashier-menu';host.append(menu);
 const search=document.createElement('input');search.type='search';search.className='form-control schedule-cashier-search';search.placeholder='Find cashier';search.setAttribute('aria-label','Find cashier');menu.append(search);
 const list=document.createElement('div');list.className='schedule-cashier-options';menu.append(list);
 const message=document.createElement('div');message.className='invalid-feedback';message.textContent='Choose a cashier.';host.append(message);
 const rolesFor=option=>{try{const value=JSON.parse(option.dataset.roles||'[]');return Array.isArray(value)?value:[];}catch(_){return [];}};
 function userLabel(option){const wrap=document.createElement('span');wrap.className='schedule-cashier-user';const name=document.createElement('span');name.textContent=option.dataset.cashierName||option.text;wrap.append(name);for(const role of rolesFor(option)){const badge=document.createElement('span');badge.className='schedule-role-badge';badge.textContent=role;wrap.append(badge);}return wrap;}
 function update(){const option=select.selectedOptions[0];trigger.replaceChildren(option&&option.value?userLabel(option):document.createTextNode('Choose cashier'));trigger.classList.remove('is-invalid');message.classList.remove('d-block');list.querySelectorAll('button').forEach(button=>button.classList.toggle('active',button.dataset.value===select.value));}
 for(const option of select.options){if(!option.value)continue;const button=document.createElement('button');button.type='button';button.className='dropdown-item';button.dataset.value=option.value;button.append(userLabel(option));button.addEventListener('click',()=>{select.value=option.value;select.dispatchEvent(new Event('change',{bubbles:true}));trigger.focus();});list.append(button);}
 const empty=document.createElement('p');empty.className='small text-muted p-3 mb-0';empty.textContent='No matching cashiers';empty.hidden=true;list.append(empty);
 search.addEventListener('input',()=>{const term=search.value.toLowerCase();let visible=0;for(const button of list.querySelectorAll('button')){button.hidden=!button.textContent.toLowerCase().includes(term);if(!button.hidden)visible++;}empty.hidden=visible>0;});
 trigger.addEventListener('shown.bs.dropdown',()=>{search.value='';search.dispatchEvent(new Event('input'));search.focus();});
 select.addEventListener('change',update);select.addEventListener('invalid',event=>{event.preventDefault();trigger.classList.add('is-invalid');message.classList.add('d-block');trigger.focus();});select.form.addEventListener('reset',()=>setTimeout(update,0));select.classList.add('visually-hidden');select.tabIndex=-1;update();
}
document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('[data-cashier-picker]').forEach(enhanceCashierPicker);
 const table=document.getElementById('schedule-audit-table');if(!table)return;
 let audit=null;const error=document.getElementById('schedule-audit-error');
 function showError(text){error.textContent=text;error.hidden=false;}
 function loadAudit(){
  if(audit){audit.columns.adjust();return;}
  const $=window.jQuery;if(!$||!$.fn.DataTable){showError('The audit table could not load. Refresh this page to try again.');return;}
  const text=$.fn.dataTable.render.text();
  audit=$(table).DataTable({processing:true,serverSide:true,searchDelay:400,pageLength:25,lengthMenu:[10,25,50,100],order:[[0,'desc']],autoWidth:false,
   ajax:{url:table.dataset.endpoint,type:'GET',data:d=>{d.action='get';d.branch_id=table.dataset.branch;d.cashier_id=table.dataset.cashier;d.recorded_from=document.getElementById('schedule-audit-from').value;d.recorded_to=document.getElementById('schedule-audit-to').value;},error:xhr=>{showError(xhr.responseJSON&&(xhr.responseJSON.message||xhr.responseJSON.error)||'Audit history could not be loaded. Please retry.');$(table).find('tbody').empty();}},
   columns:['created_at','branch_name','cashier_name','action','actor_name','reason'].map(data=>({data,render:text})),language:{emptyTable:'No schedule audit records match these filters.'}});
  $(table).on('preXhr.dt',()=>{error.hidden=true;});
 }
 document.getElementById('schedule-section-tab-audit').addEventListener('shown.bs.tab',loadAudit);
 document.getElementById('schedule-audit-apply').addEventListener('click',()=>{loadAudit();if(audit)audit.ajax.reload();});
 document.addEventListener('shown.bs.tab',event=>{if(audit && event.target.id==='shift-tab-schedules')audit.columns.adjust();});
 const sectionKey='cashier-schedule-section:'+table.dataset.branch;
 document.querySelectorAll('.schedule-section-tabs [data-bs-toggle="pill"]').forEach(button=>button.addEventListener('shown.bs.tab',()=>{try{sessionStorage.setItem(sectionKey,button.id);}catch(_){}}));
 try{const previous=sessionStorage.getItem(sectionKey),button=previous&&document.getElementById(previous);if(button&&button.closest('.schedule-section-tabs')&&window.bootstrap)bootstrap.Tab.getOrCreateInstance(button).show();}catch(_){}

});
