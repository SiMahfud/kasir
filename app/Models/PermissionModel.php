<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['permission_key', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // Validation - Optional
    /*
    protected $validationRules      = [
        'permission_key' => 'required|is_unique[permissions.permission_key,id,{id}]|max_length[100]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    */
}
