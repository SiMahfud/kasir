<?php namespace App\Models;

use CodeIgniter\Model;

class ProdukModel extends Model
{
    protected $table = 'produk'; // Nama tabel yang digunakan
    protected $primaryKey = 'id'; // Kunci utama tabel
    protected $useAutoIncrement = true; // Menggunakan auto increment
    protected $returnType = 'array'; // Tipe data yang dikembalikan
    protected $allowedFields = ['nama', 'kategori', 'harga', 'stok', 'deskripsi']; // Field yang diizinkan untuk diisi

    // Metode untuk mengambil semua produk
    public function getAllProduk()
    {
        return $this->findAll();
    }

    // Metode untuk mengambil produk berdasarkan ID
    public function getProdukById($id)
    {
        return $this->asArray()
                    ->where(['id' => $id])
                    ->first();
    }

    // Metode untuk menyimpan produk baru
    public function saveProduk($data)
    {
        return $this->save($data);
    }

    // Metode untuk memperbarui produk
    public function updateProduk($id, $data)
    {
        return $this->update($id, $data);
    }

    // Metode untuk menghapus produk
    public function deleteProduk($id)
    {
        return $this->delete($id);
    }
}
