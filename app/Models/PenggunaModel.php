<?php namespace App\Models;

use CodeIgniter\Model;

class PenggunaModel extends Model
{
    protected $table = 'pengguna';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['nama', 'email', 'password', 'level'];

    public function getPengguna()
    {
        return $this->findAll();
    }

    public function getPenggunaById($id)
    {
        return $this->asArray()
                    ->where(['id' => $id])
                    ->first();
    }

    public function savePengguna($data)
    {
        return $this->save($data);
    }

    public function updatePengguna($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deletePengguna($id)
    {
        return $this->delete($id);
    }
}
