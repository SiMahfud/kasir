<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
Customer Management
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-800">Customer List</h2>
        <a href="<?= site_url('pelanggan/create') ?>"
           class="bg-teal-500 hover:bg-teal-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out flex items-center">
           <i class="fas fa-user-plus mr-2"></i>Add New Customer
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
                    <th class="py-3 px-5 text-left">Name</th>
                    <th class="py-3 px-5 text-left">Email</th>
                    <th class="py-3 px-5 text-left">Phone</th>
                    <th class="py-3 px-5 text-left">Address</th>
                    <th class="py-3 px-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (!empty($customers) && is_array($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                            <td class="py-4 px-5 text-left whitespace-nowrap"><?= esc($customer['id']) ?></td>
                            <td class="py-4 px-5 text-left font-medium"><?= esc($customer['name']) ?></td>
                            <td class="py-4 px-5 text-left text-gray-600"><?= esc($customer['email'] ?? 'N/A') ?></td>
                            <td class="py-4 px-5 text-left text-gray-600"><?= esc($customer['phone'] ?? 'N/A') ?></td>
                            <td class="py-4 px-5 text-left text-gray-600">
                                <?= nl2br(esc($customer['address'] ?? 'N/A')) ?>
                            </td>
                            <td class="py-4 px-5 text-center whitespace-nowrap">
                                <a href="<?= site_url('pelanggan/edit/' . $customer['id']) ?>"
                                   class="text-blue-600 hover:text-blue-800 font-semibold mr-4 transition duration-150 ease-in-out" title="Edit">
                                   <i class="fas fa-edit fa-lg"></i>
                                </a>
                                <a href="<?= site_url('pelanggan/delete/' . $customer['id']) ?>"
                                   class="text-red-600 hover:text-red-800 font-semibold transition duration-150 ease-in-out"
                                   title="Delete"
                                   onclick="return confirm('Are you sure you want to delete this customer?');">
                                   <i class="fas fa-trash-alt fa-lg"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-6 px-5 text-center text-gray-500">No customers found.</td>
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
