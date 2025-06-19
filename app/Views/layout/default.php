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
                    <ul class="flex space-x-4">
                        <li><a href="<?= site_url('/') ?>" class="hover:text-blue-200">Dashboard</a></li>
                        <li><a href="<?= site_url('produk') ?>" class="hover:text-blue-200">Products</a></li>
                        <li><a href="<?= site_url('kategori') ?>" class="hover:text-blue-200">Categories</a></li>
                        <li><a href="<?= site_url('pesanan') ?>" class="hover:text-blue-200">Orders</a></li>
                        <li><a href="<?= site_url('pelanggan') ?>" class="hover:text-blue-200">Customers</a></li>
                        <li><a href="<?= site_url('pengguna') ?>" class="hover:text-blue-200">Users</a></li>
                        <li><a href="<?= site_url('laporan') ?>" class="hover:text-blue-200">Reports</a></li>
                        <li><a href="<?= site_url('auth/login') ?>" class="hover:text-blue-200">Login</a></li>
                        <!-- Placeholder for Login/Logout -->
                    </ul>
                </nav>
            </div>
        </header>

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

    <?= $this->renderSection('scripts') ?> <!-- Optional: for page-specific scripts -->
</body>
</html>
