<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\CustomerModel;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $model = new CustomerModel();
        // Timestamps will be handled by the model.
        // $now = date('Y-m-d H:i:s');

        $customersData = [
            [
                'name'    => 'Budi Santoso',
                'email'   => 'budi.santoso@example.com',
                'phone'   => '081234567890',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                // 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'name'    => 'Citra Lestari',
                'email'   => 'citra.lestari@example.com',
                'phone'   => '085678901234',
                'address' => 'Jl. Asia Afrika No. 25, Bandung',
                // 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'name'    => 'Walk-in Customer', // Generic for POS if no specific customer
                'email'   => null,
                'phone'   => null,
                'address' => null,
                // 'created_at' => $now, 'updated_at' => $now
            ],
        ];

        // CustomerModel has $useTimestamps = true
        foreach ($customersData as $data) {
            $this->db->table('customers')->insert($data);
            // OR $model->save($data);
        }

        echo "Customers seeded successfully.\n";
    }
}
