<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $permissions = [
            // Users
            ['permission_key' => 'users_view', 'description' => 'View list of users', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'users_create', 'description' => 'Create new users', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'users_edit', 'description' => 'Edit existing users', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'users_delete', 'description' => 'Delete users', 'created_at' => $now, 'updated_at' => $now],

            // Products
            ['permission_key' => 'products_view', 'description' => 'View list of products', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'products_create', 'description' => 'Create new products', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'products_edit', 'description' => 'Edit existing products', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'products_delete', 'description' => 'Delete products', 'created_at' => $now, 'updated_at' => $now],

            // Categories
            ['permission_key' => 'categories_view', 'description' => 'View list of categories', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'categories_create', 'description' => 'Create new categories', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'categories_edit', 'description' => 'Edit existing categories', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'categories_delete', 'description' => 'Delete categories', 'created_at' => $now, 'updated_at' => $now],

            // Suppliers
            ['permission_key' => 'suppliers_view', 'description' => 'View list of suppliers', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'suppliers_create', 'description' => 'Create new suppliers', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'suppliers_edit', 'description' => 'Edit existing suppliers', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'suppliers_delete', 'description' => 'Delete suppliers', 'created_at' => $now, 'updated_at' => $now],

            // Customers
            ['permission_key' => 'customers_view', 'description' => 'View list of customers', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'customers_create', 'description' => 'Create new customers', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'customers_edit', 'description' => 'Edit existing customers', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'customers_delete', 'description' => 'Delete customers', 'created_at' => $now, 'updated_at' => $now],

            // Orders
            ['permission_key' => 'orders_view_all', 'description' => 'View all orders', 'created_at' => $now, 'updated_at' => $now],
            // ['permission_key' => 'orders_view_own', 'description' => 'View own orders (for specific staff if implemented)', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'orders_create_pos', 'description' => 'Create new orders via POS', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'orders_view_details', 'description' => 'View detailed information of an order', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'orders_edit', 'description' => 'Edit existing orders (e.g., status, items if pending)', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'orders_cancel', 'description' => 'Cancel orders', 'created_at' => $now, 'updated_at' => $now],
            // ['permission_key' => 'orders_process_refunds', 'description' => 'Process refunds for orders', 'created_at' => $now, 'updated_at' => $now],

            // Reports
            ['permission_key' => 'reports_view_sales', 'description' => 'View sales reports', 'created_at' => $now, 'updated_at' => $now],
            ['permission_key' => 'reports_view_stock', 'description' => 'View stock reports', 'created_at' => $now, 'updated_at' => $now],
            // ['permission_key' => 'reports_view_profit_loss', 'description' => 'View profit/loss reports', 'created_at' => $now, 'updated_at' => $now],
            // ['permission_key' => 'reports_export_data', 'description' => 'Export report data', 'created_at' => $now, 'updated_at' => $now],

            // Settings
            // ['permission_key' => 'settings_manage_general', 'description' => 'Manage general application settings', 'created_at' => $now, 'updated_at' => $now],
            // ['permission_key' => 'settings_manage_roles_permissions', 'description' => 'Manage roles and permissions', 'created_at' => $now, 'updated_at' => $now],
        ];

        $this->db->table('permissions')->insertBatch($permissions);
        echo "Permissions seeded successfully.\n";
    }
}
