<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSupplierAndPurchasePriceToProducts extends Migration
{
    public function up()
    {
        $fields = [
            'supplier_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'category_id', // Optional: specify position
            ],
            'purchase_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => 0.00,
                'after'      => 'price', // Optional: specify position
            ],
        ];
        $this->forge->addColumn('products', $fields);

        // Add foreign key constraint using SQL directly for more control if needed,
        // or use forge's addForeignKey if it reliably works with existing tables.
        // Note: addForeignKey might have issues if table already has data that violates constraint temporarily.
        // It's generally safer to add constraints when table is empty or data is clean.
        // $this->db->query('ALTER TABLE products ADD CONSTRAINT FK_products_supplier_id FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL ON UPDATE CASCADE');
        // Using forge for consistency:
        $this->forge->addForeignKey('supplier_id', 'suppliers', 'id', 'NO ACTION', 'SET NULL');
        // 'NO ACTION' for ON UPDATE, 'SET NULL' for ON DELETE. Adjust as per requirements.
        // 'CASCADE' for ON UPDATE could be risky if supplier IDs change.
        // 'CASCADE' for ON DELETE would delete products if supplier is deleted - usually not desired.
        // 'RESTRICT' or 'NO ACTION' are often safer defaults for ON DELETE unless specific cascading logic is intended.
        // Given the prompt's suggestion for SET NULL, that's what I've used for ON DELETE.
    }

    public function down()
    {
        // It's good practice to remove foreign keys by their constraint name if known.
        // If not known, CodeIgniter's dropForeignKey might work by column name.
        // $this->forge->dropForeignKey('products', 'FK_products_supplier_id'); // If named constraint
        $this->forge->dropForeignKey('products', 'products_supplier_id_foreign'); // CI default naming pattern

        $this->forge->dropColumn('products', 'supplier_id');
        $this->forge->dropColumn('products', 'purchase_price');
    }
}
