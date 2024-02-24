<?php namespace App\Controllers;

use App\Models\LaporanModel;
use CodeIgniter\Controller;

class Laporan extends Controller
{
    protected $laporanModel;

    public function __construct()
    {
        // Load model LaporanModel
        $this->laporanModel = new LaporanModel();
    }

    public function penjualan()
    {
        // Ambil parameter tanggal dari request
        $tanggal_awal = $this->request->getVar('tanggal_awal');
        $tanggal_akhir = $this->request->getVar('tanggal_akhir');

        // Ambil data laporan penjualan dari model
        $data['laporan_penjualan'] = $this->laporanModel->getLaporanPenjualan($tanggal_awal, $tanggal_akhir);

        // Tampilkan view dengan data laporan penjualan
        return view('laporan/penjualan', $data);
    }

    public function stok()
    {
        // Ambil data laporan stok dari model
        $data['laporan_stok'] = $this->laporanModel->getLaporanStok();

        // Tampilkan view dengan data laporan stok
        return view('laporan/stok', $data);
    }
}
