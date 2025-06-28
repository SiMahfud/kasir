<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
Product Management
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-800">Product List</h2>
        <a href="<?= site_url('produk/create') ?>"
           class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
           <i class="fas fa-plus mr-2"></i>Add New Product
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
                    <th class="py-3 px-5 text-left">ID</th>
                    <th class="py-3 px-5 text-left">SKU</th>
                    <th class="py-3 px-5 text-left">Name</th>
                    <th class="py-3 px-5 text-left">Category</th>
                    <th class="py-3 px-5 text-left">Supplier</th> <!-- New Column -->
                    <th class="py-3 px-5 text-right">Price</th>
                    <th class="py-3 px-5 text-center">Stock</th>
                    <th class="py-3 px-5 text-center">Image</th>
                    <th class="py-3 px-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (!empty($products) && is_array($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="py-3 px-5 text-left whitespace-nowrap"><?= esc($product['id']) ?></td>
                            <td class="py-3 px-5 text-left"><?= esc($product['sku'] ?? 'N/A') ?></td>
                            <td class="py-3 px-5 text-left font-medium"><?= esc($product['name']) ?></td>
                            <td class="py-3 px-5 text-left"><?= esc($product['category_name'] ?? 'N/A') ?></td>
                            <td class="py-3 px-5 text-left"><?= esc($product['supplier_name'] ?? 'N/A') ?></td> <!-- New Column Data -->
                            <td class="py-3 px-5 text-right">Rp <?= number_format($product['price'] ?? 0, 0, ',', '.') ?></td>
                            <td class="py-3 px-5 text-center">
                                <?php
                                    $stock = $product['stock'] ?? 0;
                                    $stockClass = 'px-3 py-1 text-xs font-semibold rounded-full shadow-sm ';
                                    if ($stock > 10) {
                                        $stockClass .= 'bg-green-200 text-green-800';
                                    } elseif ($stock > 0) {
                                        $stockClass .= 'bg-yellow-200 text-yellow-800';
                                    } else {
                                        $stockClass .= 'bg-red-200 text-red-800';
                                    }
                                ?>
                                <span class="<?= $stockClass ?>">
                                    <?= esc($stock) ?>
                                </span>
                            </td>
                            <td class="py-3 px-5 text-center">
                                <?php if (!empty($product['image_path'])): ?>
                                    <img src="<?= base_url('uploads/products/' . esc($product['image_path'])) ?>" alt="<?= esc($product['name']) ?>" class="h-12 w-12 object-cover rounded-md mx-auto shadow">
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs italic">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-5 text-center">
                                <a href="<?= site_url('produk/' . $product['id'].'/edit') ?>"
                                   class="text-blue-600 hover:text-blue-800 font-semibold mr-3 transition duration-150 ease-in-out" title="Edit">
                                   <i class="fas fa-edit fa-lg"></i>
                                </a>
                                <a href="<?= site_url('produk/' . $product['id'].'/delete') ?>"
                                   class="text-red-600 hover:text-red-800 font-semibold transition duration-150 ease-in-out"
                                   onclick="return confirm('Are you sure you want to delete this product? This action cannot be undone.');" title="Delete">
                                   <i class="fas fa-trash-alt fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="py-6 px-5 text-center text-gray-500">No products found.</td> <!-- Adjusted colspan -->
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
