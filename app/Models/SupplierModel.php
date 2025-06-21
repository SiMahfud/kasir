<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table            = 'suppliers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array'; // Or 'object'
    protected $useSoftDeletes   = true; // Set to true if 'deleted_at' column is used for soft deletes

    protected $allowedFields    = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'notes'
        // 'deleted_at' should not be in allowedFields if $useSoftDeletes is true
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // 'datetime', 'date', or 'int'
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at'; // For soft deletes

    // Validation - Optional: Add validation rules within the model
    /*
    protected $validationRules      = [
        'name'  => 'required|min_length[3]|max_length[255]',
        'email' => 'permit_empty|valid_email|is_unique[suppliers.email,id,{id}]|max_length[255]',
        'phone' => 'permit_empty|max_length[50]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    */

    // Callbacks - Optional: Add callbacks for before/after events
    /*
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
    */
}
