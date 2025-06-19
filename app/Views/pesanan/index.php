<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
Order Management
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-800">Order List</h2>
        <a href="<?= site_url('pesanan/new') ?>"
           class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
           <i class="fas fa-plus-circle mr-2"></i>Create New Order (POS)
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm rounded-md" role="alert">
            <p><?= session()->getFlashdata('message') ?></p>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm rounded-md" role="alert">
            <p><?= session()->getFlashdata('error') ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-xl rounded-lg overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-gray-100">
                <tr class="text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-5 text-left">Order ID</th>
                    <th class="py-3 px-5 text-left">Date</th>
                    <th class="py-3 px-5 text-left">Customer</th>
                    <th class="py-3 px-5 text-left">Staff</th>
                    <th class="py-3 px-5 text-right">Total Amount</th>
                    <th class="py-3 px-5 text-center">Status</th>
                    <th class="py-3 px-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (!empty($orders) && is_array($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="py-4 px-5 text-left whitespace-nowrap">#<?= esc($order['id']) ?></td>
                            <td class="py-4 px-5 text-left whitespace-nowrap">
                                <?= esc(date('d M Y, H:i', strtotime($order['order_date']))) ?>
                            </td>
                            <td class="py-4 px-5 text-left"><?= esc($order['customer_name'] ?? 'N/A (Guest)') ?></td>
                            <td class="py-4 px-5 text-left"><?= esc($order['user_name'] ?? 'N/A') ?></td>
                            <td class="py-4 px-5 text-right font-medium">
                                Rp <?= number_format($order['total_amount'] ?? 0, 0, ',', '.') ?>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <?php
                                    $status = strtolower($order['status'] ?? 'unknown');
                                    $status_bg = 'bg-gray-200'; $status_text = 'text-gray-800';
                                    if ($status === 'completed') { $status_bg = 'bg-green-200'; $status_text = 'text-green-800'; }
                                    elseif ($status === 'pending') { $status_bg = 'bg-yellow-200'; $status_text = 'text-yellow-800'; }
                                    elseif ($status === 'cancelled') { $status_bg = 'bg-red-200'; $status_text = 'text-red-800'; }
                                ?>
                                <span class="capitalize px-3 py-1 text-xs font-semibold rounded-full shadow-sm <?= $status_bg ?> <?= $status_text ?>">
                                    <?= esc($order['status']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                <a href="<?= site_url('pesanan/view/' . $order['id']) ?>"
                                   class="text-indigo-600 hover:text-indigo-800 font-semibold mr-3 transition duration-150 ease-in-out" title="View Details">
                                   <i class="fas fa-eye fa-lg"></i>
                                </a>
                                <?php if (strtolower($order['status'] ?? '') === 'pending'): ?>
                                    <a href="<?= site_url('pesanan/edit/' . $order['id']) ?>"
                                       class="text-blue-600 hover:text-blue-800 font-semibold mr-3 transition duration-150 ease-in-out" title="Edit Order">
                                       <i class="fas fa-edit fa-lg"></i>
                                    </a>
                                    <a href="<?= site_url('pesanan/cancel/' . $order['id']) ?>"
                                       class="text-red-600 hover:text-red-800 font-semibold transition duration-150 ease-in-out"
                                       title="Cancel Order"
                                       onclick="return confirm('Are you sure you want to cancel this order?');">
                                       <i class="fas fa-times-circle fa-lg"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-6 px-5 text-center text-gray-500">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pager) && $pager): ?>
        <div class="mt-8">
            <?= $pager->links('default', 'tailwind_pagination') // Assuming a tailwind_pagination view for pager ?>
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
