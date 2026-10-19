// Match navigation by application route, ignoring filters and editor parameters.
$(function () {
  'use strict';
  const nav=document.getElementById('sidebarnav');if(!nav)return;
  const current=new URL(window.location.href);const route=current.searchParams.get('route')||'dashboard';
  let match=null,best=-1;
  nav.querySelectorAll('a.sidebar-link').forEach(link=>{
    link.classList.remove('active');link.removeAttribute('aria-current');
    const href=link.getAttribute('href')||'';if(!href||href.startsWith('javascript:')||href==='#')return;
    const target=new URL(href,window.location.href);const candidate=target.searchParams.get('route');
    if(target.origin!==current.origin||!candidate)return;
    if((route===candidate||route.startsWith(candidate+'/'))&&candidate.length>best){match=link;best=candidate.length;}
  });
  nav.querySelectorAll('li.selected').forEach(li=>li.classList.remove('selected'));
  if(match){
    match.classList.add('active');match.setAttribute('aria-current','page');match.closest('li').classList.add('selected');
    let parent=match.parentElement.parentElement;
    while(parent&&parent!==nav){if(parent.matches('ul.first-level')){parent.classList.add('in');parent.setAttribute('aria-expanded','true');const group=parent.parentElement;group.classList.add('selected');const toggle=group.querySelector(':scope > a');if(toggle){toggle.classList.add('active');toggle.setAttribute('aria-expanded','true');}}parent=parent.parentElement;}
  }
  const scroller=nav.closest('.scroll-sidebar');
  if(scroller){
    const key='pos-sidebar-scroll:'+current.pathname;
    const remember=()=>{try{sessionStorage.setItem(key,String(scroller.scrollTop));}catch(error){}};
    const restore=()=>{
      try{const saved=sessionStorage.getItem(key);if(saved!==null&&Number.isFinite(Number(saved)))scroller.scrollTop=Math.max(0,Number(saved));}catch(error){}
      if(match){
        const viewport=scroller.getBoundingClientRect(),active=match.getBoundingClientRect();
        const padding=12;
        if(active.top<viewport.top+padding)scroller.scrollTop-=viewport.top+padding-active.top;
        else if(active.bottom>viewport.bottom-padding)scroller.scrollTop+=active.bottom-viewport.bottom+padding;
      }
      remember();
    };
    requestAnimationFrame(()=>{restore();scroller.addEventListener('scroll',remember,{passive:true});});
    nav.addEventListener('click',remember,true);
    window.addEventListener('pagehide',remember);
    window.addEventListener('pageshow',event=>{if(event.persisted)restore();});
  }
  nav.querySelectorAll('a.has-arrow').forEach(toggle=>toggle.addEventListener('click',function(event){
    const submenu=this.nextElementSibling;if(!submenu||!submenu.matches('ul'))return;event.preventDefault();
    const expanded=!submenu.classList.contains('in');submenu.classList.toggle('in',expanded);submenu.setAttribute('aria-expanded',String(expanded));this.setAttribute('aria-expanded',String(expanded));
  }));
});
