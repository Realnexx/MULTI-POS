let posCheckoutKey = '';
// Global CSRF Token Helper
function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content') || '';
}
// Set Order Type (Dine-in vs Take-out)
function setOrderType(type) {
    const btnDineIn = $('#btnOrderTypeDineIn');
    const btnTakeOut = $('#btnOrderTypeTakeOut');
    
    if (!btnDineIn.length || !btnTakeOut.length) return;
    btnDineIn.attr('aria-pressed', type === 'Dine-in' ? 'true' : 'false');
    btnTakeOut.attr('aria-pressed', type === 'Take-out' ? 'true' : 'false');
    
    const activeClasses = ['bg-surface-container-lowest', 'text-primary', 'font-bold', 'shadow-xs'];
    const inactiveClasses = ['text-on-surface-variant', 'font-medium', 'hover:bg-surface-container-highest', 'bg-transparent'];
    
    if (type === 'Dine-in') {
        inactiveClasses.forEach(cls => btnDineIn.removeClass(cls));
        activeClasses.forEach(cls => btnDineIn.addClass(cls));
        activeClasses.forEach(cls => btnTakeOut.removeClass(cls));
        inactiveClasses.forEach(cls => btnTakeOut.addClass(cls));
    } else {
        inactiveClasses.forEach(cls => btnTakeOut.removeClass(cls));
        activeClasses.forEach(cls => btnTakeOut.addClass(cls));
        activeClasses.forEach(cls => btnDineIn.removeClass(cls));
        inactiveClasses.forEach(cls => btnDineIn.addClass(cls));
    }
    
    $('#orderType').val(type);
    
    // Save to session via AJAX
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: {
            action: 'set_order_type',
            order_type: type,
            csrf_token: getCsrfToken()
        },
        dataType: 'json',
        success: function(response) {
            if (!response.success) {
                console.error('Failed to sync order type session:', response.message);
            }
        },
        error: function() {
            console.error('Network error syncing order type session.');
        }
    });
}
// Load Cart State
function loadCart() {
    if (!$('#cartItemsList').length) return;
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: { action: 'load', csrf_token: getCsrfToken() },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                renderCart(response.cart, response.subtotal, response.discount, response.total); renderFinance(response.financial);
            }
        },
        error: function() {
            console.error('Failed to load POS cart.');
        }
    });
}
// Add Item to Cart
function addToCart(productId, productName, price, stock, modifiersJson = '') {
    if (stock <= 0) {
        showWarningModal('This item is currently sold out!', 'Out of Stock', 'Review Inventory', 'inventory_2');
        return;
    }
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: {
            action: 'add',
            product_id: productId,
            product_name: productName,
            price: price,
            stock: stock,
            modifiers: modifiersJson,
            csrf_token: getCsrfToken()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                renderCart(response.cart, response.subtotal, response.discount, response.total); renderFinance(response.financial);
            } else {
                showWarningModal(response.message || 'Failed to add item.', 'Stock Limitation', 'Review Inventory', 'inventory_2');
            }
        },
        error: function() {
            showWarningModal('Error communicating with server.', 'Validation Error', 'Review Inventory', 'inventory_2');
        }
    });
}
// ─── MODIFIER MODAL ───────────────────────────────────────────────────────
let _pendingModifier = null;
function openModifierModal(productId, productName, basePrice, stock, modifiers) {
    _pendingModifier = { productId, productName, basePrice, stock, modifiers, selected: [] };
    $('#modifierModalTitle').text(productName);
    $('#modifierModalSubtitle').text('Customize your order — ' + modifiers.length + ' add-on(s) available');
    const list = $('#modifierList');
    list.empty();
    modifiers.forEach((m, i) => {
        const price = parseFloat(m.additional_price) || 0;
        list.append(`
            <label class="flex items-center gap-3 p-3 rounded-xl border border-outline-variant border-opacity-30 hover:border-primary hover:border-opacity-50 transition-all cursor-pointer bg-surface-container-low modifier-option ${price > 0 ? '' : ''}">
                <input type="checkbox" class="modifier-checkbox shadow-none" data-index="${i}" data-price="${price}" style="accent-color: var(--color-primary, #6366f1); width: 18px; height: 18px; cursor: pointer;">
                <div class="flex-grow min-w-0">
                    <div class="font-bold text-sm text-on-surface">${m.modifier_name}</div>
                    ${m.ingredient_product_id ? '<div class="text-[10px] text-on-surface-variant opacity-60">Deducts from ingredient stock</div>' : ''}
                </div>
                <div class="font-extrabold text-sm text-primary shrink-0">${price > 0 ? '+₱' + price.toFixed(2) : '<span class="text-on-surface-variant opacity-50 font-normal">Free</span>'}</div>
            </label>
        `);
    });
    $('#modifierTotalPrice').text('₱' + basePrice.toFixed(2));
    updateModifierTotal();
    $('#modifierModal').removeClass('hidden opacity-0').addClass('flex opacity-100');
    $('#modifierModalContent').removeClass('scale-95').addClass('scale-100');
    $(document).on('change', '.modifier-checkbox', updateModifierTotal);
}
function updateModifierTotal() {
    if (!_pendingModifier) return;
    let extra = 0;
    $('.modifier-checkbox:checked').each(function() {
        extra += parseFloat($(this).data('price')) || 0;
    });
    const total = _pendingModifier.basePrice + extra;
    $('#modifierTotalPrice').text('₱' + total.toFixed(2));
}
function closeModifierModal() {
    $('#modifierModal').addClass('hidden opacity-0').removeClass('flex opacity-100');
    $('#modifierModalContent').addClass('scale-95').removeClass('scale-100');
    $(document).off('change', '.modifier-checkbox', updateModifierTotal);
    _pendingModifier = null;
}
function confirmAddWithModifiers() {
    if (!_pendingModifier) return;
    const selected = [];
    $('.modifier-checkbox:checked').each(function() {
        const idx = parseInt($(this).data('index'));
        selected.push(_pendingModifier.modifiers[idx]);
    });
    _pendingModifier.selected = selected;
    const modStr = selected.length > 0 ? JSON.stringify(selected) : '';
    addToCart(_pendingModifier.productId, _pendingModifier.productName, _pendingModifier.basePrice, _pendingModifier.stock, modStr);
    closeModifierModal();
}
// Update Item Quantity
function updateCartQty(productId, quantity) {
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: {
            action: 'update_qty',
            product_id: productId,
            quantity: quantity,
            csrf_token: getCsrfToken()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                renderCart(response.cart, response.subtotal, response.discount, response.total); renderFinance(response.financial);
            } else {
                showWarningModal(response.message || 'Failed to update quantity.', 'Stock Limitation', 'Review Inventory', 'inventory_2');
                loadCart(); // reload to revert invalid value
            }
        }
    });
}
// Remove Item from Cart
function removeFromCart(productId) {
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: {
            action: 'remove',
            product_id: productId,
            csrf_token: getCsrfToken()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                renderCart(response.cart, response.subtotal, response.discount, response.total); renderFinance(response.financial);
            } else {
                showWarningModal(response.message || 'Could not remove this item.', 'Cart update', 'Review cart', 'shopping_cart');
            }
        },
        error: function() { showWarningModal('Could not reach the server. Please try removing the item again.', 'Cart update', 'Try again', 'shopping_cart'); }
    });
}
// Open Custom Confirm Clear Modal
function clearCart() {
    openConfirmModal();
}
// Execute Cart Clear AJAX request
function executeClearCart() {
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: { action: 'clear', csrf_token: getCsrfToken() },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                renderCart([], 0, 0, 0);
                $('#cartDiscount').val(0);
                $('#amountTendered').val('');
                $('#changeAmount').text('₱0.00');
                closeConfirmModal();
            }
        }
    });
}
// Update Discount
function updateCartDiscount() {
    const discount = parseFloat($('#cartDiscount').val()) || 0;
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: {
            action: 'set_discount',
            discount: discount,
            csrf_token: getCsrfToken()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                renderCart(response.cart, response.subtotal, response.discount, response.total); renderFinance(response.financial);
            }
        }
    });
}
// Render Cart UI Helper
function renderCart(cartItems, subtotal, discount, total) {
    const container = $('#cartItemsList');
    container.empty();
    if (!cartItems || cartItems.length === 0) {
        $('#checkoutBtn').prop('disabled', true);
        $('#proceedToPayBtn').prop('disabled', true);
        $('#cartItemCount').text('0');
        $('#cartItemCountPlural').text('s');
    } else {
        $('#checkoutBtn').prop('disabled', false);
        $('#proceedToPayBtn').prop('disabled', false);
        $('#cartItemCount').text(cartItems.length);
        $('#cartItemCountPlural').text(cartItems.length === 1 ? '' : 's');
        cartItems.forEach(item => {
            container.append(`
                <div class="cart-item-card flex items-center gap-sm bg-surface-container-low p-2.5 rounded-xl border border-outline-variant/20 hover:border-primary/35 transition-all duration-300">
                    <div class="w-12 h-12 bg-surface-container rounded-lg overflow-hidden shrink-0 border border-outline-variant/30">
                        <img src="${item.image_url || 'https://images.unsplash.com/photo-1551024506-0bccd828d307?q=80&w=100&auto=format&fit=crop'}" class="w-full h-full object-cover" alt="${item.name}">
                    </div>
                    <div class="flex-grow min-w-0">
                        <h4 class="font-bold text-sm text-on-surface truncate m-0 font-label-md">${item.name}</h4>
                        ${item.bundle_components?.length ? `<details class="pos-cart-included"><summary>Included items (${item.bundle_components.length})</summary>${(item.bundle_components||[]).map(component=>`<div class="pos-bundle-included">${Number(component.quantity)*Number(item.quantity)} × ${String(component.name).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]))}</div>`).join('')}</details>` : ''}
                        ${item.modifiers && item.modifiers.length > 0 ? `<div class="text-[10px] text-on-surface-variant opacity-70 mt-0.5 leading-tight">${item.modifiers.map(m => '+' + m.modifier_name).join(', ')}</div>` : ''}
                        <p class="text-xs text-primary font-bold m-0 mt-0.5">₱${(item.subtotal || item.price * item.quantity).toFixed(2)}</p>
                    </div>
                    <div class="flex items-center gap-1.5 bg-surface-container-high rounded-full px-2 py-0.5 shrink-0">
                        <button type="button" aria-label="Decrease item quantity" onclick="updateCartQty(${item.id}, ${item.quantity - 1})" class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-surface-variant hover:text-primary transition-all text-on-surface-variant border-0 p-0" style="background:none;">
                            <span class="material-symbols-outlined text-[14px] font-bold">remove</span>
                        </button>
                        <input aria-label="Item quantity" type="number" min="1" max="${item.max_stock || 999}" value="${item.quantity}" onchange="updateCartQty(${item.id}, parseInt(this.value) || 1)" onkeydown="if(event.key === 'Enter') this.blur()" onwheel="event.preventDefault()" class="font-bold text-xs w-8 text-center text-on-surface bg-transparent border-0 outline-none p-0 focus:ring-0 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" style="box-shadow: none;">
                        <button type="button" aria-label="Increase item quantity" onclick="updateCartQty(${item.id}, ${item.quantity + 1})" class="w-5 h-5 flex items-center justify-center rounded-full hover:bg-surface-variant hover:text-primary transition-all text-on-surface-variant border-0 p-0" style="background:none;">
                            <span class="material-symbols-outlined text-[14px] font-bold">add</span>
                        </button>
                    </div>
                    <button type="button" aria-label="Remove item from cart" onclick="removeFromCart(${item.id})" class="w-7 h-7 flex items-center justify-center rounded-full text-on-surface-variant opacity-50 hover:opacity-100 hover:text-error hover:bg-error-container/10 transition-all border-0 p-0 cursor-pointer" style="background:none;" title="Delete item">
                        <span class="pos-cart-remove-label">Remove</span>
                    </button>
                </div>
            `);
        });
    }
    $('#cartSubtotal').text('₱' + parseFloat(subtotal).toFixed(2));
    $('#cartTotal').text('₱' + parseFloat(total).toFixed(2));
    
    // Recalculate change if amount tendered is already filled
    calculateChange();
}
// Calculate Change Amount
function calculateChange() {
    const totalText = $('#cartTotal').text().replace('₱', '').replace(',', '');
    const total = parseFloat(totalText) || 0;
    const tendered = parseFloat($('#amountTendered').val()) || 0;
    if (tendered >= total && total > 0) {
        const change = tendered - total;
        $('#changeAmount').text('₱' + change.toFixed(2));
        $('#checkoutBtn').prop('disabled', false);
    } else {
        $('#changeAmount').text('₱0.00');
        $('#checkoutBtn').prop('disabled', total <= 0);
    }
}
// Quick Cash Handler
function selectQuickCash(amount) {
    const totalText = $('#cartTotal').text().replace('₱', '').replace(',', '');
    const total = parseFloat(totalText) || 0;
    
    if (total <= 0) {
        return;
    }
    let tenderedValue = 0;
    if (amount === 'exact') {
        tenderedValue = total;
    } else {
        tenderedValue = parseFloat(amount) || 0;
    }
    $('#amountTendered').val(tenderedValue.toFixed(2));
    calculateChange();
}
// Show modular validation warning modal overlay
function showWarningModal(message, title = 'Validation Error', buttonText = 'Correct Payment Amount', icon = 'undo') {
    $('#validationModalTitle').text(title);
    $('#validationModalMessage').text(message);
    $('#validationModalBtnText').text(buttonText);
    
    const iconSpan = $('#validationModalBtnIcon');
    if (iconSpan.length) {
        iconSpan.text(icon);
    }
    
    const modal = $('#validationModal');
    const modalContent = $('#validationModalContent');
    
    modal.removeClass('hidden');
    // Force reflow
    modal[0].offsetHeight;
    modal.addClass('opacity-100').removeClass('opacity-0');
    modalContent.addClass('scale-100').removeClass('scale-95');
}
// Close validation warning modal overlay
function closeValidationModal() {
    const modal = $('#validationModal');
    const modalContent = $('#validationModalContent');
    
    modal.addClass('opacity-0').removeClass('opacity-100');
    modalContent.addClass('scale-95').removeClass('scale-100');
    
    setTimeout(() => {
        modal.addClass('hidden');
        // Refocus amount input for rapid correction ONLY if the payment modal is visible
        if ($('#paymentModal').is(':visible')) {
            setTimeout(() => {
                $('#amountTendered').focus().select();
            }, 50);
        }
    }, 300);
}
// Open Custom Confirm Clear Modal
function openConfirmModal() {
    const modal = $('#confirmModal');
    const modalContent = $('#confirmModalContent');
    
    modal.removeClass('hidden');
    // Force reflow
    modal[0].offsetHeight;
    modal.addClass('opacity-100').removeClass('opacity-0');
    modalContent.addClass('scale-100').removeClass('scale-95');
}
// Close Custom Confirm Clear Modal
function closeConfirmModal() {
    const modal = $('#confirmModal');
    const modalContent = $('#confirmModalContent');
    
    modal.addClass('opacity-0').removeClass('opacity-100');
    modalContent.addClass('scale-95').removeClass('scale-100');
    
    setTimeout(() => {
        modal.addClass('hidden');
    }, 300);
}
// Process Checkout
function processCheckout() {
    const totalText = $('#cartTotal').text().replace('₱', '').replace(',', '');
    const total = parseFloat(totalText) || 0;
    const tendered = parseFloat($('#amountTendered').val()) || 0;
    const paymentMethod = $('#paymentMethod').val() || 'Cash';
    const orderType = $('#orderType').val() || 'Dine-in';
    if (total <= 0) {
        showWarningModal('Cannot checkout an empty cart.', 'Validation Error', 'Correct Payment Amount', 'undo');
        return;
    }
    if (tendered < total) {
        showWarningModal('Amount tendered is less than the total due amount.', 'Validation Error', 'Correct Payment Amount', 'undo');
        return;
    }
    $('#checkoutBtn').prop('disabled', true).html(`
        <span class="animate-spin material-symbols-outlined text-[20px]">sync</span>
        <span>Processing...</span>
    `);
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: {
            action: 'checkout',
            request_key: posCheckoutKey,
            payment_method: paymentMethod,
            amount_tendered: tendered,
            order_type: orderType,
            csrf_token: getCsrfToken()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                // Show a stunning custom success transition overlay inside the modal instead of a blocking browser alert
                $('#paymentSuccessScreen').removeClass('hidden');
                
                // Update stocks dynamically without a manual refresh
                if (response.updated_stocks) {
                    updateProductStocks(response.updated_stocks);
                }
                
                // Allow a brief delay for cashier feedback, then render the receipt in-modal
                setTimeout(() => {
                    showReceiptModal(response.sale_id);
                }, 1300);
            } else {
                showWarningModal(response.message || 'Checkout failed.', 'Validation Error', 'Correct Payment Amount', 'undo');
                $('#checkoutBtn').prop('disabled', false).html(`
                    Complete Payment
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                `);
            }
        },
        error: function() {
            showWarningModal('Error processing transaction.', 'Validation Error', 'Correct Payment Amount', 'undo');
            $('#checkoutBtn').prop('disabled', false).html(`
                Complete Payment
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
            `);
        }
    });
}
// Update Product Stocks Dynamically in the DOM without a page refresh
function updateProductStocks(updatedStocks) {
    if (!updatedStocks || !Array.isArray(updatedStocks)) return;
    updatedStocks.forEach(item => {
        const card = $(`.product-card[data-product-id="${item.id}"]`);
        if (card.length) {
            // Update stock attributes
            card.attr('data-product-stock', item.stock);
            card.data('product-stock', item.stock); // update jQuery cache
            // Update opacity & badge wrapper
            const badgeWrapper = card.find('.stock-badge-wrapper');
            if (item.stock <= 0) {
                card.addClass('opacity-50');
                badgeWrapper.html(`
                    <span class="bg-error/90 backdrop-blur-md text-on-error text-[10px] px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[12px] font-bold">block</span> Sold Out
                    </span>
                `);
            } else if (item.stock <= item.threshold) {
                card.removeClass('opacity-50');
                badgeWrapper.html(`
                    <span class="bg-amber-500/90 backdrop-blur-md text-white text-[10px] px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[12px] font-bold">warning</span> Low Stock: ${item.stock}
                    </span>
                `);
            } else {
                card.removeClass('opacity-50');
                badgeWrapper.html(`
                    <span class="bg-emerald-500/90 backdrop-blur-md text-white text-[10px] px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                        <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span> In Stock: ${item.stock}
                    </span>
                `);
            }
        }
    });
}
// Set Payment Method Selector (Cash, GCash, Card)
function setPaymentMethod(method) {
    if(window.manualPaymentMethods && !window.manualPaymentMethods.includes(method))method=window.manualPaymentMethods[0];
    $('#manualPaymentChoice').val(method);
    $('#paymentMethod').val(method);
    document.querySelectorAll('.pos-payment-option').forEach(button=>{const selected=button.dataset.paymentMethod===method;button.classList.toggle('is-selected',selected);button.setAttribute('aria-pressed',selected?'true':'false');});
    
    const btnCash = $('#btnPayCash');
    const btnGCash = $('#btnPayGCash');
    const btnCard = $('#btnPayCard');
    
    const activeClasses = 'bg-gradient-to-r from-primary to-secondary text-on-primary shadow-md';
    const inactiveClasses = 'bg-surface-container-low text-on-surface-variant border-outline-variant hover:border-primary hover:text-primary';
    
    // Reset all buttons to inactive styling
    [btnCash, btnGCash, btnCard].forEach(btn => {
        if (btn.length) {
            btn.removeClass(activeClasses).addClass(inactiveClasses);
        }
    });
    
    // Highlight selected button
    if (method === 'Cash') {
        if (btnCash.length) btnCash.addClass(activeClasses).removeClass(inactiveClasses);
        
        // Show cash presets and change panels, make amount tendered editable
        $('#presetsSection, #changeSection').slideDown(250);
        $('#amountTendered').prop('readonly', false).removeClass('opacity-70');
        
        // Refocus on amount input
        setTimeout(() => {
            $('#amountTendered').focus().select();
        }, 100);
    } else {
        if (method === 'GCash' && btnGCash.length) {
            btnGCash.addClass(activeClasses).removeClass(inactiveClasses);
        } else if (method === 'Credit Card' && btnCard.length) {
            btnCard.addClass(activeClasses).removeClass(inactiveClasses);
        }
        
        // For cashless payments, auto-fill exact total due and hide presets/change panels
        const totalText = $('#cartTotal').text().replace('₱', '').replace(',', '');
        const total = parseFloat(totalText) || 0;
        
        $('#amountTendered').val(total.toFixed(2)).prop('readonly', true).addClass('opacity-70');
        $('#presetsSection, #changeSection').slideUp(250);
    }
    
    calculateChange();
}
// Open Payment Modal
function openPaymentModal() {
    const modal = $('#paymentModal');
    const modalContent = $('#paymentModalContent');
    const totalText = $('#cartTotal').text();
    
    $('#modalTotalDue').text(totalText);
    
    // Reset complete payment button text and restore icon
    $('#checkoutBtn').html(`
        Complete Payment
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
    `);
    
    // Reset payment channel to default Cash on opening
    setPaymentMethod('Cash');
    $('#amountTendered').val('');
    $('#changeAmount').text('₱0.00');
    
    modal.removeClass('hidden');
    // Force reflow to trigger CSS transitions
    modal[0].offsetHeight;
    modal.addClass('opacity-100').removeClass('opacity-0');
    modalContent.addClass('scale-100').removeClass('scale-95');
    
    // Automatically focus and select the Amount Tendered text box for fast cashier typing
    setTimeout(() => {
        $('#amountTendered').focus().select();
    }, 200);
}
// Close Payment Modal
function closePaymentModal() {
    const modal = $('#paymentModal');
    const modalContent = $('#paymentModalContent');
    
    modal.addClass('opacity-0').removeClass('opacity-100');
    modalContent.addClass('scale-95').removeClass('scale-100');
    
    setTimeout(() => {
        modal.addClass('hidden');
        $('#paymentSuccessScreen').addClass('hidden');
    }, 300);
}
// Fetch and Show Receipt Modal
function showReceiptModal(saleId) {
    window.currentActiveReceiptSaleId = saleId;
    $.ajax({
        url: '?route=ajax/pos_cart',
        method: 'POST',
        data: {
            action: 'get_receipt',
            sale_id: saleId,
            csrf_token: getCsrfToken()
        },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                const sale = res.sale;
                
                // Format dates elegantly
                const dateObj = new Date(sale.created_at);
                const formattedDate = dateObj.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                }) + ' ' + dateObj.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Pad receipt number with leading zeros
                const escapeReceipt=v=>String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                const receiptNo = escapeReceipt(res.receipt?.number || String(sale.id).padStart(6, '0'));
                const receiptHeader=escapeReceipt(res.receipt?.header || '');
                const receiptFooter=escapeReceipt(res.receipt?.footer || 'Thank you for your visit!');
                Object.keys(res.store||{}).forEach(k=>{res.store[k]=escapeReceipt(res.store[k]);});
                sale.items.forEach(item=>{item.product_name=escapeReceipt(item.product_name);item.name=escapeReceipt(item.name);(item.modifiers||[]).forEach(m=>{m.modifier_name=escapeReceipt(m.modifier_name);});});

                
                // Construct items list HTML
                let itemsHtml = '';
                sale.items.forEach(item => {
                    let modsHtml = '';
                    modsHtml+=(item.bundle_components||[]).map(component=>`<div class="text-xs text-on-surface-variant">${Number(component.quantity)*Number(item.quantity)} × ${escapeReceipt(component.name)} (included)</div>`).join('');
                    if (item.modifiers && item.modifiers.length > 0) {
                        item.modifiers.forEach(mod => {
                            const modPrice = parseFloat(mod.additional_price || 0);
                            modsHtml += `
                                <div class="flex justify-between items-center text-[10px] pl-3 mt-0.5" style="color: #8a6e70;">
                                    <span class="font-medium">+ ${mod.modifier_name}</span>
                                    ${modPrice > 0 ? `<span class="font-semibold" style="color: #b60e3d;">₱${modPrice.toFixed(2)}</span>` : ''}
                                </div>
                            `;
                        });
                    }
                    itemsHtml += `
                        <div class="flex flex-col text-xs pb-3 border-b border-dashed border-outline-variant border-opacity-30 last:border-0 last:pb-0 last:border-b-0">
                            <div class="flex justify-between items-baseline gap-2">
                                <span class="font-bold text-on-surface text-sm text-left truncate flex-grow max-w-[70%]">${item.product_name}</span>
                                <div class="dot-leader"></div>
                                <span class="font-extrabold text-on-surface text-sm whitespace-nowrap">₱${parseFloat(item.subtotal).toFixed(2)}</span>
                            </div>
                            <div class="flex justify-between items-center mt-1 text-[11px] text-on-surface-variant opacity-80">
                                <span class="font-semibold text-primary bg-primary/5 px-2 py-0.5 rounded">${item.quantity} x ₱${parseFloat(item.price).toFixed(2)}</span>
                            </div>
                            ${modsHtml}
                        </div>
                    `;
                });
                
                // Set custom color templates based on payment method
                let paymentTheme = {
                    bg: 'bg-emerald-50/80',
                    border: 'border-emerald-200/60',
                    text: 'text-emerald-800',
                    accent: 'text-emerald-600',
                    icon: 'payments',
                    glow: 'shadow-[0_4px_12px_rgba(16,185,129,0.04)]'
                };
                if (sale.payment_method === 'GCash') {
                    paymentTheme = {
                        bg: 'bg-blue-50/80',
                        border: 'border-blue-200/60',
                        text: 'text-blue-800',
                        accent: 'text-blue-600',
                        icon: 'account_balance_wallet',
                        glow: 'shadow-[0_4px_12px_rgba(59,130,246,0.04)]'
                    };
                } else if (sale.payment_method === 'Credit Card') {
                    paymentTheme = {
                        bg: 'bg-purple-50/80',
                        border: 'border-purple-200/60',
                        text: 'text-purple-800',
                        accent: 'text-purple-600',
                        icon: 'credit_card',
                        glow: 'shadow-[0_4px_12px_rgba(147,51,234,0.04)]'
                    };
                }
                
                const receiptHtml = `
                    <!-- Premium Styled Receipt Stylesheet -->
                    <style>
                        .thermal-ticket {
                            background-color: #fdfdfa !important;
                            border: 1px solid #e5e4dc !important;
                            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                            border-radius: 20px;
                            font-family: 'Inter', sans-serif;
                            position: relative;
                            color: #2b2d2f;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                        }
                        .serrated-edge {
                            height: 8px;
                            background-image: linear-gradient(135deg, transparent 33.33%, #fdfdfa 33.33%, #fdfdfa 66.67%, transparent 66.67%), linear-gradient(225deg, transparent 33.33%, #fdfdfa 33.33%, #fdfdfa 66.67%, transparent 66.67%);
                            background-size: 10px 10px;
                            background-position: 0 0;
                            background-repeat: repeat-x;
                            width: 100%;
                            z-index: 10;
                        }
                        .serrated-top {
                            position: absolute;
                            top: 0;
                            left: 0;
                            transform: translateY(-4px);
                        }
                        .serrated-bottom {
                            position: absolute;
                            bottom: 0;
                            left: 0;
                            transform: translateY(4px) scaleY(-1);
                        }
                        .dot-leader {
                            border-bottom: 2px dotted #dfded4;
                            flex-grow: 1;
                            margin: 0 6px;
                            position: relative;
                            top: -4px;
                        }
                        .stamp-paid {
                            font-family: 'Montserrat', sans-serif;
                            color: #b60e3d;
                        }
                        @media print {
                            .thermal-ticket {
                                box-shadow: none !important;
                                border: none !important;
                                background-color: #ffffff !important;
                            }
                        }
                    </style>
                    <div class="thermal-ticket p-5 md:p-6 relative overflow-hidden my-2 select-none">
                        <!-- Top serrated edge -->
                        <div class="serrated-edge serrated-top"></div>
                        
                        <!-- Vintage "PAID" Watermark Stamp -->
                        <div class="absolute inset-0 pointer-events-none select-none flex items-center justify-center z-0 opacity-15 stamp-paid">
                            <div class="transform rotate-[-20deg] scale-[2.2] text-center font-black uppercase tracking-widest flex flex-col items-center justify-center">
                                <span class="text-sm font-bold tracking-widest">OFFICIAL</span>
                                <span class="text-2xl tracking-wider font-extrabold mt-1">PAID</span>
                                <span class="text-xs font-bold mt-1 tracking-wider">${sale.payment_method}</span>
                            </div>
                        </div>
                        <!-- Header -->
                        <div class="text-center mb-6 pb-5 border-b border-dashed border-outline-variant border-opacity-40 relative">
                            <p>${receiptHeader}</p><h3 class="font-extrabold text-2xl text-primary m-0 font-headline-md tracking-wider uppercase font-headline-lg">${res.store.name}</h3>
                            <div class="space-y-1 mt-3 text-[11px] text-on-surface-variant opacity-85">
                                <p class="m-0 flex items-center justify-center gap-1.5 font-medium"><span class="material-symbols-outlined text-[13px] text-primary">location_on</span> ${res.store.address}</p>
                                <p class="m-0 flex items-center justify-center gap-1.5 font-medium"><span class="material-symbols-outlined text-[13px] text-primary">call</span> ${res.store.phone}</p>
                            </div>
                        </div>
                        <!-- Transaction Metadata -->
                        <div class="bg-[#f5f4ed] bg-opacity-40 rounded-2xl p-4 border border-[#e8e7df] space-y-2.5 mb-5 text-xs text-[#444648]">
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Receipt No:</span>
                                <span class="font-extrabold text-sm text-primary font-mono tracking-wider">#${receiptNo}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Date & Time:</span>
                                <span class="font-bold text-on-surface text-[11px]">${formattedDate}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Branch:</span>
                                <span class="font-bold text-on-surface flex items-center gap-1.5 text-[11px]">
                                    <span class="material-symbols-outlined text-[14px] text-primary">store</span>
                                    ${sale.branch_name || 'Main'}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Cashier:</span>
                                <span class="font-bold text-on-surface flex items-center gap-1.5 text-[11px]">
                                    <span class="material-symbols-outlined text-[14px] text-primary">badge</span>
                                    ${sale.cashier_name || 'Staff'}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Order Type:</span>
                                <span class="font-bold text-on-surface flex items-center gap-1.5 text-[11px]">
                                    <span class="material-symbols-outlined text-[14px] text-primary">${sale.order_type === 'Take-out' ? 'takeout_dining' : 'dine_in'}</span>
                                    ${sale.order_type || 'Dine-in'}
                                </span>
                            </div>
                        </div>
                        <!-- Itemized List -->
                        <div class="mb-5">
                            <div class="flex justify-between text-uppercase text-[10px] font-extrabold text-on-surface-variant opacity-70 mb-3.5 tracking-widest pb-2 border-b border-[#e8e7df]">
                                <span>Item Description</span>
                                <span>Amount</span>
                            </div>
                            <div class="space-y-4">
                                ${itemsHtml}
                            </div>
                        </div>
                        <!-- Totals Section -->
                        <div class="bg-[#fcfbf7] rounded-2xl p-4 border border-[#e8e7df] space-y-2.5 mb-5 text-xs text-[#444648] shadow-xs">
                            <div class="flex justify-between">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Subtotal:</span>
                                <span class="font-bold text-on-surface">₱${parseFloat(sale.total_amount).toFixed(2)}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Discount:</span>
                                <span class="font-bold text-error bg-error-container bg-opacity-20 px-2 py-0.5 rounded text-[10px]">- ₱${parseFloat(sale.discount_amount).toFixed(2)}</span>
                            </div>
                            <p>Tax ${res.receipt?.tax_inclusive?'(included)':''}: PHP ${((res.receipt?.tax_cents||0)/100).toFixed(2)} · Service charge: PHP ${((res.receipt?.service_cents||0)/100).toFixed(2)}</p>
                            <div class="flex justify-between items-center pt-3.5 mt-3 border-t border-dashed border-[#e8e7df] text-on-surface font-headline-md font-bold">
                                <span class="text-xs uppercase tracking-wider font-extrabold">Total Due</span>
                                <span class="text-primary text-xl font-black tracking-wide">₱${parseFloat(sale.final_amount).toFixed(2)}</span>
                            </div>
                        </div>
                        <!-- Payment details and Change Banner -->
                        <div class="bg-[#fcfbf7] border border-[#e8e7df] rounded-2xl p-4 space-y-2.5 mb-5 text-xs text-[#444648] shadow-xs">
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Payment Method:</span>
                                <span class="font-extrabold text-on-surface flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-primary">${paymentTheme.icon}</span>
                                    ${sale.payment_method}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="opacity-80 font-bold uppercase tracking-wider text-[10px]">Amount Tendered:</span>
                                <span class="font-bold text-on-surface">₱${parseFloat(sale.amount_tendered).toFixed(2)}</span>
                            </div>
                            
                            <!-- Change Banner -->
                            <div class="flex justify-between items-center ${paymentTheme.bg} ${paymentTheme.text} rounded-xl p-3 px-4 border ${paymentTheme.border} ${paymentTheme.glow} mt-3">
                                <span class="font-extrabold text-[10px] uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] font-bold">price_check</span> Change Paid
                                </span>
                                <span class="font-black text-lg ${paymentTheme.accent}">₱${parseFloat(sale.change_amount).toFixed(2)}</span>
                            </div>
                        </div>
                        <!-- Simulated Barcode -->
                        <div class="flex flex-col items-center justify-center my-6 py-2.5 bg-white rounded-2xl border border-[#e8e7df] border-opacity-40">
                            <div class="flex justify-center items-center gap-[1.5px] opacity-80 h-8 px-4">
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1px] h-full bg-transparent"></div>
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                                <div class="w-[2px] h-full bg-transparent"></div>
                                
                                <div class="w-[1px] h-full bg-[#191c1d]"></div>
                                <div class="w-[3px] h-full bg-transparent"></div>
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1px] h-full bg-transparent"></div>
                                
                                <div class="w-[4px] h-full bg-[#191c1d]"></div>
                                <div class="w-[2px] h-full bg-transparent"></div>
                                <div class="w-[1px] h-full bg-[#191c1d]"></div>
                                <div class="w-[2px] h-full bg-transparent"></div>
                                
                                <div class="w-[1.5px] h-full bg-transparent"></div>
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1px] h-full bg-transparent"></div>
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1.5px] h-full bg-transparent"></div>
                                
                                <div class="w-[3px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1px] h-full bg-transparent"></div>
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                                <div class="w-[2px] h-full bg-transparent"></div>
                                
                                <div class="w-[4px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1px] h-full bg-transparent"></div>
                                <div class="w-[3px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1px] h-full bg-transparent"></div>
                                
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                                <div class="w-[1px] h-full bg-transparent"></div>
                                <div class="w-[2px] h-full bg-[#191c1d]"></div>
                            </div>
                            <span class="font-mono text-[9px] tracking-[6px] text-on-surface-variant font-bold mt-1.5 opacity-80 text-center uppercase pr-[-6px]">*${receiptNo}*</span>
                        </div>
                        <!-- Footer -->
                        <div class="text-center text-on-surface-variant opacity-85 mt-4">
                            <div class="flex items-center justify-center gap-1.5 mb-1.5">
                                <span class="text-xs text-primary">★</span>
                                <p class="font-extrabold text-xs text-on-surface m-0 font-headline-md tracking-wider uppercase">${receiptFooter}</p>
                                <span class="text-xs text-primary">★</span>
                            </div>
                            <p class="text-[10px] font-semibold text-on-surface-variant m-0 opacity-75">Please come again to ${res.store.name}</p>
                        </div>
                        
                        <!-- Bottom serrated edge -->
                        <div class="serrated-edge serrated-bottom"></div>
                    </div>
                `;
                
                // Inject receipt HTML
                $('#receiptModalBody').html(receiptHtml);
                
                // Gracefully close payment modal
                closePaymentModal();
                
                // Reset order type tabs to Dine-in dynamically in client
                setOrderType('Dine-in');
                
                // Clear active order inputs
                renderCart([], 0, 0, 0);
                $('#cartDiscount').val(0);
                $('#amountTendered').val('');
                $('#changeAmount').text('₱0.00');
                
                // Open the receipt modal with a slight offset delay
                setTimeout(() => {
                    openReceiptModal();
                }, 200);
            } else {
                alert(res.message || 'Failed to fetch receipt transaction details.');
            }
        },
        error: function() {
            alert('Error communicating with database for receipt retrieval.');
        }
    });
}
// Open Receipt Modal
function openReceiptModal() {
    const modal = $('#receiptModal');
    const modalContent = $('#receiptModalContent');
    
    modal.removeClass('hidden');
    // Force reflow
    modal[0].offsetHeight;
    modal.addClass('opacity-100').removeClass('opacity-0');
    modalContent.addClass('scale-100').removeClass('scale-95');
}
// Close Receipt Modal
function closeReceiptModal() {
    const modal = $('#receiptModal');
    const modalContent = $('#receiptModalContent');
    
modal.addClass('opacity-0').removeClass('opacity-100');
    modalContent.addClass('scale-95').removeClass('scale-100');
    
    setTimeout(() => {
        modal.addClass('hidden');
        loadCart(); // Sync dashboard active cart
    }, 300);
}
// Print Modal Receipt in Dedicated Workstation or Isolated Print Frame
function printModalReceipt() {
    if (window.currentActiveReceiptSaleId) {
        window.location.href = '?route=pos/receipt&id=' + window.currentActiveReceiptSaleId;
        return;
    }
    const receiptContent = $('#historyReceiptModalBody').length ? $('#historyReceiptModalBody').html() : $('#receiptModalBody').html();
    
    let iframe = document.getElementById('receiptPrintIframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'receiptPrintIframe';
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);
    }
    
    const doc = iframe.contentWindow.document;
    doc.open();
    doc.write(`
        <html>
        <head>
            <title>Print Receipt</title>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
            <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    theme: {
                        extend: {
                            colors: {
                                "primary": "#b60e3d",
                                "secondary": "#8429c8",
                                "on-surface": "#191c1d",
                                "on-surface-variant": "#5a4042",
                                "error": "#ba1a1a",
                                "error-container": "#ffdad6"
                            }
                        }
                    }
                }
            </script>
            <style>
                html, body {
                    overflow: hidden !important;
                    scrollbar-width: none !important;
                    -ms-overflow-style: none !important;
                    font-family: 'Inter', sans-serif;
                    color: #2b2d2f;
                    margin: 0;
                    padding: 4px;
                    background-color: #ffffff !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                html::-webkit-scrollbar, body::-webkit-scrollbar {
                    display: none !important;
                }
                .print-container {
                    width: 58mm;
                    max-width: 58mm;
                    margin: 0 auto;
                    overflow: hidden !important;
                }
                /* Premium Thermal Ticket Styling for Modal Receipt */
                .thermal-ticket {
                    background-color: #fdfdfa !important;
                    border: 1px solid #e5e4dc !important;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
                    border-radius: 20px;
                    font-family: 'Inter', sans-serif;
                    position: relative;
                    color: #2b2d2f;
                    padding: 20px 16px;
                    margin: 8px auto;
                    box-sizing: border-box;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    width: 100%;
                    max-width: 100%;
                    overflow: hidden !important;
                }
                .serrated-edge {
                    height: 8px;
                    background-image: linear-gradient(135deg, transparent 33.33%, #fdfdfa 33.33%, #fdfdfa 66.67%, transparent 66.67%), linear-gradient(225deg, transparent 33.33%, #fdfdfa 33.33%, #fdfdfa 66.67%, transparent 66.67%);
                    background-size: 10px 10px;
                    background-position: 0 0;
                    background-repeat: repeat-x;
                    width: 100%;
                    z-index: 10;
                    position: absolute;
                    left: 0;
                }
                .serrated-top {
                    top: 0;
                    transform: translateY(-4px);
                }
                .serrated-bottom {
                    bottom: 0;
                    transform: translateY(4px) scaleY(-1);
                }
                .stamp-paid {
                    position: absolute;
                    right: 16px;
                    top: 108px;
                    pointer-events: none;
                    transform: rotate(-15deg);
                    border: 3px dashed rgba(182, 14, 61, 0.25);
                    color: rgba(182, 14, 61, 0.55);
                    font-family: 'Montserrat', sans-serif;
                    padding: 4px 12px;
                    border-radius: 12px;
                    text-align: center;
                    font-weight: 900;
                    font-size: 10px;
                    letter-spacing: 0.15em;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                }
                .stamp-title {
                    font-size: 7px;
                    font-weight: 700;
                    letter-spacing: 0.2em;
                    opacity: 0.8;
                }
                .stamp-main {
                    font-size: 12px;
                    font-weight: 800;
                    letter-spacing: 0.1em;
                }
                .stamp-method {
                    font-size: 7px;
                    font-weight: 700;
                    letter-spacing: 0.1em;
                    margin-top: 2px;
                }
                .receipt-header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 16px;
                    border-bottom: 1px dashed rgba(226, 190, 192, 0.4);
                    position: relative;
                }
                .receipt-icon-wrap {
                    width: 56px;
                    height: 56px;
                    background: linear-gradient(135deg, rgba(244, 63, 94, 0.1), rgba(244, 63, 94, 0.05));
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #b60e3d;
                    margin: 0 auto 10px auto;
                    border: 1px solid rgba(182, 14, 61, 0.15);
                    position: relative;
                }
                .receipt-icon-wrap .material-symbols-outlined {
                    font-size: 30px;
                    font-weight: 900;
                    filter: drop-shadow(0 2px 4px rgba(182, 14, 61, 0.2));
                }
                .receipt-icon-wrap .sparkle {
                    position: absolute;
                    top: 2px;
                    right: 6px;
                    font-size: 9px;
                }
                .receipt-brand {
                    font-family: 'Montserrat', sans-serif;
                    font-weight: 800;
                    font-size: 20px;
                    color: #b60e3d;
                    margin: 0;
                    letter-spacing: 0.05em;
                    text-transform: uppercase;
                }
                .receipt-contact {
                    margin-top: 10px;
                    font-size: 10px;
                    color: #5a4042;
                    opacity: 0.85;
                }
                .receipt-contact p {
                    margin: 2px 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 4px;
                    font-weight: 500;
                }
                .receipt-contact .material-symbols-outlined {
                    font-size: 11px;
                    color: #b60e3d;
                }
                .receipt-metadata {
                    background-color: rgba(245, 244, 237, 0.4);
                    border: 1px solid #e8e7df;
                    border-radius: 12px;
                    padding: 12px;
                    margin-bottom: 16px;
                    font-size: 11px;
                    color: #444648;
                }
                .meta-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 8px;
                }
                .meta-row:last-child {
                    margin-bottom: 0;
                }
                .meta-label {
                    opacity: 0.8;
                    font-weight: 700;
                    text-transform: uppercase;
                    font-size: 9px;
                    letter-spacing: 0.05em;
                }
                .meta-value {
                    font-weight: 700;
                    color: #191c1d;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                }
                .meta-value.highlight {
                    color: #b60e3d;
                    font-family: monospace;
                    font-size: 12px;
                    letter-spacing: 0.05em;
                }
                .meta-value .material-symbols-outlined {
                    font-size: 12px;
                    color: #b60e3d;
                }
                .receipt-items {
                    margin-bottom: 16px;
                }
                .items-header {
                    display: flex;
                    justify-content: space-between;
                    font-size: 9px;
                    font-weight: 800;
                    color: #5a4042;
                    opacity: 0.7;
                    text-transform: uppercase;
                    letter-spacing: 0.08em;
                    padding-bottom: 6px;
                    border-bottom: 1px solid #e8e7df;
                    margin-bottom: 12px;
                }
                .items-list {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                }
                .item-row {
                    display: flex;
                    flex-direction: column;
                    padding-bottom: 8px;
                    border-bottom: 1px dashed rgba(232, 231, 223, 0.4);
                }
                .item-row:last-child {
                    border-bottom: none;
                    padding-bottom: 0;
                }
                .item-main-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: baseline;
                    gap: 6px;
                }
                .item-name {
                    font-weight: 700;
                    color: #191c1d;
                    font-size: 12px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    max-width: 70%;
                }
                .dot-leader {
                    border-bottom: 1px dotted #dfded4;
                    flex-grow: 1;
                    margin: 0 4px;
                    position: relative;
                    top: -4px;
                }
                .item-subtotal {
                    font-weight: 800;
                    color: #191c1d;
                    font-size: 12px;
                    white-space: nowrap;
                }
                .item-sub-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 2px;
                }
                .item-qty-price {
                    font-weight: 600;
                    color: #b60e3d;
                    background-color: rgba(182, 14, 61, 0.05);
                    padding: 1px 6px;
                    border-radius: 4px;
                    font-size: 10px;
                }
                .receipt-summary {
                    background-color: #fcfbf7;
                    border: 1px solid #e8e7df;
                    border-radius: 12px;
                    padding: 12px;
                    margin-bottom: 16px;
                    font-size: 11px;
                    color: #444648;
                }
                .summary-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 8px;
                }
                .summary-row:last-of-type {
                    margin-bottom: 0;
                }
                .summary-label {
                    opacity: 0.8;
                    font-weight: 700;
                    text-transform: uppercase;
                    font-size: 9px;
                    letter-spacing: 0.05em;
                }
                .summary-value {
                    font-weight: 750;
                    color: #191c1d;
                }
                .summary-value.discount {
                    color: #ba1a1a;
                    background-color: rgba(186, 26, 26, 0.08);
                    padding: 1px 6px;
                    border-radius: 4px;
                    font-size: 9px;
                    font-weight: 700;
                }
                .total-due-row {
                    border-top: 1px dashed #e8e7df;
                    padding-top: 10px;
                    margin-top: 10px;
                    margin-bottom: 0;
                }
                .total-due-row .summary-label {
                    font-size: 11px;
                    font-weight: 800;
                }
                .total-due-row .total-amount {
                    color: #b60e3d;
                    font-size: 16px;
                    font-weight: 900;
                    letter-spacing: 0.02em;
                }
                .receipt-payment {
                    background-color: #fcfbf7;
                    border: 1px solid #e8e7df;
                    border-radius: 12px;
                    padding: 12px;
                    margin-bottom: 16px;
                    font-size: 11px;
                    color: #444648;
                }
                .payment-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 8px;
                }
                .payment-row:last-of-type {
                    margin-bottom: 0;
                }
                .payment-label {
                    opacity: 0.8;
                    font-weight: 700;
                    text-transform: uppercase;
                    font-size: 9px;
                    letter-spacing: 0.05em;
                }
                .payment-value {
                    font-weight: 800;
                    color: #191c1d;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                }
                .payment-value .material-symbols-outlined {
                    font-size: 14px;
                    color: #b60e3d;
                }
                .change-banner {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-radius: 10px;
                    padding: 10px 12px;
                    border: 1px solid;
                    margin-top: 10px;
                }
                .change-banner.theme-cash {
                    background-color: rgba(16, 185, 129, 0.05);
                    border-color: rgba(16, 185, 129, 0.2);
                    color: #065f46;
                }
                .change-banner.theme-gcash {
                    background-color: rgba(59, 130, 246, 0.05);
                    border-color: rgba(59, 130, 246, 0.2);
                    color: #1e40af;
                }
                .change-banner.theme-card {
                    background-color: rgba(147, 51, 234, 0.05);
                    border-color: rgba(147, 51, 234, 0.2);
                    color: #6b21a8;
                }
                .change-label {
                    font-weight: 800;
                    font-size: 9px;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    display: flex;
                    align-items: center;
                    gap: 4px;
                }
                .change-label .material-symbols-outlined {
                    font-size: 14px;
                    font-weight: 700;
                }
                .change-amount {
                    font-weight: 900;
                    font-size: 15px;
                }
                .receipt-barcode {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    margin: 20px 0;
                    padding: 8px;
                    background-color: #ffffff;
                    border: 1px solid rgba(232, 231, 223, 0.4);
                    border-radius: 12px;
                }
                .barcode-lines {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 1.5px;
                    opacity: 0.8;
                    height: 28px;
                    padding: 0 12px;
                }
                .barcode-lines .line {
                    height: 100%;
                    background-color: #191c1d;
                }
                .barcode-lines .line.thin { width: 1px; }
                .barcode-lines .line.medium { width: 2px; }
                .barcode-lines .line.thick { width: 3px; }
                .barcode-lines .line.extra-thick { width: 4px; }
                .barcode-lines .space {
                    height: 100%;
                    background-color: transparent;
                }
                .barcode-lines .space.thin { width: 1px; }
                .barcode-lines .space.medium { width: 2px; }
                .barcode-lines .space.thick { width: 3px; }
                .barcode-text {
                    font-family: monospace;
                    font-size: 8px;
                    letter-spacing: 4px;
                    color: #5a4042;
                    font-weight: 700;
                    margin-top: 4px;
                    opacity: 0.8;
                    text-transform: uppercase;
                }
                .receipt-footer {
                    text-align: center;
                    color: #5a4042;
                    opacity: 0.85;
                    margin-top: 12px;
                }
                .footer-stars {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 4px;
                    margin-bottom: 4px;
                }
                .footer-stars .star {
                    font-size: 10px;
                    color: #b60e3d;
                }
                .footer-thanks {
                    font-family: 'Montserrat', sans-serif;
                    font-weight: 800;
                    font-size: 10px;
                    color: #191c1d;
                    margin: 0;
                    letter-spacing: 0.05em;
                    text-transform: uppercase;
                }
                .footer-tagline {
                    font-size: 9px;
                    font-weight: 600;
                    margin: 0;
                    opacity: 0.75;
                }
                @media print {
                    @page {
                        size: 58mm auto;
                        margin: 0;
                    }
                    html, body {
                        width: 58mm !important;
                        max-width: 58mm !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background-color: #ffffff !important;
                        color: #000000 !important;
                    }
                    .print-container {
                        width: 58mm !important;
                        max-width: 58mm !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    .thermal-ticket {
                        width: 58mm !important;
                        max-width: 58mm !important;
                        padding: 2mm 2.5mm !important;
                        margin: 0 !important;
                        border: none !important;
                        box-shadow: none !important;
                        background-color: #ffffff !important;
                        background-image: none !important;
                        border-radius: 0 !important;
                    }
                    /* Hide decorative zigzags on physical print */
                    .serrated-edge {
                        display: none !important;
                    }
                    /* Scale down headers */
                    .thermal-ticket h3 {
                        font-size: 14px !important;
                        margin-bottom: 2px !important;
                    }
                    /* Adjust text sizes to fit 58mm cleanly without wrapping items */
                    .thermal-ticket p, .thermal-ticket span, .thermal-ticket div, .thermal-ticket td {
                        font-size: 10px !important;
                        line-height: 1.2 !important;
                    }
                    .thermal-ticket .text-sm {
                        font-size: 10px !important;
                    }
                    .thermal-ticket .text-xs {
                        font-size: 9px !important;
                    }
                    .thermal-ticket .text-xl {
                        font-size: 12px !important;
                    }
                    .thermal-ticket .text-2xl {
                        font-size: 13px !important;
                    }
                    /* Itemized list compact styling */
                    .thermal-ticket .font-bold.text-on-surface.text-sm {
                        font-size: 10px !important;
                        max-width: 65% !important; /* restrict item name width to prevent overlapping */
                    }
                    .thermal-ticket .font-extrabold.text-on-surface.text-sm {
                        font-size: 10px !important;
                    }
                    /* Dotted leader adjustments */
                    .dot-leader {
                        border-bottom: 1px dotted #000000 !important;
                        opacity: 0.6 !important;
                        top: -2px !important;
                    }
                    /* Slanted PAID stamp */
                    .stamp-paid {
                        right: 3mm !important;
                        top: 18mm !important;
                        font-size: 9px !important;
                        padding: 2px 4px !important;
                        border: 2px dashed #000000 !important;
                        color: #000000 !important;
                        opacity: 0.8 !important;
                    }
                    /* High contrast blocks for thermal printer */
                    .thermal-ticket .bg-\[\#f5f4ed\], 
                    .thermal-ticket .bg-\[\#fcfbf7\],
                    .thermal-ticket .bg-emerald-50\/80,
                    .thermal-ticket .bg-blue-50\/80,
                    .thermal-ticket .bg-purple-50\/80 {
                        background-color: #ffffff !important;
                        border: 1px dashed #000000 !important;
                        color: #000000 !important;
                        box-shadow: none !important;
                        border-radius: 4px !important;
                        padding: 6px !important;
                    }
                    .thermal-ticket .text-emerald-800,
                    .thermal-ticket .text-blue-800,
                    .thermal-ticket .text-purple-800,
                    .thermal-ticket .text-emerald-600,
                    .thermal-ticket .text-blue-600,
                    .thermal-ticket .text-purple-600,
                    .thermal-ticket .text-primary {
                        color: #000000 !important;
                    }
                    /* Barcode adjustments */
                    .thermal-ticket .my-6 {
                        margin-top: 8px !important;
                        margin-bottom: 8px !important;
                        padding: 4px !important;
                    }
                    .thermal-ticket .h-8 {
                        height: 24px !important;
                    }
                    .thermal-ticket .font-mono {
                        font-size: 8px !important;
                        letter-spacing: 4px !important;
                    }
                    .no-print {
                        display: none !important;
                    }
                    /* Semantic receipt class support */
                    .stamp-title, .stamp-method {
                        font-size: 6px !important;
                    }
                    .stamp-main {
                        font-size: 10px !important;
                    }
                    .receipt-header {
                        margin-bottom: 12px !important;
                        padding-bottom: 10px !important;
                        border-bottom: 1px dashed #000000 !important;
                    }
                    .receipt-icon-wrap {
                        display: none !important;
                    }
                    .receipt-brand {
                        font-size: 14px !important;
                    }
                    .receipt-contact p {
                        font-size: 9px !important;
                        margin: 2px 0 !important;
                    }
                    .receipt-metadata {
                        background-color: #ffffff !important;
                        border: 1px dashed #000000 !important;
                        border-radius: 4px !important;
                        padding: 6px !important;
                        margin-bottom: 12px !important;
                        color: #000000 !important;
                    }
                    .meta-row {
                        margin-bottom: 4px !important;
                    }
                    .meta-label {
                        font-size: 8px !important;
                    }
                    .meta-value {
                        font-size: 9px !important;
                    }
                    .meta-value.highlight {
                        font-size: 10px !important;
                        color: #000000 !important;
                    }
                    .receipt-items {
                        margin-bottom: 12px !important;
                    }
                    .items-header {
                        font-size: 8px !important;
                        padding-bottom: 4px !important;
                        margin-bottom: 8px !important;
                        border-bottom: 1px solid #000000 !important;
                    }
                    .items-list {
                        gap: 8px !important;
                    }
                    .item-row {
                        padding-bottom: 6px !important;
                        border-bottom: 1px dashed #000000 !important;
                    }
                    .item-name {
                        font-size: 9px !important;
                        max-width: 65% !important;
                    }
                    .item-subtotal {
                        font-size: 9px !important;
                    }
                    .item-qty-price {
                        font-size: 8px !important;
                        background-color: transparent !important;
                        padding: 0 !important;
                        color: #000000 !important;
                    }
                    .receipt-summary, .receipt-payment {
                        background-color: #ffffff !important;
                        border: 1px dashed #000000 !important;
                        border-radius: 4px !important;
                        padding: 6px !important;
                        margin-bottom: 12px !important;
                        color: #000000 !important;
                    }
                    .summary-row, .payment-row {
                        margin-bottom: 4px !important;
                    }
                    .summary-label, .payment-label {
                        font-size: 8px !important;
                    }
                    .summary-value, .payment-value {
                        font-size: 9px !important;
                        color: #000000 !important;
                    }
                    .summary-value.discount {
                        background-color: transparent !important;
                        padding: 0 !important;
                        color: #000000 !important;
                    }
                    .total-due-row {
                        border-top: 1px dashed #000000 !important;
                        padding-top: 8px !important;
                        margin-top: 6px !important;
                    }
                    .total-due-row .summary-label {
                        font-size: 9px !important;
                    }
                    .total-due-row .total-amount {
                        font-size: 12px !important;
                        color: #000000 !important;
                    }
                    .change-banner {
                        background-color: #ffffff !important;
                        border: 1px solid #000000 !important;
                        color: #000000 !important;
                        padding: 6px !important;
                        margin-top: 8px !important;
                        border-radius: 4px !important;
                    }
                    .change-label {
                        font-size: 8px !important;
                    }
                    .change-amount {
                        font-size: 11px !important;
                    }
                    .receipt-barcode {
                        margin: 12px 0 !important;
                        padding: 4px !important;
                        border: none !important;
                    }
                    .barcode-lines {
                        height: 20px !important;
                    }
                    .barcode-lines .line {
                        background-color: #000000 !important;
                    }
                    .barcode-text {
                        font-size: 8px !important;
                        letter-spacing: 4px !important;
                        margin-top: 4px !important;
                    }
                    .receipt-footer {
                        margin-top: 8px !important;
                    }
                    .footer-stars .star {
                        font-size: 10px !important;
                        color: #000000 !important;
                    }
                    .footer-thanks {
                        font-size: 9px !important;
                        color: #000000 !important;
                    }
                    .footer-tagline {
                        font-size: 8px !important;
                        color: #000000 !important;
                    }
                }
            </style>
        </head>
        <body class="bg-white">
            <div class="print-container">
                ${receiptContent}
            </div>
            <script>
                window.onload = function() {
                    // Give Tailwind CDN and fonts a moment to resolve before triggering the system print dialog
                    setTimeout(() => {
                        window.focus();
                        window.print();
                    }, 400);
                };
            <\/script>
        </body>
        </html>
    `);
    doc.close();
}
function toggleHotkeysGuide(forceState = null) {
    const content = $('#hotkeysGuideContent');
    const text = $('#hotkeysGuideStatusText');
    const chevron = $('#hotkeysGuideChevron');
    
    if (!content.length || !text.length || !chevron.length) return;
    
    let shouldExpand = false;
    if (forceState !== null) {
        shouldExpand = forceState;
    } else {
        shouldExpand = content.hasClass('hidden');
    }
    
    if (shouldExpand) {
        // Expand
        content.removeClass('hidden').addClass('grid');
        text.text('Click to collapse');
        chevron.css('transform', 'rotate(180deg)');
        localStorage.setItem('pos_hotkeys_expanded', 'true');
    } else {
        // Collapse
        content.removeClass('grid').addClass('hidden');
        text.text('Click to expand');
        chevron.css('transform', 'rotate(0deg)');
        localStorage.setItem('pos_hotkeys_expanded', 'false');
    }
}
// Auto-restore Hotkeys Guide State on load
function initHotkeysGuide() {
    const expanded = localStorage.getItem('pos_hotkeys_expanded');
    if (expanded === 'false') {
        toggleHotkeysGuide(false);
    } else {
        toggleHotkeysGuide(true); // Default to expanded (true) if null or 'true'
    }
}
// POS Keyboard Hotkeys Dispatcher
$(document).keydown(function(e) {
    // 1. Focus Search (F7)
    if (e.key === "F7") {
        e.preventDefault();
        $('input[name="search"]').focus().select();
    }
    
    // 2. Clear Active Order (F4)
    if (e.key === "F4") {
        e.preventDefault();
        clearCart();
    }
    
    // 3. Switch to Dine-in (F1)
    if (e.key === "F1") {
        e.preventDefault();
        setOrderType('Dine-in');
    }
    
    // 4. Switch to Take-out (F2)
    if (e.key === "F2") {
        e.preventDefault();
        setOrderType('Take-out');
    }
    
    // 5. Proceed to Payment Modal (F8)
    if (e.key === "F8") {
        e.preventDefault();
        if (!$('#proceedToPayBtn').prop('disabled') && !$('#paymentModal').is(':visible')) {
            openPaymentModal();
        }
    }
    
    // 6. Complete Payment (F9)
    if (e.key === "F9") {
        e.preventDefault();
        if ($('#paymentModal').is(':visible') && !$('#checkoutBtn').prop('disabled')) {
            processCheckout();
        }
    }
    // 7. Close Modals (F10)
    if (e.key === "F10") {
        e.preventDefault();
        closePaymentModal();
        closeReceiptModal();
        closeValidationModal();
        closeConfirmModal();
    }
});
// Product Detail page Add to POS Cart button listener
$(document).ready(function() {
    // Init Hotkeys Guide State
    initHotkeysGuide();
    // Dynamic click listener for POS product cards
    $(document).on('click', '.product-card', function() {
        const card = $(this);
        const id = card.data('product-id');
        const name = card.data('product-name');
        const price = parseFloat(card.data('product-price'));
        const stock = parseInt(card.data('product-stock'));
        if (stock <= 0) {
            showWarningModal('This item is currently sold out!', 'Out of Stock', 'Review Inventory', 'inventory_2');
            return;
        }
        // Check if product has modifiers
        $.post('?route=ajax/inventory_action', { action: 'get_product_modifiers', product_id: id, csrf_token: getCsrfToken() }, function(res) {
            if (res.success && res.modifiers && res.modifiers.length > 0) {
                openModifierModal(id, name, price, stock, res.modifiers);
            } else {
                addToCart(id, name, price, stock);
            }
        }, 'json').fail(function() {
            addToCart(id, name, price, stock);
        });
    });
    $('.add-to-pos-btn').click(function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const price = $(this).data('price');
        $.ajax({
            url: '?route=ajax/pos_cart',
            method: 'POST',
            data: {
                action: 'add',
                product_id: id,
                product_name: name,
                price: price,
                stock: 999, // Allow adding from detail page if in stock
                csrf_token: getCsrfToken()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                if (response.request_key) posCheckoutKey = response.request_key;
                    alert('Product added to POS Cart successfully!');
                    window.location.href = '?route=pos';
                } else {
                    alert(response.message || 'Failed to add product.');
                }
            }
        });
    });
    // Close Payment Modal when Escape key is pressed
    $(document).keyup(function(e) {
        if (e.key === "Escape") { 
            closePaymentModal();
            closeReceiptModal();
            closeValidationModal();
            closeConfirmModal();
        }
    });
    // Close Payment Modal when clicking on the backdrop overlay
    $('#paymentModal').click(function(e) {
        if (e.target === this) {
            closePaymentModal();
        }
    });
    // Close Receipt Modal when clicking on the backdrop overlay
    $('#receiptModal').click(function(e) {
        if (e.target === this) {
            closeReceiptModal();
        }
    });
    // Close Validation Modal when clicking on the backdrop overlay
    $('#validationModal').click(function(e) {
        if (e.target === this) {
            closeValidationModal();
        }
    });
    // Close Confirm Modal when clicking on the backdrop overlay
    $('#confirmModal').click(function(e) {
        if (e.target === this) {
            closeConfirmModal();
        }
    });
});

function renderFinance(f) {
    let box=document.getElementById('checkoutFinance');
    if(!box){const anchor=document.getElementById('cartSubtotal');if(!anchor)return;box=document.createElement('div');box.id='checkoutFinance';box.className='text-xs py-2';anchor.parentElement.insertAdjacentElement('afterend',box);}
    if(!f){box.textContent='';return;}
    box.textContent=(f.tax_cents?'Tax '+(f.tax_inclusive?'(included) ':'')+': PHP '+(f.tax_cents/100).toFixed(2)+'  ':'')+(f.service_cents?'Service charge: PHP '+(f.service_cents/100).toFixed(2):'');
}
