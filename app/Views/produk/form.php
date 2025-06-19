<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<?= isset($product) ? 'Edit Product' : 'Create New Product' ?>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-semibold text-gray-800 mb-8">
        <?= isset($product) ? 'Edit Product' : 'Create New Product' ?>
    </h2>

    <?php if (isset($validation)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-md rounded-md" role="alert">
            <p class="font-bold text-lg mb-2">Validation Errors</p>
            <ul class="list-disc ml-5">
                <?php foreach ($validation->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php
        $form_action = isset($product) ? site_url('produk/update/' . $product['id']) : site_url('produk/store');
        $button_label = isset($product) ? 'Update Product' : 'Create Product';
    ?>

    <form action="<?= $form_action ?>" method="post" enctype="multipart/form-data" class="bg-white shadow-xl rounded-lg p-8">
        <?= csrf_field() ?>
        <?php if(isset($product)): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div class="mb-5">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Product Name:</label>
                <input type="text" name="name" id="name" value="<?= old('name', $product['name'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-150 ease-in-out" required>
            </div>

            <div class="mb-5">
                <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Category:</label>
                <select name="category_id" id="category_id"
                        class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-150 ease-in-out" required>
                    <option value="">Select Category</option>
                    <?php if (!empty($categories) && is_array($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= esc($category['id']) ?>" <?= (old('category_id', $product['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>>
                                <?= esc($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No categories available</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="mb-5 mt-6">
            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Description:</label>
            <textarea name="description" id="description" rows="4"
                      class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-150 ease-in-out"><?= old('description', $product['description'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 mt-6">
            <div class="mb-5 md:mb-0">
                <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Price (Rp):</label>
                <input type="number" name="price" id="price" value="<?= old('price', $product['price'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-150 ease-in-out" required step="0.01" min="0">
            </div>

            <div class="mb-5 md:mb-0">
                <label for="stock" class="block text-gray-700 text-sm font-bold mb-2">Stock:</label>
                <input type="number" name="stock" id="stock" value="<?= old('stock', $product['stock'] ?? 0) ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-150 ease-in-out" required min="0">
            </div>

            <div>
                <label for="sku" class="block text-gray-700 text-sm font-bold mb-2">SKU (Optional):</label>
                <input type="text" name="sku" id="sku" value="<?= old('sku', $product['sku'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition duration-150 ease-in-out">
            </div>
        </div>

        <div class="mb-6 mt-6">
            <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Product Image:</label>
            <input type="file" name="image" id="image"
                   class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition duration-150 ease-in-out">

            <?php if (isset($product) && !empty($product['image_path'])): ?>
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-1">Current image:</p>
                    <img src="<?= base_url('uploads/products/' . esc($product['image_path'])) ?>" alt="<?= esc($product['name'] ?? 'Product Image') ?>" class="h-24 w-24 object-cover rounded-md shadow">
                    <p class="mt-1 text-xs text-gray-500 italic"><?= esc($product['image_path']) ?></p>
                </div>
            <?php endif; ?>
            <p class="mt-2 text-xs text-gray-500 italic">Max file size: 2MB. Allowed types: JPG, PNG, GIF. Leave blank to keep current image if editing.</p>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-8 border-t pt-6">
            <a href="<?= site_url('produk') ?>"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
               Cancel
            </a>
            <button type="submit"
                    class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
                <i class="fas fa-save mr-2"></i><?= $button_label ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
