document.addEventListener('DOMContentLoaded',function(){
    const bar=document.getElementById('pos-drawer-bar'),dialog=document.getElementById('pos-drawer-dialog');
    if(!bar || !dialog)return;
    let state=null,view='loading',busy=false,pendingExit=null,receipt=null,internalNavigation=false,requestSerial=0;
    const money=c=>new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(Number(c||0)/100);
    const token=()=>document.querySelector('meta[name="csrf-token"]').content;
    const key=()=>Array.from(crypto.getRandomValues(new Uint8Array(16)),b=>b.toString(16).padStart(2,'0')).join('');
    const error=document.getElementById('pos-drawer-error');
    function fail(message){error.textContent=message;error.hidden=false;}
    function usable(){return state && state.schedule_allowed!==false && state.shift && !state.shift.locked && state.shift.matches_branch;}
    async function call(action,form){
        const serial=++requestSerial;
        const data=form?new FormData(form):new FormData();data.set('action',action);data.set('csrf_token',token());
        if(!['status','transactions','open'].includes(action))data.set('shift_id',form?form.dataset.shiftId||'':state&&state.shift?state.shift.id:'');
        if(form){form.dataset.requestKey=form.dataset.requestKey||key();data.set('request_key',form.dataset.requestKey);}else data.set('request_key',key());
        const response=await fetch(bar.dataset.endpoint,{method:'POST',credentials:'same-origin',body:data,headers:{'X-Requested-With':'XMLHttpRequest'}});
        let result;try{result=await response.json();}catch(_){throw new Error('Unable to check the drawer. Please retry.');}
        if(!response.ok || !result.success)throw new Error(result.message||'Drawer operation failed.');
        if(serial===requestSerial)state=result.state;return result;
    }
    function transactions(rows){
        const tbody=document.getElementById('pos-drawer-transactions');tbody.replaceChildren();
        for(const e of rows||[]){const tr=document.createElement('tr');for(const value of [e.created_at+(e.sale_id?' · Sale #'+e.sale_id:''),e.kind.replaceAll('_',' '),e.payment_method,money(Math.round(Number(e.amount)*100)),e.reason]){const td=document.createElement('td');td.textContent=value;tr.appendChild(td);}tbody.appendChild(tr);}
        if(!tbody.children.length){const tr=document.createElement('tr'),td=document.createElement('td');td.colSpan=5;td.textContent='No transactions recorded in this shift yet.';tr.appendChild(td);tbody.appendChild(tr);}
    }
    const openingSource=document.getElementById('pos-drawer-opening-source'),openingAmount=document.getElementById('pos-drawer-opening-amount');
    function openingCash(){
        const select=document.getElementById('pos-drawer-register');
        const register=state&&state.registers.find(r=>String(r.id)===select.value),prior=register&&register.previous_cash;
        const previousId=document.getElementById('pos-drawer-previous-shift'),confirm=document.querySelector('#pos-drawer-cash-confirm input');
        const signature=prior?String(prior.shift_id)+':'+prior.amount:'';
        if(previousId.dataset.signature!==signature){confirm.checked=false;previousId.dataset.signature=signature;delete openingAmount.form.dataset.requestKey;}
        openingSource.querySelector('option[value="previous"]').disabled=!prior;
        if(!prior && openingSource.value==='previous'){openingSource.value='manual';openingAmount.value='';}
        const reuse=openingSource.value==='previous'&&!!prior;
        previousId.value=reuse?prior.shift_id:'';openingAmount.readOnly=reuse;
        if(reuse)openingAmount.value=prior.amount;
        document.getElementById('pos-drawer-cash-confirm').hidden=!reuse;confirm.required=reuse;
        document.getElementById('pos-drawer-previous-amount').textContent=prior?money(Math.round(Number(prior.amount)*100)):'—';
        document.getElementById('pos-drawer-previous-info').textContent=prior?'Your closing count · Shift #'+prior.shift_id+' · '+prior.closed_at:register?'No previous closing count of yours is available for this register. Enter a fresh physical count.':'Choose a register to check its last closing count.';
    }
    openingSource.addEventListener('change',()=>{delete openingAmount.form.dataset.requestKey;openingAmount.value='';document.querySelector('#pos-drawer-cash-confirm input').checked=false;openingCash();});
    document.getElementById('pos-drawer-register').addEventListener('change',()=>{delete openingAmount.form.dataset.requestKey;openingAmount.value='';openingCash();});
    function render(requested){
        const s=state&&state.shift;
        const perms=state?state.permissions:{};
        view=receipt?'closed':!state?'loading':!s?'open':s.locked?'unlock':!s.matches_branch?'mismatch':requested||'overview';
        if(state && state.schedule_allowed===false && !receipt){view=s?(s.locked?'unlock':'close'):'open';fail(state.schedule_message||'Your cashier schedule is not active.');}
        if(usable() && !['overview','transactions','move','close'].includes(view))view='overview';
        document.querySelectorAll('[data-drawer-section]').forEach(el=>{el.hidden=el.dataset.drawerSection!==view;});
        document.getElementById('pos-drawer-tabs').hidden=!usable()||!!receipt;
        document.querySelectorAll('#pos-drawer-tabs [data-drawer-view]').forEach(button=>{
            button.setAttribute('aria-current',button.dataset.drawerView===view?'page':'false');
            button.hidden=(button.dataset.drawerView==='move' && !perms.cash_drawer_movements)||(button.dataset.drawerView==='close' && !perms.close_cash_shift);
        });
        document.getElementById('pos-drawer-dismiss').hidden=!usable()||!!receipt;
        document.getElementById('pos-drawer-title').textContent={loading:'Checking drawer',open:'Open your POS drawer',unlock:'Drawer locked',mismatch:'Check register branch',overview:'Your active drawer',transactions:'Shift transactions',move:'Cash-in / cash-out',close:'Count & close POS',closed:'Shift closed'}[view]||'Your drawer';
        document.getElementById('pos-drawer-subtitle').textContent=s?'Shift #'+s.id+' · '+s.register_name+' · '+s.branch_name+' · Opened '+s.opened_at:'';
        document.getElementById('pos-drawer-status').textContent=!state?'Checking drawer…':!s?'Open a drawer to start using the POS':s.locked?'Drawer locked · '+s.register_name:'Shift #'+s.id+' · '+s.register_name;
        document.getElementById('pos-drawer-lock').disabled=!usable()||!state.permissions.lock_cash_drawer;
        document.getElementById('pos-drawer-exit').textContent=s&&!s.locked?'Count & close before leaving':'Leave POS';
        if(state){
            dialog.querySelectorAll('form[data-drawer-action]').forEach(form=>{
                if(form.dataset.drawerAction==='open')return;
                const id=s?String(s.id):'';
                if(form.dataset.shiftId!==undefined && form.dataset.shiftId!==id){form.reset();delete form.dataset.requestKey;}
                form.dataset.shiftId=id;
            });
            const select=document.getElementById('pos-drawer-register'),selected=select.value;select.replaceChildren(new Option('Choose available register',''));
            for(const r of state.registers)select.add(new Option(r.name,r.id));select.value=Array.from(select.options).some(o=>o.value===selected)?selected:'';
            openingCash();
            const unavailable=state.schedule_allowed===false||!state.permissions.open_cash_shift||!state.registers.length;
            document.querySelector('[data-drawer-action="open"]').hidden=unavailable;
            document.getElementById('pos-drawer-opening-intro').hidden=unavailable;
            document.getElementById('pos-drawer-unavailable').hidden=!unavailable;
            const noSchedule=state.schedule_allowed===false,noPermission=!state.permissions.open_cash_shift;
            document.getElementById('pos-drawer-unavailable-title').textContent=noSchedule?'Your schedule is not active':noPermission?'Manager assistance required':'No register is available';
            document.getElementById('pos-drawer-open-help').textContent=noSchedule?'Ask your manager to review your schedule for this branch.':noPermission?'An authorized manager needs to open your cashier shift before you can start.':'The registers in this branch may be in use, inactive, or still awaiting setup.';
            document.getElementById('pos-drawer-setup-first').textContent=noSchedule?'Have your working schedule and availability checked.':noPermission?'Ask a manager to open a register and assign your shift.':'Ask a manager to activate a register or reconcile and close its existing shift.';
            document.getElementById('pos-drawer-setup-second').textContent=noPermission?'Check again after your manager confirms the opening.':'Once everything is ready, check availability to continue.';
            document.querySelector('[data-drawer-action="close"]').hidden=!state.permissions.close_cash_shift;
            document.getElementById('pos-drawer-close-help').textContent=state.permissions.close_cash_shift?'Any overage or shortage requires an explanation.':'An authorized manager must close your shift.';
            document.querySelector('[data-drawer-action="move"]').hidden=!state.permissions.cash_drawer_movements;
        }
        document.querySelectorAll('[data-drawer-total]').forEach(el=>{el.textContent=money(s&&s.totals?s.totals[el.dataset.drawerTotal]:0);});
    }
    function show(requested){render(requested);if(!dialog.open)dialog.showModal();}
    async function refresh(initial=false){
        if(busy)return;
        try{
            const result=await call(dialog.open&&view==='transactions'&&usable()?'transactions':'status');
            if(result.state.transactions)transactions(result.state.transactions);
            render(view);
            if((initial && sessionStorage.getItem('pos-drawer-internal')!=='1') || (!usable()&&!receipt))show(view);
            if(initial)sessionStorage.removeItem('pos-drawer-internal');
        }catch(e){fail(e.message);if(initial)show('loading');}
    }
    dialog.addEventListener('cancel',event=>{if(!usable()||receipt||busy)event.preventDefault();else pendingExit=null;});
    document.getElementById('pos-drawer-dismiss').addEventListener('click',()=>{if(usable()&&!busy){pendingExit=null;dialog.close();}});
    document.getElementById('pos-drawer-continue').addEventListener('click',()=>{if(usable()&&!busy){pendingExit=null;dialog.close();}});
    document.querySelectorAll('[data-drawer-view]').forEach(button=>button.addEventListener('click',async()=>{
        if(busy)return;
        error.hidden=true;const requested=button.dataset.drawerView;show(requested);
        if(requested==='transactions'&&usable())try{const result=await call('transactions');transactions(result.state.transactions);render('transactions');}catch(e){fail(e.message);await refresh();}
    }));
    document.getElementById('pos-drawer-lock').addEventListener('click',async()=>{
        if(busy)return;busy=true;try{await call('lock');show('unlock');}catch(e){fail(e.message);show(view);}finally{busy=false;}
    });
    dialog.querySelectorAll('form[data-drawer-action]').forEach(form=>{
        form.addEventListener('input',()=>{delete form.dataset.requestKey;});
        form.addEventListener('submit',async event=>{
            event.preventDefault();if(busy||!form.reportValidity())return;busy=true;error.hidden=true;
            const button=form.querySelector('button[type="submit"]');button.disabled=true;form.inert=true;
            try{
                const result=await call(form.dataset.drawerAction,form);delete form.dataset.requestKey;
                if(result.closing){receipt=result.closing;const r=receipt;document.getElementById('pos-drawer-closing-summary').textContent='Shift #'+r.id+' · '+r.register_name+' · '+r.branch_name+'\nExpected cash: '+money(Math.round(Number(r.expected_cash)*100))+'\nCounted cash: '+money(Math.round(Number(r.counted_cash)*100))+'\nOverage / shortage: '+money(Math.round(Number(r.difference)*100))+'\nStatus: '+r.status.replaceAll('_',' ')+'\nClosed: '+r.closed_at+'\n'+(r.close_notes||'Balanced drawer');show('closed');}
                else{form.reset();show('overview');}
            }catch(e){fail(e.message);await refresh();}finally{
                const secret=form.querySelector('[name="secret"]');if(secret)secret.value='';busy=false;button.disabled=false;form.inert=false;
            }
        });
    });
    document.getElementById('pos-drawer-new').addEventListener('click',async()=>{
        if(busy)return;busy=true;const button=document.getElementById('pos-drawer-new');button.disabled=true;
        try{await call('status');receipt=null;pendingExit=null;document.querySelector('[data-drawer-action="open"]').reset();show('open');}
        catch(e){fail(e.message);}finally{busy=false;button.disabled=false;}
    });
    document.getElementById('pos-drawer-retry').addEventListener('click',async()=>{
        if(busy)return;busy=true;const button=document.getElementById('pos-drawer-retry'),label=button.querySelector('span');button.disabled=true;label.textContent='Checking…';error.hidden=true;
        try{await call('status');show('open');}catch(e){fail(e.message);}finally{busy=false;button.disabled=false;label.textContent='Check availability';}
    });
    document.getElementById('pos-drawer-print').addEventListener('click',()=>window.print());
    document.getElementById('pos-drawer-leave').addEventListener('click',()=>{internalNavigation=true;location.assign(pendingExit||bar.dataset.exit);});
    document.addEventListener('click',event=>{
        const a=event.target.closest('a[href]');if(!a||a.target==='_blank'||event.ctrlKey||event.metaKey||event.shiftKey)return;
        if(busy&&dialog.contains(a)){event.preventDefault();return;}
        const target=new URL(a.href,location.href);if(target.origin!==location.origin||!['http:','https:'].includes(target.protocol))return;
        if(target.searchParams.get('route')==='pos'){internalNavigation=true;sessionStorage.setItem('pos-drawer-internal','1');return;}
        if(state&&state.shift&&!state.shift.locked&&!receipt){event.preventDefault();pendingExit=target.href;show('close');}
    },true);
    document.addEventListener('submit',event=>{const f=event.target;if(f.querySelector('input[name="route"][value="pos"]')){internalNavigation=true;sessionStorage.setItem('pos-drawer-internal','1');}},true);
    document.addEventListener('keydown',event=>{if(dialog.open)event.stopImmediatePropagation();},true);
    window.addEventListener('beforeunload',event=>{if(state&&state.shift&&!state.shift.locked&&!internalNavigation&&!receipt){event.preventDefault();event.returnValue='';}});
    window.addEventListener('pagehide',()=>{
        if(state&&state.shift&&!state.shift.locked&&!receipt&&!internalNavigation&&state.permissions.lock_cash_drawer){const data=new FormData();data.set('action','lock');data.set('shift_id',state.shift.id);data.set('csrf_token',token());data.set('request_key',key());navigator.sendBeacon(bar.dataset.endpoint,data);}
    });
    document.addEventListener('visibilitychange',()=>{if(!document.hidden)refresh();});
    show('loading');refresh(true);setInterval(()=>refresh(),5000);
});
