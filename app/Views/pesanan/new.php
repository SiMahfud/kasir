<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
New Order (POS)
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-semibold text-gray-800 mb-8">Point of Sale</h2>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-md rounded-md" role="alert">
            <p class="font-bold">Error</p>
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('pesanan/submit_order') ?>" method="post" id="pos-form">
        <?= csrf_field() ?>

        <!-- Customer and Product Search -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Customer Selection -->
            <div>
                <label for="pos_customer_id" class="block text-gray-700 text-sm font-bold mb-2">Customer:</label>
                <select name="pos_customer_id" id="pos_customer_id" class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Walk-in Customer</option>
                    <?php if (!empty($customers)): ?>
                        <?php foreach($customers as $customer): ?>
                            <option value="<?= $customer['id'] ?>" <?= old('pos_customer_id') == $customer['id'] ? 'selected' : '' ?>><?= esc($customer['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <!-- Product Search -->
            <div>
                <label for="product_search_select" class="block text-gray-700 text-sm font-bold mb-2">Search Product (Name/SKU):</label>
                <select id="product_search_select" class="w-full"></select>
            </div>
        </div>

        <!-- Cart Items Table -->
        <div class="bg-white shadow-xl rounded-lg overflow-x-auto mb-6">
            <table class="min-w-full leading-normal">
                <thead class="bg-gray-200">
                    <tr class="text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-5 text-left">Product</th>
                        <th class="py-3 px-5 text-center">Price</th>
                        <th class="py-3 px-5 text-center">Quantity</th>
                        <th class="py-3 px-5 text-right">Total</th>
                        <th class="py-3 px-5 text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="cart-items" class="text-gray-700 text-sm">
                    <tr id="cart-empty-row">
                        <td colspan="5" class="text-center py-10 text-gray-500">Cart is empty. Search for a product to begin.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Order Summary & Payment -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <label for="pos_final_notes" class="block text-gray-700 text-sm font-bold mb-2">Notes:</label>
                <textarea name="pos_final_notes" id="pos_final_notes" rows="4" class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"><?= old('pos_final_notes') ?></textarea>
            </div>
            <div class="bg-gray-100 p-6 rounded-lg shadow-lg">
                <h4 class="text-lg font-semibold mb-4">Order Summary</h4>
                <div class="flex justify-between mb-2">
                    <span>Subtotal</span>
                    <span id="summary-subtotal">Rp 0</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Discount</span>
                    <span id="summary-discount">Rp 0</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span>Tax (10%)</span>
                    <span id="summary-tax">Rp 0</span>
                </div>
                <hr class="my-2 border-gray-300">
                <div class="flex justify-between font-bold text-xl">
                    <span>Total</span>
                    <span id="summary-total">Rp 0</span>
                </div>
                <input type="hidden" name="pos_items_json" id="pos_items_json" value="<?= esc(old('pos_items_json', '[]')) ?>">
                <input type="hidden" name="pos_discount_type" id="pos_discount_type" value="fixed_amount">
                <input type="hidden" name="pos_discount_value_input" id="pos_discount_value_input" value="0">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-4 rounded-lg mt-6 shadow-md hover:shadow-lg transition duration-150">
                    Process Payment
                </button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- jQuery and Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- STATE & CONFIG ---
        let cart = JSON.parse(document.getElementById('pos_items_json').value || '[]').map(item => ({
            ...item,
            price_per_unit: parseFloat(item.price_per_unit),
            quantity: parseInt(item.quantity, 10)
        }));
        const TAX_RATE = 0.10; // 10%

        // --- DOM ELEMENTS ---
        const cartItemsContainer = document.getElementById('cart-items');
        const cartEmptyRowHTML = `<tr id="cart-empty-row"><td colspan="5" class="text-center py-10 text-gray-500">Cart is empty. Search for a product to begin.</td></tr>`;
        const posItemsJsonInput = document.getElementById('pos_items_json');
        const summarySubtotalEl = document.getElementById('summary-subtotal');
        const summaryDiscountEl = document.getElementById('summary-discount');
        const summaryTaxEl = document.getElementById('summary-tax');
        const summaryTotalEl = document.getElementById('summary-total');

        // --- UTILITY FUNCTIONS ---
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
        };

        // --- CART LOGIC ---
        function addToCart(product) {
            if (!product || !product.id) return;

            const existingItem = cart.find(item => item.product_id === product.id);

            if (existingItem) {
                if (existingItem.quantity < product.stock) {
                    existingItem.quantity++;
                } else {
                    alert(`Cannot add more ${product.name}. Stock limit reached (${product.stock}).`);
                }
            } else {
                if (product.stock > 0) {
                    cart.push({
                        product_id: product.id.toString(), // Ensure ID is a string for consistent matching
                        name: product.name,
                        price_per_unit: parseFloat(product.price),
                        quantity: 1,
                        stock: parseInt(product.stock, 10)
                    });
                } else {
                    alert(`${product.name} is out of stock.`);
                }
            }
            renderCart();
        }

        function updateQuantity(productId, newQuantity) {
            const item = cart.find(item => item.product_id === productId);
            if (!item) return;

            const quantity = Math.max(1, parseInt(newQuantity, 10) || 1);

            if (quantity > item.stock) {
                alert(`Cannot set quantity for ${item.name} above stock limit (${item.stock}).`);
                item.quantity = item.stock;
                // Reflect the corrected value back to the input field
                document.querySelector(`[data-product-id="${productId}"].cart-item-qty`).value = item.stock;
            } else {
                item.quantity = quantity;
            }
        }

        function removeFromCart(productId) {
            cart = cart.filter(item => item.product_id !== productId);
            renderCart();
        }

        function updateSummary() {
            const subtotal = cart.reduce((acc, item) => acc + (item.price_per_unit * item.quantity), 0);
            const discount = 0; // Placeholder for future discount logic
            const amountAfterDiscount = subtotal - discount;
            const tax = amountAfterDiscount * TAX_RATE;
            const total = amountAfterDiscount + tax;

            summarySubtotalEl.textContent = formatRupiah(subtotal);
            summaryDiscountEl.textContent = formatRupiah(discount);
            summaryTaxEl.textContent = formatRupiah(tax);
            summaryTotalEl.textContent = formatRupiah(total);
        }

        function renderCart() {
            cartItemsContainer.innerHTML = ''; // Clear current cart view

            if (cart.length === 0) {
                cartItemsContainer.innerHTML = cartEmptyRowHTML;
            } else {
                cart.forEach(item => {
                    const itemTotal = item.price_per_unit * item.quantity;
                    const row = document.createElement('tr');
                    row.className = 'border-b border-gray-200 hover:bg-gray-50';
                    row.innerHTML = `
                        <td class="py-3 px-5 text-left">${item.name}</td>
                        <td class="py-3 px-5 text-center">${formatRupiah(item.price_per_unit)}</td>
                        <td class="py-3 px-5 text-center">
                            <input type="number" value="${item.quantity}" min="1" max="${item.stock}" 
                                   class="cart-item-qty w-20 text-center border rounded py-1 px-2" 
                                   data-product-id="${item.product_id}">
                        </td>
                        <td class="py-3 px-5 text-right">${formatRupiah(itemTotal)}</td>
                        <td class="py-3 px-5 text-center">
                            <button type="button" class="remove-item-btn text-red-500 hover:text-red-700 p-1" title="Remove Item" data-product-id="${item.product_id}"><i class="fas fa-times-circle fa-lg"></i></button>
                        </td>
                    `;
                    cartItemsContainer.appendChild(row);
                });
            }

            updateSummary();
            // Only include necessary data for submission
            posItemsJsonInput.value = JSON.stringify(cart.map(({ product_id, quantity, price_per_unit }) => ({ product_id, quantity, price_per_unit })));
        }

        // --- EVENT LISTENERS ---

        // Product Search with Select2
        $('#product_search_select').select2({
            placeholder: 'Start typing to search for a product...',
            minimumInputLength: 1,
            ajax: {
                url: '<?= site_url('pesanan/ajax_product_search') ?>',
                dataType: 'json',
                delay: 250, // wait 250ms before triggering the request
                data: function (params) {
                    return {
                        term: params.term // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(product => ({
                            id: product.id,
                            text: `${product.name} (SKU: ${product.sku || 'N/A'})`,
                            ...product // Pass the whole product object
                        }))
                    };
                },
                cache: true
            },
            templateResult: formatProductResult,
            templateSelection: () => 'Search for a product...',
            escapeMarkup: (markup) => markup
        }).on('select2:select', function (e) {
            const product = e.params.data;
            addToCart(product);
            // Clear selection to allow searching for the next product
            $(this).val(null).trigger('change');
        });

        function formatProductResult(product) {
            if (product.loading) return product.text;
            return `<div class='select2-result-repository clearfix'>
                        <div class='select2-result-repository__title'>${product.text}</div>
                        <div class='select2-result-repository__statistics'>
                            <div class='select2-result-repository__forks'>Price: ${formatRupiah(product.price)}</div>
                            <div class='select2-result-repository__stargazers'>Stock: ${product.stock}</div>
                        </div>
                    </div>`;
        }

        // Event delegation for cart item actions
        cartItemsContainer.addEventListener('click', function(e) {
            // Use .closest() to reliably find the button, even if the SVG icon is clicked
            const removeBtn = e.target.closest('.remove-item-btn');
            if (removeBtn) {
                const productId = removeBtn.dataset.productId; // Get ID as string
                removeFromCart(productId);
            }
        });

        cartItemsContainer.addEventListener('input', function(e) {
            // Check if the event target is a quantity input field
            if (e.target && e.target.classList.contains('cart-item-qty')) {
                const productId = e.target.dataset.productId; // Get ID as string
                const newQuantity = parseInt(e.target.value, 10);
                
                // 1. Update the data model
                updateQuantity(productId, newQuantity);

                // 2. Directly update the item total in the same row
                const item = cart.find(item => item.product_id === productId);
                const row = e.target.closest('tr');
                row.querySelector('td:nth-child(4)').textContent = formatRupiah(item.price_per_unit * item.quantity);

                // 3. Update the final summary
                updateSummary();
                posItemsJsonInput.value = JSON.stringify(cart.map(({ product_id, quantity, price_per_unit }) => ({ product_id, quantity, price_per_unit })));
            }
        });

        // --- INITIALIZATION ---
        renderCart();
    });
</script>
<?= $this->endSection() ?>