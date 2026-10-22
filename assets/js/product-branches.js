$(function () {
    const decimals = field => {
        if (field.value === '') return;
        const number=Number(field.value);
        if (Number.isFinite(number) && number>=0) field.value=number.toFixed(2);
    };
    $(document).on('shown.bs.modal','#createProductModal, #editProductModal',function () {
        this.querySelectorAll('[data-stock-summary]').forEach(decimals);
    });
    $(document).on('blur change','[data-stock-summary]',function () { decimals(this); });
    const form=document.getElementById('transfer-product-form');
    if (!form) return;
    const feedback=document.getElementById('transfer-product-feedback');
    function notice(message,state=''){
        feedback.textContent=message;feedback.dataset.state=state;
        feedback.setAttribute('role',state==='error'?'alert':'status');feedback.setAttribute('aria-live',state==='error'?'assertive':'polite');
        if(state==='error'){feedback.focus();feedback.scrollIntoView({block:'nearest',behavior:'smooth'});}
    }
    let busy=false;
    const key=()=>Array.from(crypto.getRandomValues(new Uint8Array(16)),value=>value.toString(16).padStart(2,'0')).join('');
    $(document).on('click','.transfer-product-btn',function () {
        if (busy) return;
        form.reset();notice('');
        const ingredient = this.dataset.ingredient === '1';
        document.getElementById('transfer-product-title').textContent = ingredient ? 'Transfer ingredient to another branch' : 'Transfer product to another branch';
        form.querySelector('button[type="submit"]').textContent = ingredient ? 'Transfer ingredient & stock' : 'Transfer product & stock';
        form.elements.product_id.value=this.dataset.id;
        form.elements.source_branch_id.value=this.dataset.sourceBranch;
        form.elements.request_key.value=key();
        const title=this.closest('.dropdown')?.querySelector('.menu-title');
        document.getElementById('transfer-product-description').textContent=title ? title.textContent.trim() : 'Product #'+this.dataset.id;
        Array.from(form.elements.destination_branch_id.options).forEach(option=>{option.disabled=false;});
        bootstrap.Modal.getOrCreateInstance(document.getElementById('transferProductModal')).show();
    });
    form.addEventListener('submit',function (event) {
        event.preventDefault();
        if (busy || !form.reportValidity()) return;
        busy=true; notice('Transferring product and stock…','loading');
        const data=new FormData(form);
        data.set('action','transfer_product_branch');data.set('csrf_token',document.querySelector('meta[name="csrf-token"]').content);
        const controls=Array.from(form.querySelectorAll('button,select'));
        controls.forEach(control=>{control.disabled=true;});
        $.ajax({url:'?route=ajax/inventory_action',type:'POST',data,processData:false,contentType:false,dataType:'json'})
            .done(response=>{
                notice(response.message||(response.success?'Transfer completed.':'Transfer could not be completed.'),response.success?'success':'error');
                if (response.success) window.location.reload();
            })
            .fail(xhr=>{notice(xhr.responseJSON?.message||'No confirmation received. Retry this same transfer to safely check its result.','error');})
            .always(()=>{busy=false;controls.forEach(control=>{control.disabled=false;});});
    });
    $('#transferProductModal').on('hide.bs.modal',event=>{if(busy)event.preventDefault();});
    form.elements.destination_branch_id.addEventListener('change',()=>{form.elements.request_key.value=key();notice('');});
});
