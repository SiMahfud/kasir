<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
Sales Report
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Link to a date picker CSS if you plan to use one, e.g., Flatpickr -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> -->
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-800 mb-4 sm:mb-0 flex items-center">
            <i class="fas fa-chart-line mr-3 text-blue-500"></i>Sales Report
        </h2>
        <div class="flex space-x-2">
             <?php if (hasPermission('reports_export_data')): ?>
            <a href="#" id="exportSalesCsvBtn"
               class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out text-sm flex items-center">
                <i class="fas fa-file-csv mr-2"></i>Export to CSV
            </a>
            <?php endif; ?>
            <!-- PDF button placeholder -->
            <!-- <button class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-3 rounded-lg shadow-md text-sm transition duration-150 ease-in-out">
                <i class="fas fa-file-pdf mr-1"></i>Export PDF
            </button> -->
        </div>
    </div>

    <!-- Filter Section -->
    <form method="get" action="<?= site_url('laporan/penjualan') ?>" class="bg-white shadow-lg rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
            <div>
                <label for="tanggal_awal" class="block text-sm font-medium text-gray-700 mb-1">Start Date:</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" value="<?= esc($filters['tanggal_awal'] ?? '') ?>" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div>
                <label for="tanggal_akhir" class="block text-sm font-medium text-gray-700 mb-1">End Date:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="<?= esc($filters['tanggal_akhir'] ?? '') ?>" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <!-- Add other filters if needed, e.g., by status, by customer -->
            <div class="sm:col-span-2 lg:col-span-1">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="<?= site_url('laporan/penjualan') ?>" class="w-full block text-center bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center justify-center">
                    <i class="fas fa-undo mr-2"></i>Reset Filters
                </a>
            </div>
        </div>
    </form>

    <!-- Report Summary -->
    <div class="bg-white shadow-lg rounded-lg p-6 mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="text-center p-4 border border-gray-200 rounded-lg bg-blue-50">
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Total Orders</h4>
            <p class="text-3xl font-bold text-blue-600"><?= esc($summary['total_orders'] ?? 0) ?></p>
        </div>
        <div class="text-center p-4 border border-gray-200 rounded-lg bg-green-50">
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Total Revenue</h4>
            <p class="text-3xl font-bold text-green-600">Rp <?= number_format($summary['total_revenue'] ?? 0, 0, ',', '.') ?></p>
        </div>
        <div class="text-center p-4 border border-gray-200 rounded-lg bg-purple-50">
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Avg. Order Value</h4>
            <p class="text-3xl font-bold text-purple-600">Rp <?= number_format($summary['average_order_value'] ?? 0, 0, ',', '.') ?></p>
        </div>
    </div>

    <!-- Sales Data Table -->
    <div class="bg-white shadow-xl rounded-lg overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-gray-100">
                <tr class="text-gray-600 uppercase text-xs leading-normal">
                    <th class="py-3 px-5 text-left">Order ID</th>
                    <th class="py-3 px-5 text-left">Date</th>
                    <th class="py-3 px-5 text-left">Customer</th>
                    <th class="py-3 px-5 text-left">Staff</th>
                    <th class="py-3 px-5 text-center">Items Sold</th>
                    <th class="py-3 px-5 text-right">Total Amount</th>
                    <th class="py-3 px-5 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (!empty($sales_data) && is_array($sales_data)): ?>
                    <?php foreach ($sales_data as $sale): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="py-3 px-5 text-left">
                                <a href="<?= site_url('pesanan/view/' . esc($sale['id'])) ?>" class="text-indigo-600 hover:underline font-medium">
                                    #<?= esc($sale['id']) ?>
                                </a>
                            </td>
                            <td class="py-3 px-5 text-left whitespace-nowrap"><?= esc(date('d M Y, H:i', strtotime($sale['order_date']))) ?></td>
                            <td class="py-3 px-5 text-left"><?= esc($sale['customer_name'] ?? 'N/A (Guest)') ?></td>
                            <td class="py-3 px-5 text-left"><?= esc($sale['user_name'] ?? 'N/A') ?></td>
                            <td class="py-3 px-5 text-center"><?= esc($sale['total_items'] ?? 0) ?></td>
                            <td class="py-3 px-5 text-right font-medium">Rp <?= number_format($sale['total_amount'] ?? 0, 0, ',', '.') ?></td>
                            <td class="py-3 px-5 text-center">
                                <?php
                                    $status = strtolower($sale['status'] ?? 'unknown');
                                    $status_bg = 'bg-gray-200'; $status_text = 'text-gray-800';
                                    if ($status === 'completed') { $status_bg = 'bg-green-200'; $status_text = 'text-green-800'; }
                                    elseif ($status === 'pending') { $status_bg = 'bg-yellow-200'; $status_text = 'text-yellow-800'; }
                                    elseif ($status === 'cancelled') { $status_bg = 'bg-red-200'; $status_text = 'text-red-800'; }
                                ?>
                                <span class="capitalize px-3 py-1 text-xs font-semibold rounded-full shadow-sm <?= $status_bg ?> <?= $status_text ?>">
                                    <?= esc($sale['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-8 px-5 text-center text-gray-500 text-lg">
                            No sales data found for the selected filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <?php if (isset($pager) && $pager): ?>
        <div class="mt-8">
            <?= $pager->links('default', 'tailwind_pagination') // Assuming a tailwind_pagination view for pager ?>
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Flatpickr initialization
            // flatpickr("input[type=date]", {
            //     dateFormat: "Y-m-d",
            //     altInput: true,
            //     altFormat: "F j, Y",
            // });

            const exportBtn = document.getElementById('exportSalesCsvBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tanggalAwal = document.getElementById('tanggal_awal').value;
                    const tanggalAkhir = document.getElementById('tanggal_akhir').value;

                    let exportUrl = '<?= site_url('laporan/export_sales_csv') ?>';
                    const params = new URLSearchParams();

                    if (tanggalAwal) {
                        params.append('tanggal_awal', tanggalAwal);
                    }
                    if (tanggalAkhir) {
                        params.append('tanggal_akhir', tanggalAkhir);
                    }
                    // Add other filter parameters here if they are added to the form
                    // e.g., const status = document.getElementById('status_filter').value;
                    // if (status) params.append('status', status);

                    if (params.toString()) {
                        exportUrl += '?' + params.toString();
                    }
                    window.location.href = exportUrl;
                });
            }
        });
    </script>
<?= $this->endSection() ?>
