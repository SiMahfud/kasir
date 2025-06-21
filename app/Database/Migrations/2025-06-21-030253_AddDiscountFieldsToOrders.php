<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountFieldsToOrders extends Migration
{
    public function up()
    {
        $fields = [
            'subtotal_before_discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'order_date', // Position after order_date or similar
            ],
            'discount_type' => [
                'type'       => 'ENUM',
                'constraint' => ['percentage', 'fixed_amount'],
                'null'       => true,
                'after'      => 'subtotal_before_discount',
            ],
            'discount_value' => [ // Stores the raw value entered by user (e.g., 10 for 10% or 10000 for Rp 10.000)
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => 0.00,
                'after'      => 'discount_type',
            ],
            'calculated_discount_amount' => [ // Stores the actual monetary value of the discount
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => 0.00,
                'after'      => 'discount_value',
            ],
            'tax_amount' => [ // Stores the calculated tax amount
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'default'    => 0.00,
                'after'      => 'calculated_discount_amount',
            ],
            // 'total_amount' column should already exist and will store the final amount
        ];
        $this->forge->addColumn('orders', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('orders', 'subtotal_before_discount');
        $this->forge->dropColumn('orders', 'discount_type');
        $this->forge->dropColumn('orders', 'discount_value');
        $this->forge->dropColumn('orders', 'calculated_discount_amount');
        $this->forge->dropColumn('orders', 'tax_amount');
    }
}
