<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?: 'My Store' ?></title>
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <?= $this->renderSection('head') ?>
</head>
<body class="bg-gray-100">

    <div class="flex flex-col min-h-screen">

        <!-- Header/Navigation -->
        <header class="bg-blue-600 text-white p-4 shadow-md">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-bold">My Store</h1>
                <nav>
                    <ul class="flex space-x-4 items-center">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="<?= site_url('/') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Dashboard</a></li>
                            <?php if (hasPermission('products_view')): ?>
                                <li><a href="<?= site_url('produk') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Products</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('categories_view')): ?>
                                <li><a href="<?= site_url('kategori') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Categories</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('orders_view_all')): ?>
                                <li><a href="<?= site_url('pesanan') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Orders</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('customers_view')): ?>
                                <li><a href="<?= site_url('pelanggan') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Customers</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('suppliers_view')): ?>
                                <li><a href="<?= site_url('suppliers') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Suppliers</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('users_view')): // Typically admin only, already handled by users_view permission check ?>
                                <li><a href="<?= site_url('pengguna') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Users</a></li>
                            <?php endif; ?>
                            <?php if (hasPermission('reports_view_sales') || hasPermission('reports_view_stock')): ?>
                                <li><a href="<?= site_url('laporan/penjualan') ?>" class="hover:text-blue-200 transition duration-150 ease-in-out">Reports</a></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (isLoggedIn()): ?>
                            <li>
                                <span class="text-sm text-blue-100">Welcome, <?= esc(currentUserName() ?? 'User') ?> (<?= esc(currentUserRole() ?? '') ?>)</span>
                            </li>
                            <li><a href="<?= site_url('logout') ?>" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Logout</a></li>
                        <?php else: ?>
                            <li><a href="<?= site_url('login') ?>" class="bg-green-500 hover:bg-green-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">Login</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Flash Messages - Global -->
        <?php if (session()->getFlashdata('message')): ?>
            <div class="container mx-auto mt-4">
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-md rounded-md" role="alert">
                    <p><?= session()->getFlashdata('message') ?></p>
                </div>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="container mx-auto mt-4">
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow-md rounded-md" role="alert">
                    <p><?= session()->getFlashdata('error') ?></p>
                </div>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('info')): ?>
            <div class="container mx-auto mt-4">
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 shadow-md rounded-md" role="alert">
                    <p><?= session()->getFlashdata('info') ?></p>
                </div>
            </div>
        <?php endif; ?>


        <!-- Main Content Area -->
        <main class="flex-grow container mx-auto p-6">
            <?= $this->renderSection('content') ?>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
            <div class="container mx-auto">
                <p>&copy; <?= date('Y') ?> My Store. All rights reserved.</p>
            </div>
        </footer>

    </div>

    <script>
        const siteUrl = '<?= rtrim(site_url(), '/') . '/' ?>'; // Ensures it ends with a slash
        const baseUrl = '<?= rtrim(base_url(), '/') . '/' ?>'; // For assets if needed, ends with a slash
    </script>
    <?= $this->renderSection('scripts') ?> <!-- Optional: for page-specific scripts -->
</body>
</html>
