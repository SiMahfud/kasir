<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
Stock Report
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-800 mb-4 sm:mb-0 flex items-center">
            <i class="fas fa-boxes mr-3 text-blue-500"></i>Stock Report
        </h2>
        <!-- Optional: Export buttons -->
    </div>

    <!-- Filter Section -->
    <form method="get" action="<?= site_url('laporan/stok') ?>" class="bg-white shadow-lg rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category:</label>
                <select name="category_id" id="category_id" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Categories</option>
                    <?php if (!empty($categories) && is_array($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['id']) ?>" <?= (isset($filters['category_id']) && esc($filters['category_id']) == $category['id']) ? 'selected' : '' ?>>
                                <?= esc($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div>
                <label for="stock_level" class="block text-sm font-medium text-gray-700 mb-1">Stock Level:</label>
                <select name="stock_level" id="stock_level" class="mt-1 block w-full p-3 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    <option value="low" <?= (isset($filters['stock_level']) && esc($filters['stock_level']) === 'low') ? 'selected' : '' ?>>Low Stock (Qty &lt;= 10)</option>
                    <option value="out" <?= (isset($filters['stock_level']) && esc($filters['stock_level']) === 'out') ? 'selected' : '' ?>>Out of Stock (Qty = 0)</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="<?= site_url('laporan/stok') ?>" class="w-full block text-center bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center justify-center">
                    <i class="fas fa-undo mr-2"></i>Reset Filters
                </a>
            </div>
        </div>
    </form>

    <!-- Stock Data Table -->
    <div class="bg-white shadow-xl rounded-lg overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-gray-100">
                <tr class="text-gray-600 uppercase text-xs leading-normal">
                    <th class="py-3 px-5 text-left">Product ID</th>
                    <th class="py-3 px-5 text-left">SKU</th>
                    <th class="py-3 px-5 text-left">Product Name</th>
                    <th class="py-3 px-5 text-left">Category</th>
                    <th class="py-3 px-5 text-center">Current Stock</th>
                    <th class="py-3 px-5 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (!empty($stock_data) && is_array($stock_data)): ?>
                    <?php
                        // Default stock threshold for "low stock" if not defined elsewhere
                        $low_stock_threshold = defined('LOW_STOCK_THRESHOLD') ? LOW_STOCK_THRESHOLD : 10;
                    ?>
                    <?php foreach ($stock_data as $item): ?>
                        <?php
                            $current_stock = $item['stock'] ?? 0;
                            $row_bg_class = '';
                            $stock_text_color = 'text-green-600';
                            $status_text = 'In Stock';
                            $status_color_class = 'bg-green-200 text-green-800';

                            if ($current_stock <= 0) {
                                $row_bg_class = 'bg-red-50 hover:bg-red-100';
                                $stock_text_color = 'text-red-600';
                                $status_text = 'Out of Stock';
                                $status_color_class = 'bg-red-200 text-red-800';
                            } elseif ($current_stock <= $low_stock_threshold) {
                                $row_bg_class = 'bg-yellow-50 hover:bg-yellow-100';
                                $stock_text_color = 'text-yellow-600';
                                $status_text = 'Low Stock';
                                $status_color_class = 'bg-yellow-200 text-yellow-800';
                            }
                        ?>
                        <tr class="border-b border-gray-200 <?= $row_bg_class ?> transition duration-150 ease-in-out">
                            <td class="py-3 px-5 text-left">
                                <a href="<?= site_url('produk/edit/' . esc($item['id'])) ?>" class="text-indigo-600 hover:underline font-medium">
                                    #<?= esc($item['id']) ?>
                                </a>
                            </td>
                            <td class="py-3 px-5 text-left"><?= esc($item['sku'] ?? 'N/A') ?></td>
                            <td class="py-3 px-5 text-left font-medium"><?= esc($item['name']) ?></td>
                            <td class="py-3 px-5 text-left"><?= esc($item['category_name'] ?? 'N/A') ?></td>
                            <td class="py-3 px-5 text-center font-bold <?= $stock_text_color ?>">
                                <?= esc($current_stock) ?>
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="capitalize px-3 py-1 text-xs font-semibold rounded-full shadow-sm <?= $status_color_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-8 px-5 text-center text-gray-500 text-lg">
                            No stock data found for the selected filters.
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
