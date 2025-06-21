<?php namespace App\Models;

use CodeIgniter\Model;

class PenggunaModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['name', 'email', 'password', 'role_id']; // Updated: role -> role_id

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

    public function getUserWithRoleAndPermissions($userId)
    {
        $builder = $this->db->table('users');
        $builder->select('users.*, roles.name as role_name, GROUP_CONCAT(DISTINCT permissions.permission_key) as permissions_list');
        $builder->join('roles', 'users.role_id = roles.id', 'left');
        $builder->join('role_permissions', 'roles.id = role_permissions.role_id', 'left');
        $builder->join('permissions', 'role_permissions.permission_id = permissions.id', 'left');
        $builder->where('users.id', $userId);
        $builder->groupBy(['users.id', 'roles.id']); // Group by users.id and roles.id to ensure one row per user-role combination

        $query = $builder->get();
        return $query->getRowArray(); // Return a single row or null
    }
}
