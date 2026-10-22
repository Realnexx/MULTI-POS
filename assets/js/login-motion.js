document.addEventListener('DOMContentLoaded', () => {
 const root=document.querySelector('[data-login-motion]');
 const reduced=window.matchMedia('(prefers-reduced-motion: reduce)');
 if(!root||reduced.matches||!window.gsap||root.querySelector('.signin-feedback')) return;
 const context=gsap.context(()=>{
  gsap.from(root.querySelectorAll('.signin-panel>.eyebrow,.signin-panel>h1,.signin-description'),{y:20,opacity:0,duration:.4,stagger:.06,ease:'power2.out',clearProps:'transform,opacity'});
  gsap.from(root.querySelector('.signin-art'),{x:-30,opacity:.3,duration:.65,ease:'power2.out',clearProps:'transform,opacity'});
 },root);
 root.addEventListener('focusin',()=>context.revert(),{once:true});
 reduced.addEventListener('change',event=>{if(event.matches)context.revert();});
});
