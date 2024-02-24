<?php namespace App\Controllers;

use App\Models\TransaksiModel;
use CodeIgniter\Controller;

class Transaksi extends Controller
{
    protected $transaksiModel;

    public function __construct()
    {
        // Load model TransaksiModel
        $this->transaksiModel = new TransaksiModel();
    }

    public function index()
    {
        // Ambil semua transaksi dari database
        $data['transaksi'] = $this->transaksiModel->findAll();

        // Tampilkan view dengan data transaksi
        return view('transaksi/index', $data);
    }

    public function create()
    {
        // Cek apakah ini request POST
        if ($this->request->getMethod() === 'post') {
            // Validasi input
            $validationRules = [
                'produk_id' => 'required',
                'jumlah' => 'required|numeric',
                'total_harga' => 'required|numeric'
            ];

            if ($this->validate($validationRules)) {
                // Simpan data ke database
                $this->transaksiModel->save([
                    'produk_id' => $this->request->getPost('produk_id'),
                    'jumlah' => $this->request->getPost('jumlah'),
                    'total_harga' => $this->request->getPost('total_harga')
                ]);

                // Redirect ke halaman index
                return redirect()->to('/transaksi');
            } else {
                // Tampilkan error validasi
                $data['validation'] = $this->validator;
            }
        }

        // Tampilkan form create
        return view('transaksi/create', $data);
    }

    public function edit($id = null)
    {
        // Cek apakah ini request POST
        if ($this->request->getMethod() === 'post') {
            // Validasi input
            $validationRules = [
                'produk_id' => 'required',
                'jumlah' => 'required|numeric',
                'total_harga' => 'required|numeric'
            ];

            if ($this->validate($validationRules)) {
                // Update data di database
                $this->transaksiModel->update($id, [
                    'produk_id' => $this->request->getPost('produk_id'),
                    'jumlah' => $this->request->getPost('jumlah'),
                    'total_harga' => $this->request->getPost('total_harga')
                ]);

                // Redirect ke halaman index
                return redirect()->to('/transaksi');
            } else {
                // Tampilkan error validasi
                $data['validation'] = $this->validator;
            }
        }

        // Ambil data transaksi berdasarkan ID dan kirim ke view
        $data['transaksi'] = $this->transaksiModel->find($id);
        return view('transaksi/edit', $data);
    }

    public function delete($id = null)
    {
        // Hapus transaksi dari database
        $this->transaksiModel->delete($id);

        // Redirect ke halaman index
        return redirect()->to('/transaksi');
    }
}
