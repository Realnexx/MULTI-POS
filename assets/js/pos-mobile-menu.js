document.addEventListener('DOMContentLoaded',function(){
 const menu=document.getElementById('pos-mobile-menu'),trigger=document.getElementById('pos-mobile-menu-open'),header=document.querySelector('.pos-topbar');
 if(!menu||!trigger||!header)return;
 const links=document.getElementById('pos-mobile-menu-links'),register=document.getElementById('pos-mobile-menu-register'),moves=[];
 function movable(node,target){if(!node)return;const marker=document.createComment('POS action position');node.before(marker);moves.push({node,marker,target});}
 header.querySelectorAll('.pos-header-primary>a').forEach(node=>movable(node,links));
 movable(header.querySelector('.pos-header-brand button'),links);
 movable(document.getElementById('pos-drawer-bar'),register);
 const mobile=window.matchMedia('(max-width:900px)');
 function update(){if(!mobile.matches&&menu.open)menu.close();moves.forEach(({node,marker,target})=>{if(mobile.matches)target.append(node);else marker.after(node);});}
 trigger.addEventListener('click',()=>{if(!menu.open)menu.showModal();});
 document.getElementById('pos-mobile-menu-close').addEventListener('click',()=>menu.close());
 menu.addEventListener('click',event=>{if(event.target===menu){const rect=menu.getBoundingClientRect();if(event.clientX<rect.left||event.clientX>rect.right||event.clientY<rect.top||event.clientY>rect.bottom)menu.close();}else if(event.target.closest('a,button')&&!event.target.closest('#pos-mobile-menu-close'))menu.close();});
 menu.addEventListener('close',()=>trigger.focus());
 mobile.addEventListener('change',update);update();
});
