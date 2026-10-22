(() => {
 'use strict';
 const source = document.getElementById('branch-share-data');
 if (!source) return;
 const data = JSON.parse(source.textContent), canvas = document.getElementById('branch-share-canvas'), ctx = canvas.getContext('2d');
 const theme = document.getElementById('branch-share-theme'), page = document.getElementById('branch-share-page'), status = document.getElementById('branch-share-status');
 const layout = document.getElementById('branch-share-layout');
 let fontFamily='Arial, sans-serif';
 const hide = document.getElementById('branch-share-hide');
 const hideNames = document.getElementById('branch-share-hide-names');
 const rows = data.branches.map(r => ({name:String(r.name || 'Unnamed branch'), total:Number(r.total)||0, transactions:Number(r.transactions)||0})).sort((a,b) => b.total-a.total || a.name.localeCompare(b.name));
 rows.forEach((r,i) => r.rank = i && r.total === rows[i-1].total ? rows[i-1].rank : i+1);
 const pages = Math.max(1,Math.ceil(rows.length/8));
 for(let i=0;i<pages;i++){const o=document.createElement('option');o.value=i;o.textContent=rows.length ? `Branches ${i*8+1}–${Math.min(rows.length,(i+1)*8)} of ${rows.length}` : 'No branch data';page.append(o);}
 const palettes = {aqua:['#e2f8fa','#103b48','#007e98','#ffffff','#577b85'],midnight:['#101b2d','#ffffff','#69e4e8','#1e3047','#aec2d3'],paper:['#f7f1e5','#302c26','#805a28','#fffdf8','#7a7061'],rose:['#fae9ef','#4d2537','#ac3f68','#fff7fa','#896474'],cobalt:['#e9efff','#142a57','#2148c0','#ffffff','#5d6f93'],mono:['#f0f0ed','#202020','#292929','#ffffff','#656562'],matcha:['#eef0de','#293f30','#43634a','#fafbf2','#637461'],lavender:['#f1eaff','#39245b','#7750ad','#fffaff','#7c6a94'],citrus:['#f7f3d3','#353823','#506128','#fffef1','#747854'],terracotta:['#f6eae1','#50372b','#9c4e34','#fff9f3','#8a7061']};
 const money = n => '₱'+n.toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});
 function text(value,x,y,size,color,maxWidth,weight='400') {ctx.font=`${weight} ${size}px ${fontFamily}`;ctx.fillStyle=color;ctx.textAlign='left';let s=String(value);if(maxWidth){while(s.length && ctx.measureText(s).width>maxWidth)s=s.slice(0,-1);if(s!==String(value))s=s.slice(0,-1)+'…';}ctx.fillText(s,x,y);}
 function box(x,y,w,h,r,color){ctx.fillStyle=color;ctx.beginPath();ctx.moveTo(x+r,y);ctx.arcTo(x+w,y,x+w,y+h,r);ctx.arcTo(x+w,y+h,x,y+h,r);ctx.arcTo(x,y+h,x,y,r);ctx.arcTo(x,y,x+w,y,r);ctx.closePath();ctx.fill();}
 function trophy(x,y,color) {
  ctx.save();ctx.translate(x,y);
  ctx.strokeStyle=color;ctx.lineWidth=13;
  for(const direction of [-1,1]) {ctx.beginPath();ctx.ellipse(direction*63,48,35,47,direction*.35,0,Math.PI*2);ctx.stroke();}
  const metal=ctx.createLinearGradient(-55,0,65,90);metal.addColorStop(0,'#8be4ed');metal.addColorStop(.45,color);metal.addColorStop(1,'#004354');
  ctx.fillStyle=metal;ctx.beginPath();ctx.moveTo(-66,0);ctx.quadraticCurveTo(-60,115,0,124);ctx.quadraticCurveTo(60,115,66,0);ctx.closePath();ctx.fill();
  box(-10,116,20,32,4,color);box(-51,145,102,26,7,color);box(-65,169,130,18,5,color);
  ctx.fillStyle='#e2fbff';ctx.beginPath();for(let i=0;i<10;i++){const angle=-Math.PI/2+i*Math.PI/5,r=i%2?13:29;const px=Math.cos(angle)*r,py=51+Math.sin(angle)*r;i?ctx.lineTo(px,py):ctx.moveTo(px,py);}ctx.closePath();ctx.fill();ctx.restore();
 }
 function referenceCard(palette,current,visible,variant='leaderboard') {
  const [bg,ink,accent,card,muted]=palette;
  const editorial=variant==='editorial', poster=variant==='poster', ticket=variant==='ticket', celebration=variant==='celebration';
  const corner=editorial||poster?4:ticket?10:20;
  ctx.fillStyle=bg;ctx.fillRect(0,0,1080,1350);
  const wash=ctx.createRadialGradient(390,270,20,540,470,930);wash.addColorStop(0,card);wash.addColorStop(1,bg);ctx.fillStyle=wash;ctx.fillRect(0,0,1080,1350);
  ctx.save();ctx.globalAlpha=.1;ctx.fillStyle=accent;
  for(let i=0;i<3;i++){ctx.beginPath();ctx.ellipse(1070,330+i*65,360,150,-.4,0,Math.PI*2);ctx.fill();}ctx.restore();
  box(46,40,330,44,10,accent);text('▥  BRANCH LEADERBOARD',64,69,19,card,295,'700');
  text('PERFORMANCE / '+String(current+1).padStart(2,'0'),785,65,13,muted,260,'700');
  trophy(920,125,accent);
  if(celebration){ctx.save();ctx.globalAlpha=.35;for(let i=0;i<24;i++){ctx.fillStyle=i%2?accent:muted;ctx.fillRect(50+(i*137)%980,95+(i*53)%220,4,9);}ctx.restore();}
  if(poster){ctx.fillStyle=accent;ctx.fillRect(0,0,12,1350);}
  if(ticket){ctx.save();ctx.strokeStyle=accent;ctx.setLineDash([5,8]);ctx.strokeRect(22,22,1036,1306);ctx.restore();}
  fontFamily=poster?'Arial, sans-serif':ticket?'monospace':'Georgia, serif';
  if(poster)text('BRANCH / BEST.',46,183,67,ink,760,'800');
  else if(ticket)text('Branch highlights.',46,183,60,ink,760,'700');
  else if(celebration)text('Every sale counts.',46,183,69,ink,760,'700');
  else {text('The branch',46,183,77,ink,665,'700');text('edit.',480,183,77,accent,230,'700');}
  text('Celebrating every branch, every sale.',48,229,28,muted,760);
  fontFamily='Arial, sans-serif';box(46,262,58,58,16,bg);text('▦',61,301,31,accent,40,'700');
  text(data.period,124,286,25,ink,630,'700');text(hideNames.checked?'Selected branches':data.scope,124,315,19,muted,630);
  const total=rows.reduce((n,r)=>n+r.total,0),transactions=rows.reduce((n,r)=>n+r.transactions,0);
  for(let i=0;i<2;i++){const x=46+i*504;box(x,346,484,118,corner,card);box(x+20,368,66,66,20,bg);text(i?'▤':'▥',x+37,413,33,accent,45,'700');text(i?'TOTAL TRANSACTIONS':hide.checked?'BRANCHES IN THE SPOTLIGHT':'TOTAL COMPLETED SALES',x+106,385,13,muted,360,'700');text(i?transactions.toLocaleString():hide.checked?String(rows.length):money(total),x+106,436,38,accent,350,'700');}
  box(38,486,1004,54,14,accent);text('RANK',78,520,14,card,100,'700');text('BRANCH',215,520,14,card,500,'700');if(!hide.checked)text('SALES',882,520,14,card,130,'700');
  const list=document.getElementById('branch-share-accessible');list.replaceChildren();
  const height=Math.min(103,(650-Math.max(0,visible.length-1)*12)/Math.max(1,visible.length));
  visible.forEach((r,i)=>{
   const y=554+i*(height+12),cy=y+height/2,name=hideNames.checked?`Branch ${current*8+i+1}`:r.name;
   box(38,y,1004,height,corner,card);
   if(r.rank===1){ctx.save();ctx.strokeStyle=accent;ctx.globalAlpha=.25;ctx.lineWidth=2;ctx.strokeRect(40,y+2,1000,height-4);ctx.restore();};
   const colors=r.rank===1?['#ffe7a3','#bc7c12']:r.rank===2?['#ecf0f4','#788795']:r.rank===3?['#f4ccaf','#a3603d']:['#cfdee8','#577b91'];
   const medal=ctx.createLinearGradient(72,cy-30,130,cy+30);medal.addColorStop(0,colors[0]);medal.addColorStop(1,colors[1]);ctx.fillStyle=medal;ctx.beginPath();ctx.arc(101,cy,31,0,Math.PI*2);ctx.fill();ctx.strokeStyle=colors[0];ctx.lineWidth=5;ctx.stroke();
   ctx.font='700 23px Arial';ctx.fillStyle='#fff';ctx.textAlign='center';ctx.fillText(String(r.rank).padStart(2,'0'),101,cy+8);ctx.textAlign='left';
   box(174,cy-31,60,62,18,bg);text('▥',190,cy+12,31,accent,40,'700');
   fontFamily='Georgia, serif';text(name,258,cy-1,27,ink,hide.checked?710:440,'700');fontFamily='Arial, sans-serif';
   text(`${r.transactions.toLocaleString()} transactions${r.rank===1?' · LEADING THE WAY':''}`,258,cy+25,14,muted,430);
   if(!hide.checked){box(714,cy-34,294,68,16,bg);ctx.font='700 29px Georgia';ctx.textAlign='right';ctx.fillStyle=accent;ctx.fillText(money(r.total),986,cy+10,255);ctx.textAlign='left';}
   const li=document.createElement('li');li.textContent=`Rank ${r.rank}: ${name}, ${hide.checked?'':money(r.total)+', '}${r.transactions} transactions`;list.append(li);
  });
  if(!visible.length)text('No completed sales for this selection.',70,640,28,ink,940);
  text('RANKED BY COMPLETED SALES',50,1250,14,muted,700,'700');text(`PAGE ${current+1} / ${pages}`,900,1250,14,accent,150,'700');text(data.generated,50,1290,15,muted,960);
  canvas.setAttribute('aria-label',`Branch rankings: ${data.period}. Page ${current+1} of ${pages}.`);status.textContent='';
 }
 function draw(){
  const [bg,ink,accent,card,muted]=palettes[theme.value], current=Number(page.value)||0, visible=rows.slice(current*8,(current+1)*8);
  referenceCard(palettes[theme.value],current,visible,layout.value);return;
 }

 function blob(){return new Promise((resolve,reject)=>canvas.toBlob(b=>b?resolve(b):reject(new Error('Image could not be created.')),'image/png'));}
 function filename(){return `branch-rankings-${theme.value}-${layout.value}-${Number(page.value)+1}.png`;}
 async function download(){const b=await blob(),url=URL.createObjectURL(b),a=document.createElement('a');a.href=url;a.download=filename();document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);status.textContent='Image downloaded. You can upload it to your preferred social app.';}
 theme.addEventListener('change',draw);layout.addEventListener('change',draw);page.addEventListener('change',draw);hide.addEventListener('change',draw);hideNames.addEventListener('change',draw);
 document.getElementById('branch-share-download').addEventListener('click',()=>download().catch(e=>status.textContent=e.message));
 document.getElementById('branch-share-native').addEventListener('click',async()=>{try{const file=new File([await blob()],filename(),{type:'image/png'});if(navigator.canShare && navigator.canShare({files:[file]})){await navigator.share({files:[file]});status.textContent='Image shared.';}else await download();}catch(e){if(e.name!=='AbortError')status.textContent='Sharing could not be completed. Try Download PNG.';}});
 draw();
})();
