<?php namespace App\Controllers;

use App\Models\PenggunaModel;
use CodeIgniter\Controller;

class Pengguna extends Controller
{
    protected $penggunaModel;

    public function __construct()
    {
        // Load model PenggunaModel
        $this->penggunaModel = new PenggunaModel();
    }

    public function index()
    {
        // Ambil semua pengguna dari database
        $data['pengguna'] = $this->penggunaModel->findAll();

        // Tampilkan view dengan data pengguna
        return view('pengguna/index', $data);
    }

    public function create()
    {
        // Cek apakah ini request POST
        if ($this->request->getMethod() === 'post') {
            // Validasi input
            $validationRules = [
                'nama' => 'required',
                'email' => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ];

            if ($this->validate($validationRules)) {
                // Simpan data ke database
                $this->penggunaModel->save([
                    'nama' => $this->request->getPost('nama'),
                    'email' => $this->request->getPost('email'),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT)
                ]);

                // Redirect ke halaman index
                return redirect()->to('/pengguna');
            } else {
                // Tampilkan error validasi
                $data['validation'] = $this->validator;
            }
        }

        // Tampilkan form create
        return view('pengguna/create', $data);
    }

    public function edit($id = null)
    {
        // Cek apakah ini request POST
        if ($this->request->getMethod() === 'post') {
            // Validasi input
            $validationRules = [
                'nama' => 'required',
                'email' => 'required|valid_email'
                // Tidak perlu validasi password jika tidak diubah
            ];

            if ($this->validate($validationRules)) {
                // Update data di database
                $dataUpdate = [
                    'nama' => $this->request->getPost('nama'),
                    'email' => $this->request->getPost('email')
                ];
                // Jika password diubah, hash dan update
                if ($this->request->getPost('password')) {
                    $dataUpdate['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
                }
                $this->penggunaModel->update($id, $dataUpdate);

                // Redirect ke halaman index
                return redirect()->to('/pengguna');
            } else {
                // Tampilkan error validasi
                $data['validation'] = $this->validator;
            }
        }

        // Ambil data pengguna berdasarkan ID dan kirim ke view
        $data['pengguna'] = $this->penggunaModel->find($id);
        return view('pengguna/edit', $data);
    }

    public function delete($id = null)
    {
        // Hapus pengguna dari database
        $this->penggunaModel->delete($id);

        // Redirect ke halaman index
        return redirect()->to('/pengguna');
    }
}
