(function(){
 'use strict';
 const dialog=document.getElementById('demo-account-dialog');
 if(!dialog)return;
 let previousFocus=null;
 function open(){
  if(dialog.open)return;
  previousFocus=document.activeElement;
  if(typeof dialog.showModal==='function')dialog.showModal();else dialog.setAttribute('open','');
  const button=dialog.querySelector('[data-demo-close]');if(button)button.focus();
 }
 function close(event){
  if(typeof dialog.close!=='function'){event.preventDefault();dialog.removeAttribute('open');}
 }
 document.querySelectorAll('[data-demo-open]').forEach(button=>button.addEventListener('click',open));
 dialog.querySelector('form').addEventListener('submit',close);
 dialog.addEventListener('keydown',event=>event.stopPropagation());
 dialog.addEventListener('close',()=>{if(previousFocus&&previousFocus.isConnected)previousFocus.focus();});
 if(dialog.dataset.showOnLogin==='1')open();
})();
