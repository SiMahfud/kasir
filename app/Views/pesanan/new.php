<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
New Order - POS
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add any POS-specific CSS links here -->
    <style>
        /* Custom scrollbar for product/cart lists if needed */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <form id="pos-form" action="<?= site_url('pesanan/submit_order') ?>" method="post">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Product Selection & Customer -->
            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white shadow-xl rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-user-tag mr-3 text-gray-500"></i>Customer (Optional)
                    </h3>
                    <select name="customer_id" id="customer_id" class="block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="">Select Existing Customer or Walk-in</option>
                        <?php if (!empty($customers) && is_array($customers)): ?>
                            <?php foreach ($customers as $customer): ?>
                                <option value="<?= esc($customer['id']) ?>"><?= esc($customer['name']) ?> - <?= esc($customer['phone'] ?? $customer['email'] ?? 'No Contact') ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No customers available</option>
                        <?php endif; ?>
                    </select>
                    <!-- <button type="button" id="add-new-customer-btn" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">Add New Customer</button> -->
                </div>

                <div class="bg-white shadow-xl rounded-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-search mr-3 text-gray-500"></i>Product Search
                    </h3>
                    <input type="text" id="product_search" placeholder="Search by name or SKU..." class="block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm mb-2">
                    <!-- Category filter dropdown could go here -->
                </div>

                <div id="product-list-area" class="bg-white shadow-xl rounded-lg p-4 h-96 overflow-y-auto custom-scrollbar">
                    <!-- Example Product Item Structure (to be repeated by JS) -->
                    <div class="product-item p-4 border rounded-lg hover:shadow-lg cursor-pointer flex justify-between items-center mb-3 bg-gray-50"
                         data-product-id="1" data-name="Sample Product A" data-price="15000" data-stock="20">
                        <div>
                            <h4 class="font-semibold text-gray-800">Sample Product A</h4>
                            <p class="text-sm text-gray-600">Rp 15,000 - Stock: 20</p>
                        </div>
                        <button type="button" class="add-to-cart-btn bg-blue-500 text-white px-3 py-2 rounded-md hover:bg-blue-600 transition duration-150">
                            <i class="fas fa-cart-plus"></i> Add
                        </button>
                    </div>
                     <div class="product-item p-4 border rounded-lg hover:shadow-lg cursor-pointer flex justify-between items-center mb-3 bg-gray-50"
                         data-product-id="2" data-name="Sample Product B" data-price="25000" data-stock="10">
                        <div>
                            <h4 class="font-semibold text-gray-800">Sample Product B</h4>
                            <p class="text-sm text-gray-600">Rp 25,000 - Stock: 10</p>
                        </div>
                        <button type="button" class="add-to-cart-btn bg-blue-500 text-white px-3 py-2 rounded-md hover:bg-blue-600 transition duration-150">
                            <i class="fas fa-cart-plus"></i> Add
                        </button>
                    </div>
                    <!-- End Example -->
                    <p id="no-products-message" class="text-gray-500 text-center py-10 hidden">No products match your search or filter.</p>
                    <p id="loading-products-message" class="text-gray-500 text-center py-10">Loading products...</p>
                </div>
            </div>

            <!-- Right Column: Cart & Order Summary -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white shadow-xl rounded-lg p-6 sticky top-6">
                    <h3 class="text-xl font-semibold text-gray-700 mb-4 flex justify-between items-center">
                        <span class="flex items-center"><i class="fas fa-shopping-cart mr-3 text-gray-500"></i>Current Order</span>
                        <button type="button" id="clear-cart-btn" class="text-xs text-red-500 hover:text-red-700 font-medium disabled:opacity-50" title="Clear Cart" disabled>
                            <i class="fas fa-trash-alt"></i> Clear All
                        </button>
                    </h3>
                    <div id="cart-items-list" class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar mb-4 custom-scrollbar pr-1">
                        <!-- Example Cart Item (to be repeated by JS) -->
                        <!--
                        <div class="cart-item flex justify-between items-center border-b pb-2 pt-1" data-cart-item-id="1">
                            <div>
                                <p class="font-medium text-sm">Sample Product A</p>
                                <p class="text-xs text-gray-500">
                                    Rp 15,000 x
                                    <input type="number" value="1" class="quantity-input w-12 text-center border rounded text-xs py-0.5 mx-1" min="1" data-item-id="1">
                                    = Rp 15,000
                                </p>
                            </div>
                            <button type="button" class="remove-from-cart-btn text-red-400 hover:text-red-600 ml-2" title="Remove Item">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                        -->
                        <!-- End Example -->
                        <p id="empty-cart-message" class="text-gray-500 text-center py-10">Cart is empty. Add products from the left.</p>
                    </div>

                    <div class="border-t pt-4 space-y-2 text-sm text-gray-700">
                        <div class="flex justify-between"><span>Subtotal:</span> <span id="cart-subtotal" class="font-medium">Rp 0</span></div>

                        <div class="flex justify-between items-center">
                            <span>Discount:</span>
                            <div class="flex items-center space-x-2">
                                <select id="discount_type" name="pos_discount_type" class="text-sm border rounded-md px-2 py-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="fixed_amount" selected>Rp</option>
                                    <option value="percentage">%</option>
                                </select>
                                <input type="number" id="discount_value_input" name="pos_discount_value_input" class="w-24 text-right border rounded-md text-sm py-1 px-2 shadow-sm" value="0" min="0">
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <span>Calculated Discount:</span>
                            <span id="cart_calculated_discount_display" class="font-medium text-red-600">- Rp 0</span>
                        </div>

                        <div class="flex justify-between"><span>Tax (10%):</span> <span id="cart-tax" class="font-medium">Rp 0</span></div>
                        <hr class="my-2">
                        <div class="flex justify-between font-bold text-lg text-gray-800"><span>Total:</span> <span id="cart-total">Rp 0</span></div>
                    </div>

                    <div class="mt-6">
                        <label for="order_notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes (Optional):</label>
                        <textarea name="pos_final_notes" id="order_notes" rows="2" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>

                    <!-- Hidden fields for form submission -->
                    <input type="hidden" name="pos_customer_id" id="pos_customer_id">
                    <input type="hidden" name="pos_items_json" id="pos_items_json">
                    <input type="hidden" name="pos_subtotal_before_discount" id="pos_subtotal_before_discount"> <!-- New -->
                    <input type="hidden" name="pos_calculated_discount_amount" id="pos_calculated_discount_amount"> <!-- Renamed/repurposed from pos_discount_amount -->
                    <input type="hidden" name="pos_tax_amount" id="pos_tax_amount">
                    <input type="hidden" name="pos_total_amount" id="pos_total_amount"> <!-- Final total -->
                    <!-- pos_discount_type and pos_discount_value_input are submitted directly from their visible fields -->

                    <button type="submit" id="submit-order-btn" class="w-full mt-6 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow-md text-lg transition duration-150 ease-in-out flex items-center justify-center disabled:opacity-50" disabled>
                        <i class="fas fa-check-circle mr-2"></i>Finalize & Submit Order
                    </button>
                </div>
            </div>
        </div> <!-- End main grid -->
    </form> <!-- End POS form -->
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url('js/pos.js') ?>"></script>
    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        // This has been moved to pos.js
        // });
    </script>
