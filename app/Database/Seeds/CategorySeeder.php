<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\KategoriModel;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $model = new KategoriModel();
        // Timestamps will be handled by the model.
        // $now = date('Y-m-d H:i:s');

        $categoriesData = [
            [
                'name' => 'Electronics',
                'description' => 'Gadgets, computers, and consumer electronics.',
                // 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'name' => 'Books',
                'description' => 'Fiction, non-fiction, educational books.',
                // 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'name' => 'Clothing',
                'description' => 'Apparel for men, women, and children.',
                // 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'name' => 'Home Goods',
                'description' => 'Furniture, decor, and kitchen essentials.',
                // 'created_at' => $now, 'updated_at' => $now
            ],
            [
                'name' => 'Groceries',
                'description' => 'Food items, beverages, and household supplies.',
                // 'created_at' => $now, 'updated_at' => $now
            ],
        ];

        // KategoriModel has $useTimestamps = true, so it will handle timestamps.
        // $this->db->table('categories')->insertBatch($categoriesData);
        // Using model's insertBatch is also an option if it's configured to allow it without issues.
        foreach ($categoriesData as $data) {
           $this->db->table('categories')->insert($data);
           // OR $model->save($data);
        }

        echo "Categories seeded successfully.\n";
    }
}
