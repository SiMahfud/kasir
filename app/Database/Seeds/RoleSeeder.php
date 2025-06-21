<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\RoleModel;
use App\Models\PermissionModel;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roleModel = new RoleModel();
        $permissionModel = new PermissionModel();
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // 1. Insert Roles
        $rolesData = [
            ['name' => 'admin', 'description' => 'Administrator with all permissions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'staff', 'description' => 'Staff user with operational permissions', 'created_at' => $now, 'updated_at' => $now],
        ];
        $db->table('roles')->insertBatch($rolesData);

        // Get IDs of the created roles
        $adminRole = $roleModel->where('name', 'admin')->first();
        $staffRole = $roleModel->where('name', 'staff')->first();

        if (!$adminRole || !$staffRole) {
            echo "Error: Could not retrieve admin or staff role IDs after seeding roles.\n";
            return;
        }

        // 2. Fetch all permissions
        $allPermissions = $permissionModel->findAll();
        if (empty($allPermissions)) {
            echo "Warning: No permissions found in the permissions table. Make sure PermissionSeeder ran correctly.\n";
        }

        $allPermissionIds = array_map(function ($perm) {
            return $perm['id'];
        }, $allPermissions);

        // 3. Define Staff Permissions (subset)
        $staffPermissionKeys = [
            'products_view', 'products_create', 'products_edit',
            'categories_view',
            'suppliers_view',
            'customers_view', 'customers_create', 'customers_edit',
            'orders_view_all', 'orders_create_pos', 'orders_view_details',
            'reports_view_sales', 'reports_view_stock',
        ];

        $staffPermissionIds = [];
        foreach ($staffPermissionKeys as $key) {
            $perm = $permissionModel->where('permission_key', $key)->first();
            if ($perm) {
                $staffPermissionIds[] = $perm['id'];
            } else {
                echo "Warning: Staff permission key '$key' not found.\n";
            }
        }

        // 4. Prepare role_permissions data
        $rolePermissionsData = [];

        // Admin gets all permissions
        foreach ($allPermissionIds as $permissionId) {
            $rolePermissionsData[] = [
                'role_id' => $adminRole['id'],
                'permission_id' => $permissionId
            ];
        }

        // Staff gets defined subset
        foreach ($staffPermissionIds as $permissionId) {
            $rolePermissionsData[] = [
                'role_id' => $staffRole['id'],
                'permission_id' => $permissionId
            ];
        }

        // Remove duplicates if any permission was accidentally assigned twice (e.g. if a staff permission was also in general list for admin)
        $rolePermissionsData = array_unique($rolePermissionsData, SORT_REGULAR);


        // 5. Insert into role_permissions table
        if (!empty($rolePermissionsData)) {
            $db->table('role_permissions')->insertBatch($rolePermissionsData);
        }

        echo "Roles and Role-Permissions seeded successfully.\n";
    }
}
