class BranchOverviewModal {
    constructor(root, endpoint) {
        this.endpoint = endpoint;
        this.el = document.createElement('div');
        this.el.id = 'branchViewModal'; this.el.className = 'modal fade'; this.el.tabIndex = -1;
        this.el.setAttribute('aria-labelledby', 'branchViewTitle');
        this.el.innerHTML = `<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><div><span class="bv-eyebrow">BRANCH OVERVIEW</span><h2 id="branchViewTitle">Branch details</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="bv-filter"><form><label>From<input type="date" name="from" required></label><label>To<input type="date" name="to" required></label><button type="submit" class="btn btn-primary">Apply dates</button><span>Defaults to month to date</span></form></div>
            <div class="modal-body"><div class="bv-content" aria-live="polite"></div></div>
            <div class="modal-footer"><span>Figures reflect the selected branch only.</span><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
        </div></div>`;
        root.append(this.el);
        this.modal = new bootstrap.Modal(this.el);
        this.content = this.el.querySelector('.bv-content');
        this.form = this.el.querySelector('form');
        this.form.addEventListener('submit', event => { event.preventDefault(); this.load(); });
        root.addEventListener('click', event => { const button = event.target.closest('.branch-view'); if (button) this.open(button.dataset.id); });
        this.el.addEventListener('hidden.bs.modal', () => this.request?.abort());
        this.content.addEventListener('click', event => { const tab = event.target.closest('[role=tab]'); if (tab) this.activate(tab); });
        this.content.addEventListener('keydown', event => {
            const tab = event.target.closest('[role=tab]'); if (!tab || !['ArrowLeft','ArrowRight','Home','End'].includes(event.key)) return;
            const tabs = [...this.content.querySelectorAll('[role=tab]')]; let index = tabs.indexOf(tab);
            if (event.key === 'Home') index=0; else if (event.key === 'End') index=tabs.length-1; else index=(index+(event.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length;
            event.preventDefault(); this.activate(tabs[index]); tabs[index].focus();
        });
    }
    escape(value) { const span=document.createElement('span');span.textContent=String(value ?? '');return span.innerHTML.replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
    money(value) { return new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP'}).format(Number(value || 0)); }
    number(value) { return new Intl.NumberFormat('en-PH',{maximumFractionDigits:4}).format(Number(value || 0)); }
    status(value) { return this.escape(String(value || '').replaceAll('_',' ')); }
    table(headings, rows, empty='No records in this period.') {
        return `<div class="bv-table"><table><thead><tr>${headings.map(h=>`<th scope="col">${h}</th>`).join('')}</tr></thead><tbody>${rows.length?rows.map(row=>`<tr>${row.map(cell=>`<td>${cell}</td>`).join('')}</tr>`).join(''):`<tr><td colspan="${headings.length}">${empty}</td></tr>`}</tbody></table></div>`;
    }
    panel(title, content, note='') { return `<section class="bv-panel"><h3>${title}</h3>${note?`<p class="bv-note">${note}</p>`:''}${content}</section>`; }
    metric(label, value, note='') { return `<div class="bv-metric"><span>${label}</span><strong>${value}</strong>${note?`<small>${note}</small>`:''}</div>`; }
    unavailable() { return '<p class="bv-empty">Your account does not have access to this section.</p>'; }
    open(id) {
        this.id=id; this.form.reset(); this.el.querySelector('#branchViewTitle').textContent='Branch details';
        this.modal.show(); this.load();
    }
    async load() {
        this.request?.abort(); this.request=new AbortController(); const signal=this.request.signal;
        const url=new URL(this.endpoint,location.href);url.searchParams.set('action','overview');url.searchParams.set('id',this.id);
        for (const field of ['from','to']) if(this.form.elements[field].value)url.searchParams.set(field,this.form.elements[field].value);
        this.content.innerHTML='<p class="bv-empty" role="status">Loading branch overview…</p>';
        this.content.setAttribute('aria-busy','true');this.form.querySelector('button').disabled=true;
        try {
            const response=await fetch(url,{signal,headers:{'Accept':'application/json'}});const result=await response.json();
            if(!response.ok || !result.success)throw new Error(result.message || 'Unable to load this branch.');
            if(signal.aborted)return;
            this.form.elements.from.value=result.data.from;this.form.elements.to.value=result.data.to;
            this.render(result.data);
        } catch(error) { if(error.name!=='AbortError')this.content.innerHTML=`<p class="bv-error" role="alert">${this.escape(error.message)}</p>`; }
        finally { if(!signal.aborted){this.content.setAttribute('aria-busy','false');this.form.querySelector('button').disabled=false;} }
    }
    activate(tab) {
        this.content.querySelectorAll('[role=tab]').forEach(button=>{const active=button===tab;button.setAttribute('aria-selected',String(active));button.tabIndex=active?0:-1;this.content.querySelector('#'+button.getAttribute('aria-controls')).hidden=!active;});
    }
    render(data) {
        const e=value=>this.escape(value),m=value=>this.money(value),n=value=>this.number(value),s=data.sales,b=data.branch;
        this.el.querySelector('#branchViewTitle').textContent=b.name;
        const hero=`<div class="bv-identity"><img src="${e(b.full_image_url)}" alt="${e(b.name)}"><div><span class="bv-eyebrow">BRANCH #${e(String(b.id).padStart(3,'0'))} · ${e(b.status)}</span><h3>${e(b.name)}</h3><p>${e(b.address || 'No address provided')}</p><p>${e(b.phone || 'No phone provided')}</p><p>Manager: <strong>${e(b.manager_name || 'Unassigned')}</strong></p></div></div>`;
        let overview=this.unavailable();
        if(s) {
            const max=Math.max(1,...s.trend.map(row=>Number(row.total)));
            overview=`<div class="bv-metrics">${this.metric('Selected-period sales',m(s.revenue))}${this.metric('Transactions',n(s.transactions))}${this.metric('Average order',m(s.average_order))}${this.metric('Estimated gross profit',m(s.gross_profit))}${data.expenses?this.metric('Recorded expenses',m(data.expenses.total))+this.metric('Profit after recorded expenses',m(s.operating_profit_estimate),'Estimate'):''}</div>
            <p class="bv-note">Completed sales after discounts. Estimated item costs: ${m(s.cost)}. Recorded stock costs are used when available, otherwise current product costs. Gross profit excludes operating expenses. Profit after expenses subtracts recorded branch expenses as entered; it is not audited net profit.</p>
            <div class="bv-metrics bv-calendar">${s.calendar.map(row=>this.metric(e(row.label),m(row.total),n(row.transactions)+' transactions')).join('')}</div>
            <p class="bv-note">Calendar summaries run through today; weeks start on Monday. Other sections use the selected dates.</p>
            ${this.panel('Sales trend',this.table(['Date','Sales','Transactions'],s.trend.map(row=>[e(row.day),`<div class="bv-trend"><span style="width:${Math.max(0,Number(row.total)/max*100)}%"></span><b>${m(row.total)}</b></div>`,n(row.transactions)])),'Days with completed sales in the selected range.')}
            <div class="bv-columns">${this.panel('Payment methods',this.table(['Method','Sales','Transactions'],s.payments.map(row=>[e(row.method),m(row.total),n(row.transactions)])))}${this.panel('Best-selling products',this.table(['Product','Units','Item sales'],s.best_sellers.map(row=>[e(row.name || 'Deleted product'),n(row.quantity),m(row.total)])),'Top 10 by quantity. Item sales are before order-level discounts.')}</div>`;
        }
        const team=this.panel('Assigned branch team',this.table(['Name','Roles'],data.team.map(row=>[e(row.name),e(row.roles || 'No roles')]),'No staff assigned.'),n(data.team.length)+' staff members. The branch manager is shown above.');
        const stock=this.panel('Low-stock alerts',data.stock?this.table(['Product','Available','Threshold'],data.stock.items.map(row=>[e(row.name),n(row.stock)+' '+e(row.unit),n(row.threshold)]),'No low-stock alerts.'):this.unavailable(),data.stock?n(data.stock.count)+' alerts · Showing up to 20. Current sellable stock, excluding expired batches.':'');
        const expenses=data.expenses?`<div class="bv-metrics">${this.metric('Recorded expenses',m(data.expenses.total))}${this.metric('Expense records',n(data.expenses.count))}</div>${this.panel('Expenses by category',this.table(['Category','Records','Total'],data.expenses.categories.map(row=>[e(row.category),n(row.count),m(row.total)])))}${this.panel('Recent expenses',this.table(['Date','Category','Description','Amount'],data.expenses.recent.map(row=>[e(row.expense_date),e(row.category),e(row.description),m(row.amount)])),'Latest 20 records by expense date. Unassigned company expenses are excluded.')}`:this.unavailable();
        let purchasing=this.unavailable();
        if(data.purchases)purchasing=data.purchases.ready?this.panel('Purchase-order summary',this.table(['Status','Orders','Ordered value'],data.purchases.statuses.map(row=>[this.status(row.status),n(row.count),m(row.total)])),'Orders created within the selected dates. Ordered value is not paid spend and is not subtracted again from profit.')+this.panel('Recent purchase orders',this.table(['Order','Supplier','Status','Expected','Ordered value'],data.purchases.recent.map(row=>['PO #'+e(row.id),e(row.supplier),this.status(row.status),e(row.expected_date || 'Not set'),m(row.total)])),'Latest 20 orders. Includes draft, submitted, approved, received and cancelled records where present.'):'<p class="bv-empty">Purchasing is not set up yet.</p>';
        let shifts=this.unavailable();
        if(data.shifts)shifts=data.shifts.ready?this.table(['Shift','Cashier','Status','Expected cash','Counted cash','Difference'],data.shifts.items.map(row=>['#'+e(row.id),e(row.cashier),this.status(row.status),row.expected_cash===null?'Not closed':m(row.expected_cash),row.counted_cash===null?'Not closed':m(row.counted_cash),row.difference===null?'Not closed':m(row.difference)]),'No matching shifts.'):'<p class="bv-empty">Shift tracking is not set up yet.</p>';
        const sections=[['performance','Performance',overview],['team','Team & stock',team+stock],['expenses','Expenses',expenses],['purchasing','Purchases & shifts',purchasing+this.panel('Cashier shifts',shifts,'Latest 20: open shifts and pending reviews, plus shifts closed in the selected period.')]];
        this.content.innerHTML=hero+`<div class="bv-tabs" role="tablist" aria-label="Branch information">${sections.map(([id,label],i)=>`<button type="button" role="tab" id="bv-tab-${id}" aria-controls="bv-${id}" aria-selected="${i===0}" tabindex="${i===0?0:-1}">${label}</button>`).join('')}</div>`+sections.map(([id,label,html],i)=>`<div role="tabpanel" id="bv-${id}" aria-labelledby="bv-tab-${id}" ${i?'hidden':''}>${html}</div>`).join('');
    }
}
