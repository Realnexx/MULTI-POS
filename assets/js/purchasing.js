document.addEventListener('DOMContentLoaded', function () {
    const list=document.getElementById('purchase-order-lines');
    const template=document.getElementById('purchase-line-template');
    const add=document.getElementById('purchase-add-line');
    const suggestions=document.getElementById('purchase-product-suggestions');
    if (!list || !template || !add || !suggestions) return;
    const products=new Map(Array.from(suggestions.options,option=>[option.value.trim().toLocaleLowerCase(),option]));
    function selectProduct(input) {
        const line=input.closest('fieldset');
        const id=line.querySelector('[data-purchase-field="product_id"]');
        const cost=line.querySelector('[data-purchase-field="unit_cost"]');
        const product=products.get(input.value.trim().toLocaleLowerCase());
        const nextId=product ? product.dataset.productId : '';
        if (id.value!==nextId) cost.value=product ? product.dataset.cost : '';
        id.value=nextId;
        input.setCustomValidity(input.value && !product ? 'Choose a product from the suggestions for this branch.' : '');
    }
    let index=0;
    function addLine() {
        if (list.children.length>=50) return;
        const fragment=template.content.cloneNode(true);
        fragment.querySelectorAll('[data-purchase-field]').forEach(field=>{field.name='items['+index+']['+field.dataset.purchaseField+']';});
        index++; list.appendChild(fragment); add.disabled=list.children.length>=50;
    }
    add.addEventListener('click',addLine);
    list.addEventListener('click',function(event){
        const remove=event.target.closest('[data-remove-purchase-line]');
        if (!remove) return;
        remove.closest('fieldset').remove(); add.disabled=false;
        if (!list.children.length) addLine();
    });
    const onProductInput=event=>{if (event.target.matches('[data-purchase-product]')) selectProduct(event.target);};
    list.addEventListener('input',onProductInput);
    list.addEventListener('change',onProductInput);
    addLine();
});