<?= $this->endSection() ?>
            // Placeholder for POS JavaScript logic
            // This will handle:
            // - Fetching/displaying products (e.g., via AJAX on search/filter)
            // - Adding products to the cart
            // - Updating quantities in the cart
            // - Removing items from the cart
            // - Calculating subtotal, tax, discount, total
            // - Populating hidden form fields before submission
            // - Handling customer selection
            // - Clearing the cart
            // - Enabling/disabling buttons based on cart state

            const productListArea = document.getElementById('product-list-area');
            const cartItemsList = document.getElementById('cart-items-list');
            const emptyCartMessage = document.getElementById('empty-cart-message');
            const loadingProductsMessage = document.getElementById('loading-products-message');
            const noProductsMessage = document.getElementById('no-products-message');

            const cartSubtotalEl = document.getElementById('cart-subtotal');
            const cartDiscountInputEl = document.getElementById('cart-discount-input');
            // const cartDiscountDisplayEl = document.getElementById('cart-discount-display');
            const cartTaxEl = document.getElementById('cart-tax');
            const cartTotalEl = document.getElementById('cart-total');

            const clearCartBtn = document.getElementById('clear-cart-btn');
            const submitOrderBtn = document.getElementById('submit-order-btn');

            let cart = []; // Array to hold cart items: { id, name, price, quantity, stock }

            // --- Product Loading/Display (Example) ---
            // Simulating product loading
            setTimeout(() => {
                loadingProductsMessage.classList.add('hidden');
                // If no products were actually loaded by a real fetch:
                // noProductsMessage.classList.remove('hidden');
                // For now, we have static examples, so they'll just show.
            }, 1000);

            // --- Cart Logic ---
            function renderCart() {
                cartItemsList.innerHTML = ''; // Clear previous items
                if (cart.length === 0) {
                    emptyCartMessage.classList.remove('hidden');
                    cartItemsList.appendChild(emptyCartMessage);
                    clearCartBtn.disabled = true;
                    submitOrderBtn.disabled = true;
                } else {
                    emptyCartMessage.classList.add('hidden');
                    clearCartBtn.disabled = false;
                    submitOrderBtn.disabled = false;
                    cart.forEach(item => {
                        const itemTotal = item.price * item.quantity;
                        const cartItemDiv = document.createElement('div');
                        cartItemDiv.classList.add('cart-item', 'flex', 'justify-between', 'items-center', 'border-b', 'pb-2', 'pt-1');
                        cartItemDiv.dataset.cartItemId = item.id;
                        cartItemDiv.innerHTML = `
                            <div>
                                <p class="font-medium text-sm">${item.name}</p>
                                <p class="text-xs text-gray-500">
                                    Rp ${formatCurrency(item.price)} x
                                    <input type="number" value="${item.quantity}" class="quantity-input w-12 text-center border rounded text-xs py-0.5 mx-1" min="1" max="${item.stock}" data-item-id="${item.id}">
                                    = Rp ${formatCurrency(itemTotal)}
                                </p>
                            </div>
                            <button type="button" class="remove-from-cart-btn text-red-400 hover:text-red-600 ml-2" title="Remove Item" data-item-id="${item.id}">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        `;
                        cartItemsList.appendChild(cartItemDiv);
                    });
                }
                calculateTotals();
                updateHiddenFormFields();
            }

            function calculateTotals() {
                let subtotal = 0;
                cart.forEach(item => subtotal += item.price * item.quantity);

                let discount = parseFloat(cartDiscountInputEl.value) || 0;
                if (discount > subtotal) discount = subtotal; // Discount cannot exceed subtotal
                cartDiscountInputEl.value = discount; // Update input if corrected

                // Example: 10% tax on (subtotal - discount)
                const taxableAmount = subtotal - discount;
                const taxRate = 0.10;
                const tax = taxableAmount * taxRate;

                const total = taxableAmount + tax;

                cartSubtotalEl.textContent = `Rp ${formatCurrency(subtotal)}`;
                // cartDiscountDisplayEl.textContent = `Rp ${formatCurrency(discount)}`;
                cartTaxEl.textContent = `Rp ${formatCurrency(tax)}`;
                cartTotalEl.textContent = `Rp ${formatCurrency(total)}`;

                // Store calculated amounts for form submission
                document.getElementById('pos_discount_amount').value = discount;
                document.getElementById('pos_tax_amount').value = tax;
                document.getElementById('pos_total_amount').value = total;
            }

            function formatCurrency(amount) {
                return parseFloat(amount).toLocaleString('id-ID');
            }

            // Event Listeners
            productListArea.addEventListener('click', function(e) {
                const productDiv = e.target.closest('.product-item');
                if (!productDiv) return;

                const targetButton = e.target.closest('.add-to-cart-btn');
                if (!targetButton) return; // Clicked elsewhere on product item

                const productId = productDiv.dataset.productId;
                const name = productDiv.dataset.name;
                const price = parseFloat(productDiv.dataset.price);
                const stock = parseInt(productDiv.dataset.stock);

                const existingItem = cart.find(item => item.id === productId);
                if (existingItem) {
                    if (existingItem.quantity < stock) {
                        existingItem.quantity++;
                    } else {
                        alert('Maximum stock reached for this item.');
                    }
                } else {
                    if (stock > 0) {
                        cart.push({ id: productId, name, price, quantity: 1, stock });
                    } else {
                        alert('This item is out of stock.');
                    }
                }
                renderCart();
            });

            cartItemsList.addEventListener('change', function(e) {
                if (e.target.classList.contains('quantity-input')) {
                    const itemId = e.target.dataset.itemId;
                    const newQuantity = parseInt(e.target.value);
                    const cartItem = cart.find(item => item.id === itemId);
                    if (cartItem) {
                        if (newQuantity > 0 && newQuantity <= cartItem.stock) {
                            cartItem.quantity = newQuantity;
                        } else if (newQuantity > cartItem.stock) {
                            e.target.value = cartItem.stock; // Reset to max stock
                            cartItem.quantity = cartItem.stock;
                            alert('Cannot exceed available stock.');
                        } else {
                             e.target.value = cartItem.quantity; // Reset to previous valid quantity
                        }
                    }
                    renderCart();
                }
            });

            cartItemsList.addEventListener('click', function(e) {
                const removeButton = e.target.closest('.remove-from-cart-btn');
                if (removeButton) {
                    const itemId = removeButton.dataset.itemId;
                    cart = cart.filter(item => item.id !== itemId);
                    renderCart();
                }
            });

            clearCartBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear the entire cart?')) {
                    cart = [];
                    renderCart();
                }
            });

            cartDiscountInputEl.addEventListener('change', calculateTotals);
            cartDiscountInputEl.addEventListener('keyup', calculateTotals);


            function updateHiddenFormFields() {
                document.getElementById('pos_customer_id').value = document.getElementById('customer_id').value;
                document.getElementById('pos_items_json').value = JSON.stringify(cart.map(item => ({product_id: item.id, quantity: item.quantity, price_per_unit: item.price, total_price: item.price * item.quantity })));
                // pos_total_amount, pos_discount_amount, pos_tax_amount are updated in calculateTotals
                document.getElementById('pos_final_notes').value = document.getElementById('order_notes').value;
            }

            // Update hidden fields before submitting form
            const posForm = document.getElementById('pos-form');
            posForm.addEventListener('submit', function(e) {
                updateHiddenFormFields();
                if (cart.length === 0) {
                    e.preventDefault();
                    alert('Cannot submit an empty order. Please add products to the cart.');
                    return false;
                }
                // Further validation can be added here if needed
            });

            // Initial render
            renderCart();
        });
    </script>
<?= $this->endSection() ?>
