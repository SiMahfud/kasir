<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false; // Roles are usually not soft-deleted

    protected $allowedFields    = ['name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at'; // Not using soft deletes for roles

    // Validation - Optional
    /*
    protected $validationRules      = [
        'name' => 'required|is_unique[roles.name,id,{id}]|max_length[100]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    */
}
