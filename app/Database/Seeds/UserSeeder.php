<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
// Correct model name for users is PenggunaModel as per previous context
use App\Models\PenggunaModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $penggunaModel = new PenggunaModel();
        $roleModel = new \App\Models\RoleModel(); // Added RoleModel

        // Fetch Role IDs
        $adminRole = $roleModel->where('name', 'admin')->first();
        $staffRole = $roleModel->where('name', 'staff')->first();

        if (!$adminRole || !$staffRole) {
            echo "Error: Admin or Staff role not found. Make sure RoleSeeder has run and roles exist.\n";
            return;
        }

        $now = date('Y-m-d H:i:s'); // For manual timestamp setting if not using model's auto-timestamp

        $usersData = [
            [
                'name'     => 'Admin User',
                'email'    => 'admin@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'  => $adminRole['id'], // Use role_id
                'created_at' => $now,          // Manually set for direct DB insert
                'updated_at' => $now,
            ],
            [
                'name'     => 'Staff User',
                'email'    => 'staff@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role_id'  => $staffRole['id'], // Use role_id
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Using direct DB commands to ensure precise seeding control, including timestamps.
        // PenggunaModel also has $useTimestamps = true, so using $penggunaModel->save($data)
        // would also work and handle timestamps automatically if 'created_at'/'updated_at' were omitted here.
        foreach ($usersData as $data) {
            $this->db->table('users')->insert($data);
        }

        echo "Users seeded successfully with role_id.\n";
    }
}
