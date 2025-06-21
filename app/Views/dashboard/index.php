<?= $this->extend('layout/default') ?>

<?= $this->section('title') ?>
<?= esc($title ?? 'Dashboard') ?>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .info-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">Welcome, <?= esc($userName ?? 'User') ?>!</h1>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="info-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                <i class="fas fa-dollar-sign fa-2x"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Sales Today</p>
                <p class="text-2xl font-bold text-gray-800"><?= number_to_currency($salesToday ?? 0, 'IDR', 'id_ID', 0) ?></p>
            </div>
        </div>

        <div class="info-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="p-3 rounded-full bg-green-100 text-green-500">
                <i class="fas fa-shopping-cart fa-2x"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Orders Today</p>
                <p class="text-2xl font-bold text-gray-800"><?= esc($ordersToday ?? 0) ?></p>
            </div>
        </div>

        <div class="info-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                <i class="fas fa-users fa-2x"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Customers</p>
                <p class="text-2xl font-bold text-gray-800"><?= esc($totalCustomers ?? 0) ?></p>
            </div>
        </div>

        <div class="info-card bg-white p-6 rounded-xl shadow-lg flex items-center space-x-4">
            <div class="p-3 rounded-full <?= ($lowStockProducts > 0) ? 'bg-red-100 text-red-500' : 'bg-yellow-100 text-yellow-500' ?>">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Low Stock Items</p>
                <p class="text-2xl font-bold text-gray-800"><?= esc($lowStockProducts ?? 0) ?></p>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white shadow-xl rounded-lg p-4 md:p-6">
        <h3 class="text-xl font-semibold text-gray-700 mb-4">Sales - Last 7 Days</h3>
        <div class="relative h-64 md:h-80 lg:h-96">
             <canvas id="salesLast7DaysChart"></canvas>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        // Data passed from controller
        const chartSalesLabels = <?= $chartSalesLabels ?? '[]' ?>;
        const chartSalesData = <?= $chartSalesData ?? '[]' ?>;
    </script>
    <script src="<?= base_url('js/dashboard.js') ?>"></script>
<?= $this->endSection() ?>
