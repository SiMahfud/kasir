<?php namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Assuming products are hard deleted or not deleted via model often
    protected $allowedFields    = [
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'sku',
        'image_path',
        'supplier_id', // Added
        'purchase_price' // Added
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // Validation
    // protected $validationRules      = [];
    // protected $validationMessages   = [];
    // protected $skipValidation       = false;
    // protected $cleanValidationRules = true;

    // Callbacks
    // protected $allowCallbacks = true;
    // protected $beforeInsert   = [];
    // protected $afterInsert    = [];
    // protected $beforeUpdate   = [];
    // protected $afterUpdate    = [];
    // protected $beforeFind     = [];
    // protected $afterFind      = [];
    // protected $beforeDelete   = [];
    // protected $afterDelete    = [];

    public function incrementStock($productId, $quantity)
    {
        if (!is_numeric($productId) || !is_numeric($quantity) || $quantity <= 0) {
            return false;
        }
        // Ensure quantity is treated as integer for stock operations
        $quantity = (int)$quantity;
        return $this->set('stock', "stock + {$quantity}", false)
                    ->where('id', $productId)
                    ->update();
    }
}
