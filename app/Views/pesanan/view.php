<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
Order Details #<?= esc($order['id'] ?? 'N/A') ?>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">

    <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
        <h2 class="text-3xl font-semibold text-gray-800">
            Order Details <span class="text-indigo-600">#<?= esc($order['id'] ?? 'N/A') ?></span>
        </h2>
        <a href="<?= site_url('pesanan') ?>"
           class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
           <i class="fas fa-arrow-left mr-2"></i>Back to Order List
        </a>
        <a href="<?= site_url('pesanan/receipt/' . ($order['id'] ?? '')) ?>" target="_blank"
           class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center no-print">
           <i class="fas fa-print mr-2"></i>Print Receipt
        </a>
        <?php if (isset($order) && strtolower($order['status'] ?? '') === 'pending' && hasPermission('orders_cancel')): ?>
            <a href="<?= site_url('pesanan/cancel/' . $order['id']) ?>"
               class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center no-print"
               onclick="return confirm('Are you sure you want to cancel this order? Stock will be restored.');">
                <i class="fas fa-times-circle mr-2"></i>Cancel Order
            </a>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-md" role="alert">
            <p><?= session()->getFlashdata('message') ?></p>
        </div>
    <?php endif; ?>

    <!-- Order Information Section -->
    <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-3 flex items-center">
            <i class="fas fa-file-invoice-dollar mr-3 text-indigo-500"></i>Order Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm text-gray-700">
            <div><strong class="text-gray-600 font-medium">Order ID:</strong> #<?= esc($order['id'] ?? 'N/A') ?></div>
            <div><strong class="text-gray-600 font-medium">Order Date:</strong> <?= esc(date('d M Y, H:i A', strtotime($order['order_date'] ?? time()))) ?></div>
            <div>
                <strong class="text-gray-600 font-medium">Status:</strong>
                <?php
                    $status = strtolower($order['status'] ?? 'unknown');
                    $status_bg = 'bg-gray-200'; $status_text = 'text-gray-800';
                    if ($status === 'completed') { $status_bg = 'bg-green-200'; $status_text = 'text-green-800'; }
                    elseif ($status === 'pending') { $status_bg = 'bg-yellow-200'; $status_text = 'text-yellow-800'; }
                    elseif ($status === 'cancelled') { $status_bg = 'bg-red-200'; $status_text = 'text-red-800'; }
                ?>
                <span class="capitalize px-3 py-1 text-xs font-semibold rounded-full shadow-sm <?= $status_bg ?> <?= $status_text ?>">
                    <?= esc($order['status'] ?? 'N/A') ?>
                </span>
            </div>
            <div><strong class="text-gray-600 font-medium">Processed by (Staff):</strong> <?= esc($order['user_name'] ?? 'N/A') ?></div>
        </div>
    </div>

    <!-- Customer Information Section -->
    <?php if (!empty($customer)): ?>
        <div class="bg-white shadow-xl rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-3 flex items-center">
                <i class="fas fa-user-circle mr-3 text-indigo-500"></i>Customer Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm text-gray-700">
                <div><strong class="text-gray-600 font-medium">Name:</strong> <?= esc($customer['name']) ?></div>
                <div><strong class="text-gray-600 font-medium">Email:</strong> <?= esc($customer['email'] ?? 'N/A') ?></div>
                <div><strong class="text-gray-600 font-medium">Phone:</strong> <?= esc($customer['phone'] ?? 'N/A') ?></div>
                <div class="md:col-span-2"><strong class="text-gray-600 font-medium">Address:</strong> <?= nl2br(esc($customer['address'] ?? 'N/A')) ?></div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-xl rounded-lg p-6 mb-6 text-gray-600 text-sm">
            <i class="fas fa-user-slash mr-2 text-gray-400"></i>No customer information associated with this order (Guest checkout or customer not found).
        </div>
    <?php endif; ?>

    <!-- Order Items Table -->
    <div class="bg-white shadow-xl rounded-lg overflow-x-auto">
        <h3 class="text-xl font-semibold text-gray-800 mb-0 p-6 border-b flex items-center">
            <i class="fas fa-shopping-cart mr-3 text-indigo-500"></i>Order Items
        </h3>
        <table class="min-w-full leading-normal">
            <thead class="bg-gray-100">
                <tr class="text-gray-600 uppercase text-xs leading-normal">
                    <th class="py-3 px-5 text-left">Product ID</th>
                    <th class="py-3 px-5 text-left">Product Name</th>
                    <th class="py-3 px-5 text-left">SKU</th>
                    <th class="py-3 px-5 text-center">Quantity</th>
                    <th class="py-3 px-5 text-right">Price per Unit</th>
                    <th class="py-3 px-5 text-right">Total Price</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (!empty($order_items) && is_array($order_items)): ?>
                    <?php foreach ($order_items as $item): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="py-3 px-5 text-left">#<?= esc($item['product_id']) ?></td>
                            <td class="py-3 px-5 text-left font-medium"><?= esc($item['product_name'] ?? 'N/A') ?></td>
                            <td class="py-3 px-5 text-left"><?= esc($item['product_sku'] ?? 'N/A') ?></td>
                            <td class="py-3 px-5 text-center"><?= esc($item['quantity']) ?></td>
                            <td class="py-3 px-5 text-right">Rp <?= number_format($item['price_per_unit'] ?? 0, 0, ',', '.') ?></td>
                            <td class="py-3 px-5 text-right font-semibold">Rp <?= number_format($item['total_price'] ?? 0, 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-6 px-5 text-center text-gray-500">No items found for this order.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="bg-gray-100">
                <tr class="font-semibold text-gray-700 uppercase text-sm">
                    <td colspan="5" class="py-3 px-5 text-right">Grand Total:</td>
                    <td class="py-4 px-5 text-right text-base">Rp <?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Order Notes -->
    <?php if (!empty($order['notes'])): ?>
        <div class="bg-white shadow-xl rounded-lg p-6 mt-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-3 flex items-center">
                <i class="fas fa-sticky-note mr-3 text-indigo-500"></i>Order Notes
            </h3>
            <p class="text-gray-600 text-sm whitespace-pre-wrap"><?= esc($order['notes']) ?></p>
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
