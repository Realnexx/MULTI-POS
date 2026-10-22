document.addEventListener('DOMContentLoaded',()=>{
 const policy=window.userFormPolicy||{};
 for(const modal of document.querySelectorAll('#createUserModal,#editUserModal')){
  const form=modal.querySelector('form');if(!form)continue;
  form.noValidate=true;
  const fields=Array.from(form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"])'));
  const notices=new Map();
  for(const field of fields){
   const error=document.createElement('div');error.className='users-field-error';error.id=modal.id+'-'+field.name+'-error';error.hidden=true;error.setAttribute('aria-live','polite');field.after(error);field.setAttribute('aria-describedby',error.id);notices.set(field,error);
   if(field.name==='password'){
    field.autocomplete='new-password';const meter=document.createElement('div');meter.className='users-password-strength';meter.innerHTML='<div class="users-strength-track"><span></span></div><small></small>';error.before(meter);
    field.addEventListener('input',()=>{const v=field.value,types=[/[a-z]/,/[A-Z]/,/[0-9]/,/[^a-zA-Z0-9]/].filter(r=>r.test(v)).length;const score=!v?0:v.length<12?1:Math.min(4,2+(v.length>=16?1:0)+(types>=3?1:0));meter.dataset.score=String(score);meter.querySelector('span').style.width=(score*25)+'%';meter.querySelector('small').textContent=!v?'At least 12 characters'+(policy.strong?' with uppercase, lowercase, number and symbol.':'.'):['','Weak','Fair','Good','Strong'][score];});field.dispatchEvent(new Event('input'));
   }
   field.addEventListener('change',()=>validate(field));field.addEventListener('blur',()=>validate(field));field.addEventListener('input',()=>{if(field.getAttribute('aria-invalid')==='true')validate(field);});
  }
  function validate(field){let message='';const v=field.value;if(field.type==='file'&&field.files?.length){const file=field.files[0];if(file.size>10*1024*1024)message='Choose an image up to 10 MB.';else if(!/\.(jpe?g|png|gif|webp)$/i.test(file.name)||! /^[a-zA-Z0-9_() .-]+$/.test(file.name))message='Choose a JPG, PNG, GIF or WebP. Use letters, numbers, spaces, parentheses, dots, hyphens or underscores in the filename.';}else if(field.required&&!v.trim())message='This field is required.';else if(field.name==='password'&&v){if(Array.from(v).length<12)message='Use at least 12 characters.';else if(new TextEncoder().encode(v).length>72||v.includes('\0'))message='Use no more than 72 bytes and no unsupported characters.';else if(policy.strong&& ![/[A-Z]/,/[a-z]/,/[0-9]/,/[^a-zA-Z0-9]/].every(r=>r.test(v)))message='Include uppercase, lowercase, a number and a symbol.';}else if(field.type==='email'&&v&&!field.validity.valid)message='Enter a valid email address.';else if(field.name==='pos_pin'&&v&&!/^[0-9]{4}$/.test(v))message='Enter exactly 4 digits.';else if(!field.validity.valid)message='Please check this value.';const error=notices.get(field);error.textContent=message;error.hidden=field.type==='file'||!message;if(field.type==='file'&&message&&window.showToast)window.showToast(message,'error');field.setAttribute('aria-invalid',message?'true':'false');return !message;}
  form.addEventListener('submit',event=>{const invalid=fields.filter(field=>!validate(field));if(invalid.length){event.preventDefault();invalid[0].focus();}});
  const saved=policy.error;if(saved&&modal.id===(saved.action==='edit'?'editUserModal':'createUserModal')){
   for(const [key,value] of Object.entries(saved.values||{})){const input=form.elements.namedItem(key);if(input&&typeof value!=='object')input.value=value;}
   const roles=(saved.values?.roles||[]).map(String);const restoreRoles=()=>form.querySelectorAll('[name="roles[]"]').forEach(input=>{input.checked=roles.includes(input.value);});restoreRoles();new MutationObserver(restoreRoles).observe(form,{childList:true,subtree:true});
   const input=form.elements.namedItem(saved.field);if(input&&notices.has(input)){notices.get(input).textContent=saved.message;notices.get(input).hidden=input.type==='file';if(input.type==='file'&&window.showToast)window.showToast(saved.message,'error');input.setAttribute('aria-invalid','true');}
   bootstrap.Modal.getOrCreateInstance(modal).show();
  }
 }
});
