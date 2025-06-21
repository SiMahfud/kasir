document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    const TAX_RATE = 0.10; // 10% TAax Rate

    // DOM Element References
    const productListArea = document.getElementById('product-list-area');
    const cartItemsList = document.getElementById('cart-items-list');
    const emptyCartMessage = document.getElementById('empty-cart-message');

    const cartSubtotalEl = document.getElementById('cart-subtotal');
    const cartDiscountInputEl = document.getElementById('cart-discount-input');
    const cartDiscountValueDisplayEl = document.getElementById('cart-discount-value-display'); // For formatted discount
    const cartTaxEl = document.getElementById('cart-tax');
    const cartTotalEl = document.getElementById('cart-total');

    const clearCartBtn = document.getElementById('clear-cart-btn');
    const submitOrderBtn = document.getElementById('submit-order-btn');

    const customerIdSelect = document.getElementById('customer_id');
    const orderNotesTextarea = document.getElementById('order_notes');

    // Hidden form inputs
    const posForm = document.getElementById('pos-form');
    const posCustomerIdInput = document.getElementById('pos_customer_id');
    const posItemsJsonInput = document.getElementById('pos_items_json');
    const posTotalAmountInput = document.getElementById('pos_total_amount');
    const posDiscountAmountInput = document.getElementById('pos_discount_amount');
    const posTaxAmountInput = document.getElementById('pos_tax_amount');
    const posFinalNotesInput = document.getElementById('pos_final_notes');

    // Initial setup
    renderCart(); // Also calls calculateTotals and updateButtonStates

    // --- Event Listeners ---

    // Product List Area: Add to Cart
    if (productListArea) {
        productListArea.addEventListener('click', function(e) {
            const addButton = e.target.closest('.add-to-cart-btn');
            if (addButton) {
                const productItemEl = addButton.closest('.product-item');
                if (productItemEl) {
                    const product = {
                        id: productItemEl.dataset.productId,
                        name: productItemEl.dataset.name,
                        price: parseFloat(productItemEl.dataset.price),
                        stock: parseInt(productItemEl.dataset.stock)
                    };
                    addToCart(product);
                }
            }
        });
    }

    // Cart Items List: Remove item or Change quantity
    if (cartItemsList) {
        cartItemsList.addEventListener('click', function(e) {
            const removeButton = e.target.closest('.remove-from-cart-btn');
            if (removeButton) {
                const productId = removeButton.dataset.itemId;
                removeFromCart(productId);
            }
        });

        cartItemsList.addEventListener('change', function(e) {
            if (e.target.classList.contains('quantity-input')) {
                const productId = e.target.dataset.itemId;
                const newQuantity = parseInt(e.target.value);
                updateQuantity(productId, newQuantity);
            }
        });
    }

    // Clear Cart Button
    if (clearCartBtn) {
        clearCartBtn.addEventListener('click', clearCart);
    }

    // Discount Input
    if (cartDiscountInputEl) {
        cartDiscountInputEl.addEventListener('input', calculateTotals);
    }

    // POS Form Submit
    if (posForm) {
        posForm.addEventListener('submit', prepareOrderSubmission);
    }

    // --- Functions ---

    function addToCart(product) {
        if (product.stock <= 0) {
            alert(`'${product.name}' is out of stock.`);
            return;
        }

        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            if (existingItem.quantity < product.stock) {
                existingItem.quantity++;
            } else {
                alert(`Maximum stock reached for '${product.name}'.`);
            }
        } else {
            cart.push({ ...product, quantity: 1 }); // Spread to copy product data
        }
        renderCart();
    }

    function removeFromCart(productId) {
        cart = cart.filter(item => item.id !== productId);
        renderCart();
    }

    function updateQuantity(productId, quantity) {
        const cartItem = cart.find(item => item.id === productId);
        if (cartItem) {
            if (quantity > 0 && quantity <= cartItem.stock) {
                cartItem.quantity = quantity;
            } else if (quantity > cartItem.stock) {
                cartItem.quantity = cartItem.stock; // Cap at stock
                alert(`Quantity for '${cartItem.name}' cannot exceed available stock (${cartItem.stock}).`);
            } else { // quantity is 0 or less, remove item
                removeFromCart(productId);
                // Or reset to 1: cartItem.quantity = 1; alert("Quantity must be at least 1.");
            }
        }
        renderCart();
    }

    function clearCart() {
        if (confirm('Are you sure you want to clear all items from the cart?')) {
            cart = [];
            cartDiscountInputEl.value = 0; // Reset discount
            renderCart();
        }
    }

    function renderCart() {
        if (!cartItemsList) return;
        cartItemsList.innerHTML = ''; // Clear previous items

        if (cart.length === 0) {
            if (emptyCartMessage) {
                emptyCartMessage.classList.remove('hidden');
                cartItemsList.appendChild(emptyCartMessage);
            }
        } else {
            if (emptyCartMessage) {
                emptyCartMessage.classList.add('hidden');
            }
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                const cartItemDiv = document.createElement('div');
                cartItemDiv.classList.add('cart-item', 'flex', 'justify-between', 'items-center', 'border-b', 'pb-2', 'pt-1');
                cartItemDiv.dataset.cartItemId = item.id;
                cartItemDiv.innerHTML = `
                    <div class="flex-grow pr-2">
                        <p class="font-medium text-sm truncate" title="${item.name}">${item.name}</p>
                        <p class="text-xs text-gray-500">
                            ${formatCurrency(item.price)} x
                            <input type="number" value="${item.quantity}" class="quantity-input w-12 text-center border rounded text-xs py-0.5 mx-1" min="1" max="${item.stock}" data-item-id="${item.id}">
                            = ${formatCurrency(itemTotal)}
                        </p>
                    </div>
                    <button type="button" class="remove-from-cart-btn text-red-400 hover:text-red-600 ml-2 flex-shrink-0" title="Remove Item" data-item-id="${item.id}">
                        <i class="fas fa-times-circle"></i>
                    </button>
                `;
                cartItemsList.appendChild(cartItemDiv);
            });
        }
        calculateTotals();
        updateButtonStates();
    }

    function calculateTotals() {
        let subtotal = 0;
        cart.forEach(item => subtotal += item.price * item.quantity);

        let discountValue = parseFloat(cartDiscountInputEl.value) || 0;
        if (discountValue < 0) {
            discountValue = 0;
            cartDiscountInputEl.value = 0;
        }
        if (discountValue > subtotal) {
            discountValue = subtotal; // Cap discount at subtotal
            cartDiscountInputEl.value = subtotal;
        }

        const amountAfterDiscount = subtotal - discountValue;
        const taxAmount = amountAfterDiscount * TAX_RATE;
        const grandTotal = amountAfterDiscount + taxAmount;

        if (cartSubtotalEl) cartSubtotalEl.textContent = formatCurrency(subtotal);
        if (cartDiscountValueDisplayEl) cartDiscountValueDisplayEl.textContent = formatCurrency(discountValue);
        if (cartTaxEl) cartTaxEl.textContent = formatCurrency(taxAmount);
        if (cartTotalEl) cartTotalEl.textContent = formatCurrency(grandTotal);

        // For form submission
        if(posDiscountAmountInput) posDiscountAmountInput.value = discountValue.toFixed(2);
        if(posTaxAmountInput) posTaxAmountInput.value = taxAmount.toFixed(2);
        if(posTotalAmountInput) posTotalAmountInput.value = grandTotal.toFixed(2);
    }

    function updateButtonStates() {
        if (cart.length === 0) {
            if (clearCartBtn) clearCartBtn.disabled = true;
            if (submitOrderBtn) submitOrderBtn.disabled = true;
        } else {
            if (clearCartBtn) clearCartBtn.disabled = false;
            if (submitOrderBtn) submitOrderBtn.disabled = false;
        }
    }

    function prepareOrderSubmission(event) {
        // If client-side validation fails, prevent form submission
        if (cart.length === 0) {
            alert('Cannot submit an empty order. Please add products to the cart.');
            event.preventDefault(); // Stop form submission
            return;
        }

        if(posCustomerIdInput && customerIdSelect) posCustomerIdInput.value = customerIdSelect.value;

        const itemsToSubmit = cart.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            price_per_unit: item.price,
            // total_price: item.price * item.quantity // Server will recalculate this
        }));
        if(posItemsJsonInput) posItemsJsonInput.value = JSON.stringify(itemsToSubmit);

        if(posFinalNotesInput && orderNotesTextarea) posFinalNotesInput.value = orderNotesTextarea.value;

        // Total, discount, tax already updated in hidden fields by calculateTotals
        // No event.preventDefault() here means form will submit after this function.
    }

    function formatCurrency(amount) {
        return amount.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // Placeholder for product fetching/filtering logic (if not server-rendered initially)
    // function fetchProducts(searchTerm = '', categoryId = '') { ... }

    // Simulate hiding loading message for products (if products are hardcoded or loaded quickly)
    const loadingMsg = document.getElementById('loading-products-message');
    const noProductsMsg = document.getElementById('no-products-message');
    const productItems = productListArea ? productListArea.querySelectorAll('.product-item') : [];

    if (loadingMsg) loadingMsg.classList.add('hidden');
    if (noProductsMsg && productItems.length === 0) {
        // noProductsMsg.classList.remove('hidden'); // Only show if no static products either
    }
     if (noProductsMsg && productListArea && productListArea.children.length <= 3) { // check if only messages are children
        // This logic is a bit fragile. Better to have a flag or count actual product items.
        // For now, if only example items are present, it's fine. If JS populates, it should handle this.
    }


});
