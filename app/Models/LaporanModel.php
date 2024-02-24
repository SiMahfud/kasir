<?php namespace App\Models;

use CodeIgniter\Model;

class LaporanModel extends Model
{
    protected $table = 'transaksi'; // Asumsi laporan berdasarkan tabel transaksi

    public function getLaporanPenjualan($tanggal_awal, $tanggal_akhir)
    {
        return $this->asArray()
                    ->where('tanggal_transaksi >=', $tanggal_awal)
                    ->where('tanggal_transaksi <=', $tanggal_akhir)
                    ->findAll();
    }

    public function getLaporanStok()
    {
        // Asumsi tabel produk memiliki field 'stok'
        $db = \Config\Database::connect();
        $query = $db->query("SELECT nama, stok FROM produk WHERE stok < 10"); // Contoh query sederhana
        return $query->getResultArray();
    }
}