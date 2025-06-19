<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
// Correct model name for users is PenggunaModel as per previous context
use App\Models\PenggunaModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $model = new PenggunaModel();
        // Timestamps will be handled by the model if $useTimestamps is true.
        // The model's $allowedFields should include 'created_at', 'updated_at' if setting them manually,
        // but it's better to let the model handle them.
        // If model's $useTimestamps is false, or if you want explicit control for seeding:
        $now = date('Y-m-d H:i:s');

        $usersData = [
            [
                'name'     => 'Admin User',
                'email'    => 'admin@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'admin',
                // 'created_at' => $now, // Let model handle if useTimestamps = true
                // 'updated_at' => $now, // Let model handle if useTimestamps = true
            ],
            [
                'name'     => 'Staff User',
                'email'    => 'staff@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'role'     => 'staff',
                // 'created_at' => $now,
                // 'updated_at' => $now,
            ],
        ];

        // Using direct DB commands to bypass model validations/callbacks for seeding if preferred
        // This also means manually handling timestamps if model doesn't do it or if bypassing model.
        // The PenggunaModel is set to use timestamps, so it should handle them.
        foreach ($usersData as $data) {
            // If model's $useTimestamps = true, it will fill created_at and updated_at
            $this->db->table('users')->insert($data);
            // Alternatively, using the model:
            // $model->save($data); // This would trigger model's timestamp handling
        }

        echo "Users seeded successfully.\n";
    }
}
