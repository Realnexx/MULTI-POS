const fs=require('fs'),vm=require('vm'),assert=require('assert');
const elements=Object.fromEntries(['pos-promo-code','pos-promo-customer','pos-promo-status','cartDiscount'].map(k=>[k,{value:'',textContent:''}]));
let receive,loaded=0;const jq=()=>({ajaxSuccess:f=>receive=f});jq.ajax=()=>({});
vm.runInNewContext(fs.readFileSync('assets/js/pos-promotions.js','utf8'),{document:{getElementById:id=>elements[id]},jQuery:jq,URLSearchParams,window:{},loadCart:()=>loaded++});
function reply(response,action){receive({}, {responseJSON:response},{url:'?route=ajax/pos_cart',data:'action='+action});}
reply({success:true,manual_discount:20,financial:{discount_cents:2000}},'load');assert.equal(elements.cartDiscount.value,'20.00');
reply({success:false,message:'Supervisor approval required'},'set_discount');assert.equal(elements['pos-promo-status'].textContent,'Supervisor approval required');assert.equal(elements.cartDiscount.value,'20.00');
reply({success:true,promotion:{name:'Summer offer'},financial:{discount_cents:1000},manual_discount:0},'load');assert.equal(elements['pos-promo-status'].textContent,'Applied: Summer offer · ₱10.00');assert.equal(elements.cartDiscount.value,'0.00');
elements['pos-promo-code'].value='SUMMER';elements['pos-promo-customer'].value='customer@example.invalid';reply({success:true,sale_id:1},'checkout');assert.equal(elements['pos-promo-code'].value,'');assert.equal(elements['pos-promo-customer'].value,'');assert.equal(elements['pos-promo-status'].textContent,'');
const markup=fs.readFileSync('views/modules/pos.php','utf8');assert(markup.includes('type="button" onclick="updateCartDiscount()"'));
console.log('5 POS interface regression checks passed: reload, approval error, automatic offer, checkout reset, explicit Apply action.');
