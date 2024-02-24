<?php namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['produk_id', 'jumlah', 'total_harga', 'tanggal_transaksi'];

    public function getTransaksi()
    {
        return $this->findAll();
    }

    public function getTransaksiById($id)
    {
        return $this->asArray()
                    ->where(['id' => $id])
                    ->first();
    }

    public function saveTransaksi($data)
    {
        return $this->save($data);
    }

    public function updateTransaksi($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteTransaksi($id)
    {
        return $this->delete($id);
    }
}
