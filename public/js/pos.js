document.addEventListener('DOMContentLoaded', function() {
    let cart = [];
    const TAX_RATE = 0.10; // 10% Tax Rate

    // DOM Element References
    const productSearchInput = document.getElementById('product_search');
    const productListArea = document.getElementById('product-list-area');
    const cartItemsList = document.getElementById('cart-items-list');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const loadingProductsMessage = document.getElementById('loading-products-message'); // Initial loading message
    const noProductsMessage = document.getElementById('no-products-message'); // Message for no search results

    const cartSubtotalEl = document.getElementById('cart-subtotal');
    const cartDiscountInputEl = document.getElementById('cart-discount-input');
    const cartDiscountValueDisplayEl = document.getElementById('cart-discount-value-display');
    const cartTaxEl = document.getElementById('cart-tax');
    const cartTotalEl = document.getElementById('cart-total');

    const clearCartBtn = document.getElementById('clear-cart-btn');
    const submitOrderBtn = document.getElementById('submit-order-btn');

    const customerIdSelect = document.getElementById('customer_id');
    const orderNotesTextarea = document.getElementById('order_notes');

    const posForm = document.getElementById('pos-form');
    const posCustomerIdInput = document.getElementById('pos_customer_id');
    const posItemsJsonInput = document.getElementById('pos_items_json');
    const posTotalAmountInput = document.getElementById('pos_total_amount');
    const posDiscountAmountInput = document.getElementById('pos_discount_amount');
    const posTaxAmountInput = document.getElementById('pos_tax_amount');
    const posFinalNotesInput = document.getElementById('pos_final_notes');

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
        if (productListArea) productListArea.innerHTML = ''; // Clear previous results before loading

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
            if (loadingProductsMessage) loadingProductsMessage.classList.add('hidden');
        }
    }

    const debouncedProductSearch = debounce(searchProducts, 400);

    if (productSearchInput) {
        productSearchInput.addEventListener('input', function(e) {
            const keyword = e.target.value.trim();
            if (keyword.length >= 1 || keyword.length === 0) { // Search on 1 char or if field is cleared
                debouncedProductSearch(keyword);
            } else {
                 if (productListArea) productListArea.innerHTML = '<p class="text-gray-500 text-center">Type 1 or more characters to search.</p>';
                 if (noProductsMessage) noProductsMessage.classList.add('hidden');
            }
        });
    }

    function renderProductList(products) {
        if (!productListArea) return;
        productListArea.innerHTML = ''; // Clear previous items or "loading" message

        if (!products || products.length === 0) {
            if (noProductsMessage) {
                noProductsMessage.classList.remove('hidden');
                productListArea.appendChild(noProductsMessage);
            } else {
                 productListArea.innerHTML = '<p class="text-gray-500 text-center">No products found.</p>';
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
                // Use baseUrl for assets like images
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

    // --- Cart Logic (mostly same as before) ---
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
            if(cartDiscountInputEl) cartDiscountInputEl.value = 0;
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

        let discountValue = parseFloat(cartDiscountInputEl ? cartDiscountInputEl.value : 0) || 0;
        if (discountValue < 0) {
            discountValue = 0;
            if(cartDiscountInputEl) cartDiscountInputEl.value = 0;
        }
        if (discountValue > subtotal) {
            discountValue = subtotal;
            if(cartDiscountInputEl) cartDiscountInputEl.value = subtotal;
        }

        const amountAfterDiscount = subtotal - discountValue;
        const taxAmount = amountAfterDiscount * TAX_RATE;
        const grandTotal = amountAfterDiscount + taxAmount;

        if (cartSubtotalEl) cartSubtotalEl.textContent = formatCurrency(subtotal);
        if (cartDiscountValueDisplayEl) cartDiscountValueDisplayEl.textContent = formatCurrency(discountValue);
        if (cartTaxEl) cartTaxEl.textContent = formatCurrency(taxAmount);
        if (cartTotalEl) cartTotalEl.textContent = formatCurrency(grandTotal);

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

        if(posFinalNotesInput && orderNotesTextarea) posFinalNotesInput.value = orderNotesTextarea.value;
    }

    // Initial product load (e.g. empty search to show some initial products)
    // or based on pre-rendered items if any
    const initialProductItems = productListArea ? productListArea.querySelectorAll('.product-item') : [];
    if (loadingProductsMessage && initialProductItems.length === 0) {
        // If no products are pre-rendered by PHP, show loading then fetch, or prompt to search
        // For now, we assume any pre-rendered products are the initial state.
        // If AJAX is the *only* way products appear, then an initial call to searchProducts('') might be good.
        // searchProducts(''); // Example: Load all (or paginated initial set)
        loadingProductsMessage.classList.add('hidden'); // Hide if relying on search
        if (productListArea && productListArea.children.length === 1 && productListArea.firstChild.id === 'loading-products-message') {
             productListArea.innerHTML = '<p class="text-gray-500 text-center py-10">Search for products to begin.</p>';
        }
    } else if (loadingProductsMessage) {
        loadingProductsMessage.classList.add('hidden');
    }

    renderCart(); // Initial call to set up cart display and button states
});
