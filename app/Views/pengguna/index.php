<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
User Management
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-800">User List</h2>
        <a href="<?= site_url('pengguna/create') ?>"
           class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
           <i class="fas fa-plus mr-2"></i>Add New User
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
            <thead class="bg-gray-200">
                <tr class="text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-5 text-left">ID</th>
                    <th class="py-3 px-5 text-left">Name</th>
                    <th class="py-3 px-5 text-left">Email</th>
                    <th class="py-3 px-5 text-left">Role</th>
                    <th class="py-3 px-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (!empty($users) && is_array($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100 transition duration-150 ease-in-out">
                            <td class="py-4 px-5 text-left whitespace-nowrap"><?= esc($user['id']) ?></td>
                            <td class="py-4 px-5 text-left"><?= esc($user['name']) ?></td>
                            <td class="py-4 px-5 text-left"><?= esc($user['email']) ?></td>
                            <td class="py-4 px-5 text-left">
                                <span class="capitalize px-3 py-1 text-xs font-bold rounded-full shadow-sm
                                    <?= $user['role'] === 'admin' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' ?>">
                                    <?= esc($user['role']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                <a href="<?= site_url('pengguna/edit/' . $user['id']) ?>"
                                   class="text-blue-600 hover:text-blue-800 font-semibold mr-4 transition duration-150 ease-in-out">
                                   <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="<?= site_url('pengguna/delete/' . $user['id']) ?>"
                                   class="text-red-600 hover:text-red-800 font-semibold transition duration-150 ease-in-out"
                                   onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                   <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-6 px-5 text-center text-gray-500">No users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($pager) && $pager): ?>
        <div class="mt-8">
            <?= $pager->links('default', 'tailwind_pagination') // Assuming you have a tailwind_pagination view ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>
