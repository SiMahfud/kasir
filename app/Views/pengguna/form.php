<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<?= isset($user) ? 'Edit User' : 'Create New User' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-semibold text-gray-800 mb-8">
        <?= isset($user) ? 'Edit User' : 'Create New User' ?>
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
        $form_action = isset($user) ? site_url('pengguna/update/' . $user['id']) : site_url('pengguna/store');
        $button_label = isset($user) ? 'Update User' : 'Create User';
    ?>

    <form action="<?= $form_action ?>" method="post" class="bg-white shadow-xl rounded-lg p-8">
        <?= csrf_field() ?>
        <?php if(isset($user)): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            <div class="mb-5">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" name="name" id="name" value="<?= old('name', $user['name'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out" required>
            </div>

            <div class="mb-5">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email:</label>
                <input type="email" name="email" id="email" value="<?= old('email', $user['email'] ?? '') ?>"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out" required>
            </div>

            <div class="mb-5">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" name="password" id="password"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 mb-1 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out"
                       <?= isset($user) ? '' : 'required' ?>>
                <?php if(isset($user)): ?>
                    <p class="text-xs text-gray-500 italic mt-1">Leave blank to keep current password.</p>
                <?php endif; ?>
            </div>

            <div class="mb-6">
                <label for="password_confirm" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
                <input type="password" name="password_confirm" id="password_confirm"
                       class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out"
                       <?= isset($user) ? '' : 'required' ?>>
            </div>

            <div class="mb-6 md:col-span-2">
                <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role:</label>
                <select name="role" id="role"
                        class="shadow-sm appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out">
                    <option value="staff" <?= (old('role', $user['role'] ?? '') === 'staff') ? 'selected' : '' ?>>Staff</option>
                    <option value="admin" <?= (old('role', $user['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-4 mt-8 border-t pt-6">
            <a href="<?= site_url('pengguna') ?>"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
               Cancel
            </a>
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
                <i class="fas fa-save mr-2"></i><?= $button_label ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>
