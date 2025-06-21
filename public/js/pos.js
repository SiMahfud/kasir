document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    const TAX_RATE = 0.10; // 10% Tax Rate

    // DOM Element References
    const productSearchInput = document.getElementById('product_search');
    const productListArea = document.getElementById('product-list-area');
    const cartItemsList = document.getElementById('cart-items-list');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const loadingProductsMessage = document.getElementById('loading-products-message');
    const noProductsMessage = document.getElementById('no-products-message');

    const cartSubtotalEl = document.getElementById('cart-subtotal');
    // New Discount elements
    const discountTypeSelect = document.getElementById('discount_type');
    const discountValueInput = document.getElementById('discount_value_input'); // Replaces cartDiscountInputEl
    const cartCalculatedDiscountDisplayEl = document.getElementById('cart_calculated_discount_display'); // New display for calculated discount

    const cartTaxEl = document.getElementById('cart-tax');
    const cartTotalEl = document.getElementById('cart-total');

    const clearCartBtn = document.getElementById('clear-cart-btn');
    const submitOrderBtn = document.getElementById('submit-order-btn');

    const customerIdSelect = document.getElementById('customer_id');
    const orderNotesTextarea = document.getElementById('order_notes');

    const posForm = document.getElementById('pos-form');
    const posCustomerIdInput = document.getElementById('pos_customer_id');
    const posItemsJsonInput = document.getElementById('pos_items_json');
    const posSubtotalBeforeDiscountInput = document.getElementById('pos_subtotal_before_discount'); // New
    const posCalculatedDiscountAmountInput = document.getElementById('pos_calculated_discount_amount'); // Replaces posDiscountAmountInput
    const posTaxAmountInput = document.getElementById('pos_tax_amount');
    const posTotalAmountInput = document.getElementById('pos_total_amount'); // Final total
    // pos_discount_type and pos_discount_value_input are submitted directly from their visible form elements.
    // pos_final_notes is submitted directly by the textarea name attribute.

    // --- Helper Functions ---
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function (match) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[match];
        });
    }

    function formatCurrency(amount) {
        return parseFloat(amount).toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // --- Product Search & Rendering ---
    async function searchProducts(keyword) {
        if (loadingProductsMessage) loadingProductsMessage.classList.remove('hidden');
        if (noProductsMessage) noProductsMessage.classList.add('hidden');
        if (productListArea) productListArea.innerHTML = '<p class="text-gray-500 text-center py-10">Loading products...</p>';

        try {
            const response = await fetch(siteUrl + 'pesanan/ajax_product_search?term=' + encodeURIComponent(keyword));
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const products = await response.json();
            renderProductList(products);
        } catch (error) {
            console.error('Error fetching products:', error);
            if (productListArea) productListArea.innerHTML = '<p class="text-red-500 text-center">Error loading products.</p>';
        } finally {
            // Removed hiding loading message from here, renderProductList will clear it.
        }
    }

    const debouncedProductSearch = debounce(searchProducts, 400);

    if (productSearchInput) {
        productSearchInput.addEventListener('input', function(e) {
            const keyword = e.target.value.trim();
            if (keyword.length >= 1 || keyword.length === 0) {
                debouncedProductSearch(keyword);
            } else {
                 if (productListArea) productListArea.innerHTML = '<p class="text-gray-500 text-center py-10">Type 1 or more characters to search.</p>';
                 if (noProductsMessage) noProductsMessage.classList.add('hidden');
            }
        });
    }

    function renderProductList(products) {
        if (!productListArea) return;
        productListArea.innerHTML = '';

        if (!products || products.length === 0) {
            if (noProductsMessage) {
                noProductsMessage.classList.remove('hidden'); // Show the "no products found" message
                productListArea.appendChild(noProductsMessage);
            } else { // Fallback if the specific message element isn't there
                 productListArea.innerHTML = '<p class="text-gray-500 text-center py-10">No products found matching your search.</p>';
            }
            return;
        }
        if (noProductsMessage) noProductsMessage.classList.add('hidden');


        products.forEach(product => {
            const productDiv = document.createElement('div');
            productDiv.classList.add('product-item', 'p-3', 'border', 'rounded-md', 'hover:shadow-lg', 'cursor-pointer', 'flex', 'justify-between', 'items-center', 'mb-2', 'bg-white');
            productDiv.dataset.productId = product.id;
            productDiv.dataset.name = product.name;
            productDiv.dataset.price = product.price;
            productDiv.dataset.stock = product.stock;

            let imageHTML = '';
            if (product.image_path) {
                imageHTML = `<img src="${baseUrl}uploads/products/${escapeHTML(product.image_path)}" alt="${escapeHTML(product.name)}" class="h-10 w-10 object-cover rounded mt-1 mr-3 flex-shrink-0">`;
            }

            productDiv.innerHTML = `
                <div class="flex items-center flex-grow">
                    ${imageHTML}
                    <div class="flex-grow">
                        <h4 class="font-semibold text-sm text-gray-800">${escapeHTML(product.name)} ${product.sku ? `(${escapeHTML(product.sku)})` : ''}</h4>
                        <p class="text-xs text-gray-600">${formatCurrency(parseFloat(product.price))} - Stock: ${product.stock}</p>
                    </div>
                </div>
                <button type="button" class="add-to-cart-btn bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 text-xs transition duration-150 ml-2 flex-shrink-0">
                    <i class="fas fa-cart-plus mr-1"></i> Add
                </button>
            `;
            productListArea.appendChild(productDiv);
        });
    }

    function addToCart(product) {
        if (product.stock <= 0) {
            alert(`'${escapeHTML(product.name)}' is out of stock.`);
            return;
        }
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            if (existingItem.quantity < product.stock) {
                existingItem.quantity++;
            } else {
                alert(`Maximum stock reached for '${escapeHTML(product.name)}'.`);
            }
        } else {
            cart.push({ ...product, quantity: 1 });
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
                cartItem.quantity = cartItem.stock;
                alert(`Quantity for '${escapeHTML(cartItem.name)}' cannot exceed available stock (${cartItem.stock}).`);
            } else {
                removeFromCart(productId);
            }
        }
        renderCart();
    }

    function clearCart() {
        if (confirm('Are you sure you want to clear all items from the cart?')) {
            cart = [];
            if(discountValueInput) discountValueInput.value = 0;
            if(discountTypeSelect) discountTypeSelect.value = 'fixed_amount'; // Reset to default
            renderCart();
        }
    }

    function renderCart() {
        if (!cartItemsList) return;
        cartItemsList.innerHTML = '';

        if (cart.length === 0) {
            if (emptyCartMessage) {
                emptyCartMessage.classList.remove('hidden');
                cartItemsList.appendChild(emptyCartMessage);
            }
        } else {
            if (emptyCartMessage) emptyCartMessage.classList.add('hidden');
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                const cartItemDiv = document.createElement('div');
                cartItemDiv.classList.add('cart-item', 'flex', 'justify-between', 'items-center', 'border-b', 'pb-2', 'pt-1');
                cartItemDiv.dataset.cartItemId = item.id;
                cartItemDiv.innerHTML = `
                    <div class="flex-grow pr-2">
                        <p class="font-medium text-sm truncate" title="${escapeHTML(item.name)}">${escapeHTML(item.name)}</p>
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

        const discountType = discountTypeSelect ? discountTypeSelect.value : 'fixed_amount';
        let discountInputValue = parseFloat(discountValueInput ? discountValueInput.value : 0) || 0;
        let actualDiscountAmount = 0;

        if (discountInputValue < 0) {
            discountInputValue = 0;
            if(discountValueInput) discountValueInput.value = 0;
        }

        if (discountType === 'percentage') {
            if (discountInputValue > 100) { // Percentage cannot exceed 100
                discountInputValue = 100;
                if(discountValueInput) discountValueInput.value = 100;
            }
            actualDiscountAmount = subtotal * (discountInputValue / 100);
        } else { // fixed_amount
            actualDiscountAmount = discountInputValue;
        }

        if (actualDiscountAmount > subtotal) { // Cap discount
            actualDiscountAmount = subtotal;
            // Optionally, if fixed_amount type, you might want to update discountValueInput to match actualDiscountAmount
            // if (discountType === 'fixed_amount' && discountValueInput && discountInputValue > actualDiscountAmount) {
            //    discountValueInput.value = actualDiscountAmount.toFixed(2);
            // }
        }

        const amountAfterDiscount = subtotal - actualDiscountAmount;
        const taxAmount = amountAfterDiscount * TAX_RATE; // Tax applied on discounted amount
        const grandTotal = amountAfterDiscount + taxAmount;

        if (cartSubtotalEl) cartSubtotalEl.textContent = formatCurrency(subtotal);
        if (cartCalculatedDiscountDisplayEl) cartCalculatedDiscountDisplayEl.textContent = `- ${formatCurrency(actualDiscountAmount)}`;
        if (cartTaxEl) cartTaxEl.textContent = formatCurrency(taxAmount);
        if (cartTotalEl) cartTotalEl.textContent = formatCurrency(grandTotal);

        // Update hidden fields for form submission
        if(posSubtotalBeforeDiscountInput) posSubtotalBeforeDiscountInput.value = subtotal.toFixed(2);
        if(posCalculatedDiscountAmountInput) posCalculatedDiscountAmountInput.value = actualDiscountAmount.toFixed(2);
        if(posTaxAmountInput) posTaxAmountInput.value = taxAmount.toFixed(2);
        if(posTotalAmountInput) posTotalAmountInput.value = grandTotal.toFixed(2);
        // Note: discount_type and discount_value_input are submitted directly by their name attributes from visible form elements.
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
        if (cart.length === 0) {
            alert('Cannot submit an empty order. Please add products to the cart.');
            event.preventDefault();
            return;
        }

        if(posCustomerIdInput && customerIdSelect) posCustomerIdInput.value = customerIdSelect.value;

        const itemsToSubmit = cart.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            price_per_unit: item.price,
        }));
        if(posItemsJsonInput) posItemsJsonInput.value = JSON.stringify(itemsToSubmit);

        // pos_final_notes is submitted directly by textarea name attribute ('pos_final_notes')
        // The other hidden fields (pos_subtotal_before_discount, pos_calculated_discount_amount, etc.) are populated in calculateTotals()
    }

    // Event Listeners
    if (productListArea) { // Add to cart
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

    if (cartItemsList) { // Remove item or Change quantity
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

    if (clearCartBtn) clearCartBtn.addEventListener('click', clearCart);

    // Updated Discount Input & Type Change Listeners
    if (discountValueInput) {
        discountValueInput.addEventListener('input', calculateTotals);
    }
    if (discountTypeSelect) {
        discountTypeSelect.addEventListener('change', calculateTotals);
    }

    if (posForm) posForm.addEventListener('submit', prepareOrderSubmission);

    // Initial product list message
    if (productListArea && productListArea.children.length === 0) {
        if (loadingProductsMessage && loadingProductsMessage.parentNode === productListArea) {
            // if loading message is still there, means no initial search was run
        } else {
             productListArea.innerHTML = '<p class="text-gray-500 text-center py-10">Search for products to begin.</p>';
        }
    }
    if(loadingProductsMessage && productListArea && productListArea.children.length > 0 && productListArea.firstChild.id !== 'loading-products-message'){
        // If products were pre-rendered or loaded by another means, hide loading
        loadingProductsMessage.classList.add('hidden');
    }


    renderCart(); // Initial call
});
