<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\ProdukModel;
use App\Models\KategoriModel;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $produkModel = new ProdukModel();
        $kategoriModel = new KategoriModel();
        // Timestamps will be handled by the model.
        // $now = date('Y-m-d H:i:s');

        // Fetch category IDs - this relies on CategorySeeder having run
        $electronics = $kategoriModel->where('name', 'Electronics')->first();
        $books = $kategoriModel->where('name', 'Books')->first();
        $clothing = $kategoriModel->where('name', 'Clothing')->first();
        $homeGoods = $kategoriModel->where('name', 'Home Goods')->first();
        // $groceries = $kategoriModel->where('name', 'Groceries')->first();


        $productsData = [
            [
                'category_id' => $electronics['id'] ?? null,
                'name' => 'Laptop Pro 15"',
                'description' => 'High-performance laptop for professionals.',
                'price' => 25000000.00,
                'stock' => 50,
                'sku' => 'LP15PRO',
                'image_path' => null, // 'laptop_pro.jpg'
            ],
            [
                'category_id' => $electronics['id'] ?? null,
                'name' => 'Smartphone X',
                'description' => 'Latest generation smartphone with AI features.',
                'price' => 12000000.00,
                'stock' => 150,
                'sku' => 'SPX001',
            ],
            [
                'category_id' => $books['id'] ?? null,
                'name' => 'The Art of Code',
                'description' => 'A deep dive into software development principles.',
                'price' => 350000.00,
                'stock' => 200,
                'sku' => 'BKARTCODE',
            ],
            [
                'category_id' => $books['id'] ?? null,
                'name' => 'Cosmic Journeys',
                'description' => 'Exploring the wonders of the universe.',
                'price' => 275000.00,
                'stock' => 120,
            ],
            [
                'category_id' => $clothing['id'] ?? null,
                'name' => 'Men\'s Classic Tee',
                'description' => 'Comfortable cotton t-shirt for everyday wear.',
                'price' => 150000.00,
                'stock' => 300,
                'sku' => 'MCTEE001',
            ],
            [
                'category_id' => $clothing['id'] ?? null,
                'name' => 'Women\'s Denim Jacket',
                'description' => 'Stylish and durable denim jacket.',
                'price' => 750000.00,
                'stock' => 80,
            ],
            [
                'category_id' => $homeGoods['id'] ?? null,
                'name' => 'Ergonomic Office Chair',
                'description' => 'Supportive chair for long working hours.',
                'price' => 3200000.00,
                'stock' => 60,
                'sku' => 'ERGOCHR01',
            ],
             [
                'category_id' => $electronics['id'] ?? null,
                'name' => 'Wireless Headphones',
                'description' => 'Noise-cancelling over-ear wireless headphones.',
                'price' => 1800000.00,
                'stock' => 75,
                'sku' => 'HDPHN00W',
            ],
        ];

        // ProdukModel has $useTimestamps = true
        foreach ($productsData as $data) {
            if ($data['category_id'] === null) {
                echo "Warning: Category for product '{$data['name']}' not found. Skipping this product or inserting without category.\n";
                // Decide if you want to skip or insert with category_id = NULL (if DB allows)
                // For this example, we'll insert with NULL if category not found.
                // The DB schema for products.category_id allows NULL.
            }
             $this->db->table('products')->insert($data);
            // OR $produkModel->save($data);
        }

        echo "Products seeded successfully.\n";
    }
}
