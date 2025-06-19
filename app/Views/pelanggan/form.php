<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<?= isset($customer) ? 'Edit Customer' : 'Create New Customer' ?>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-semibold text-gray-800 mb-8">
        <i class="fas <?= isset($customer) ? 'fa-user-edit' : 'fa-user-plus' ?> text-teal-500 mr-3"></i>
        <?= isset($customer) ? 'Edit Customer' : 'Create New Customer' ?>
    </h2>

    <?php if (isset($validation)): ?>
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
        $form_action = isset($customer) ? site_url('pelanggan/update/' . $customer['id']) : site_url('pelanggan/store');
        $button_label = isset($customer) ? 'Update Customer' : 'Create Customer';
    ?>

    <form action="<?= $form_action ?>" method="post" class="bg-white shadow-xl rounded-lg p-8">
        <?= csrf_field() ?>
        <?php if(isset($customer)): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div class="mb-6">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name:</label>
                <input type="text" name="name" id="name" value="<?= old('name', $customer['name'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition duration-150 ease-in-out" required>
            </div>

            <div class="mb-6">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address (Optional):</label>
                <input type="email" name="email" id="email" value="<?= old('email', $customer['email'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition duration-150 ease-in-out">
            </div>

            <div class="mb-6 md:col-span-2">
                <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number (Optional):</label>
                <input type="tel" name="phone" id="phone" value="<?= old('phone', $customer['phone'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition duration-150 ease-in-out">
            </div>
        </div>

        <div class="mb-8 mt-2">
            <label for="address" class="block text-gray-700 text-sm font-bold mb-2">Address (Optional):</label>
            <textarea name="address" id="address" rows="4"
                      class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition duration-150 ease-in-out"><?= old('address', $customer['address'] ?? '') ?></textarea>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-8 border-t pt-6">
            <a href="<?= site_url('pelanggan') ?>"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
               <i class="fas fa-times mr-2"></i>Cancel
            </a>
            <button type="submit"
                    class="bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
                <i class="fas fa-save mr-2"></i><?= $button_label ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
