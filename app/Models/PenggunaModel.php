<?php namespace App\Models;

use CodeIgniter\Model;

class PenggunaModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Assuming no soft deletes for now
    protected $allowedFields    = ['name', 'email', 'password', 'role'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime'; // or 'int' if using UNIX timestamps
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Uncomment if using soft deletes

    // You can add validation rules here if needed
    // protected $validationRules    = [];
    // protected $validationMessages = [];
    // protected $skipValidation     = false;
}
