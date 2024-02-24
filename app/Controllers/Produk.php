<?php namespace App\Controllers;

use App\Models\ProdukModel;
use CodeIgniter\Controller;

class Produk extends Controller
{
    protected $produkModel;

    public function __construct()
    {
        // Load model ProdukModel
        $this->produkModel = new ProdukModel();
    }

    public function index()
    {
        // Ambil semua produk dari database
        $data['produk'] = $this->produkModel->findAll();

        // Tampilkan view dengan data produk
        return view('produk/index', $data);
    }

    public function create()
    {
        // Cek apakah ini request POST
        if ($this->request->getMethod() === 'post') {
            // Validasi input
            $validationRules = [
                'nama' => 'required',
                'harga' => 'required|numeric',
                'stok' => 'required|numeric'
            ];

            if ($this->validate($validationRules)) {
                // Simpan data ke database
                $this->produkModel->save([
                    'nama' => $this->request->getPost('nama'),
                    'harga' => $this->request->getPost('harga'),
                    'stok' => $this->request->getPost('stok')
                ]);

                // Redirect ke halaman index
                return redirect()->to('/produk');
            } else {
                // Tampilkan error validasi
                $data['validation'] = $this->validator;
            }
        }

        // Tampilkan form create
        return view('produk/create', $data);
    }

    public function edit($id = null)
    {
        // Cek apakah ini request POST
        if ($this->request->getMethod() === 'post') {
            // Validasi input
            $validationRules = [
                'nama' => 'required',
                'harga' => 'required|numeric',
                'stok' => 'required|numeric'
            ];

            if ($this->validate($validationRules)) {
                // Update data di database
                $this->produkModel->update($id, [
                    'nama' => $this->request->getPost('nama'),
                    'harga' => $this->request->getPost('harga'),
                    'stok' => $this->request->getPost('stok')
                ]);

                // Redirect ke halaman index
                return redirect()->to('/produk');
            } else {
                // Tampilkan error validasi
                $data['validation'] = $this->validator;
            }
        }

        // Ambil data produk berdasarkan ID dan kirim ke view
        $data['produk'] = $this->produkModel->find($id);
        return view('produk/edit', $data);
    }

    public function delete($id = null)
    {
        // Hapus produk dari database
        $this->produkModel->delete($id);

        // Redirect ke halaman index
        return redirect()->to('/produk');
    }
}
