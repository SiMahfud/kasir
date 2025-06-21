<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<?= isset($supplier) ? 'Edit Supplier' : 'Create New Supplier' ?>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-semibold text-gray-800 mb-8 flex items-center">
        <i class="fas <?= isset($supplier) ? 'fa-edit' : 'fa-plus-circle' ?> text-indigo-500 mr-3"></i>
        <?= isset($supplier) ? 'Edit Supplier' : 'Create New Supplier' ?>
    </h2>

    <?php if (isset($validation) && $validation->getErrors()): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-md rounded-md" role="alert">
            <p class="font-bold text-lg mb-2">Please correct the following errors:</p>
            <ul class="list-disc list-inside">
                <?php foreach ($validation->getErrors() as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php
        $form_action = isset($supplier) ? site_url('suppliers/update/' . $supplier['id']) : site_url('suppliers/create');
        // Note: Resource controller uses POST to create() for store.
        // If using $routes->resource(), POST to /suppliers will hit create().
        // For update, form should have _method=PUT and POST to /suppliers/(:num) to hit update().
        // The controller is already set up for this with `update($id)`.
        // The form action for create should be `site_url('suppliers')` if following strict REST for resource controller.
        // However, `SupplierController::create()` handles POST, so `site_url('suppliers/create')` also works if routed.
        // For simplicity and consistency with resource, let's adjust create action if needed or ensure routes handle it.
        // The $routes->resource('suppliers') maps POST /suppliers to create().
        if (!isset($supplier)) { // For create form
            $form_action = site_url('suppliers');
        }

        $button_label = isset($supplier) ? 'Update Supplier' : 'Create Supplier';
    ?>

    <form action="<?= $form_action ?>" method="post" class="bg-white shadow-xl rounded-lg p-8">
        <?= csrf_field() ?>
        <?php if(isset($supplier)): ?>
            <input type="hidden" name="_method" value="PUT"> <!-- For resource update -->
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div class="mb-5">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Supplier Name: <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="<?= old('name', $supplier['name'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out" required>
            </div>

            <div class="mb-5">
                <label for="contact_person" class="block text-gray-700 text-sm font-bold mb-2">Contact Person (Optional):</label>
                <input type="text" name="contact_person" id="contact_person" value="<?= old('contact_person', $supplier['contact_person'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
            </div>

            <div class="mb-5">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email (Optional):</label>
                <input type="email" name="email" id="email" value="<?= old('email', $supplier['email'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
            </div>

            <div class="mb-5">
                <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone (Optional):</label>
                <input type="tel" name="phone" id="phone" value="<?= old('phone', $supplier['phone'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out">
            </div>
        </div>

        <div class="mb-6 mt-2">
            <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address (Optional):</label>
            <textarea name="address" id="address" rows="3"
                      class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out"><?= old('address', $supplier['address'] ?? '') ?></textarea>
        </div>

        <div class="mb-8">
            <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Notes (Optional):</label>
            <textarea name="notes" id="notes" rows="3"
                      class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out"><?= old('notes', $supplier['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-8 border-t pt-6">
            <a href="<?= site_url('suppliers') ?>"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
               <i class="fas fa-times mr-2"></i>Cancel
            </a>
            <button type="submit"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
                <i class="fas fa-save mr-2"></i><?= $button_label ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
