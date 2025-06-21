<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('UserSeeder');
        $this->call('CategorySeeder');
        // ProductSeeder depends on CategorySeeder for category IDs
        $this->call('ProductSeeder');
        $this->call('CustomerSeeder');

        // New seeders for Roles and Permissions
        // PermissionSeeder should run before RoleSeeder, as RoleSeeder might query permissions
        $this->call('PermissionSeeder');
        $this->call('RoleSeeder');
        // Add other seeders here if created, e.g., OrderSeeder in the future

        echo "All base seeders run successfully.\n";
    }
}
