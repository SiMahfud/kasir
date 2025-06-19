<?php namespace App\Controllers;

use App\Models\KategoriModel;
use App\Models\ProdukModel; // For checking associated products
use App\Controllers\BaseController;

class KategoriController extends BaseController
{
    protected $kategoriModel;
    protected $helpers = ['form', 'url', 'session']; // Added session helper

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
    }

    private function checkAuth()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to manage categories.');
            return redirect()->to('/login');
        }
        $role = session()->get('user_role');
        if ($role !== 'admin' && $role !== 'staff') {
            session()->setFlashdata('error', 'Access Denied. You do not have permission for this action.');
            return redirect()->to('/dashboard');
        }
        return null; // No redirect, auth passed
    }

    public function index()
    {
        if ($redirect = $this->checkAuth()) return $redirect;
        $data['categories'] = $this->kategoriModel->orderBy('name', 'ASC')->findAll();
        return view('kategori/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->checkAuth()) return $redirect;
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'        => 'required|min_length[2]|max_length[100]|is_unique[categories.name]',
                'description' => 'permit_empty|max_length[1000]'
            ];

            if (!$this->validate($rules)) {
                return view('kategori/form', [
                    'validation' => $this->validator
                ]);
            } else {
                $saveData = [
                    'name'        => $this->request->getPost('name'),
                    'description' => $this->request->getPost('description')
                ];

                if ($this->kategoriModel->save($saveData)) {
                    session()->setFlashdata('message', 'Category created successfully.');
                    return redirect()->to('/kategori');
                } else {
                    session()->setFlashdata('error', 'Failed to create category.');
                    return redirect()->back()->withInput()->with('validation', $this->validator);
                }
            }
        }

        // GET request
        return view('kategori/form', [
            'validation' => \Config\Services::validation()
        ]);
    }

    public function edit($id = null)
    {
        if ($redirect = $this->checkAuth()) return $redirect;
        $category = $this->kategoriModel->find($id);
        if (!$category) {
            session()->setFlashdata('error', 'Category not found.');
            return redirect()->to('/kategori');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'        => "required|min_length[2]|max_length[100]|is_unique[categories.name,id,{$id}]",
                'description' => 'permit_empty|max_length[1000]'
            ];

            if (!$this->validate($rules)) {
                return view('kategori/form', [
                    'category'   => $category,
                    'validation' => $this->validator
                ]);
            } else {
                $updateData = [
                    'name'        => $this->request->getPost('name'),
                    'description' => $this->request->getPost('description')
                ];

                if ($this->kategoriModel->update($id, $updateData)) {
                    session()->setFlashdata('message', 'Category updated successfully.');
                    return redirect()->to('/kategori');
                } else {
                    session()->setFlashdata('error', 'Failed to update category or no changes made.');
                     // Pass back the original category data on failed update attempt if needed, along with validation
                    return redirect()->back()->withInput()->with('validation', $this->validator);
                }
            }
        }

        // GET request
        return view('kategori/form', [
            'category'   => $category,
            'validation' => \Config\Services::validation()
        ]);
    }

    public function delete($id = null)
    {
        if ($redirect = $this->checkAuth()) return $redirect;
        $category = $this->kategoriModel->find($id);
        if (!$category) {
            session()->setFlashdata('error', 'Category not found.');
            return redirect()->to('/kategori');
        }

        // Check for associated products
        $produkModel = new ProdukModel();
        $associatedProducts = $produkModel->where('category_id', $id)->countAllResults();

        if ($associatedProducts > 0) {
            session()->setFlashdata('error', 'Cannot delete category. It has ' . $associatedProducts . ' associated products. Please reassign or delete them first.');
            return redirect()->to('/kategori');
        }

        // If no associated products, proceed with deletion
        if ($this->kategoriModel->delete($id)) {
            session()->setFlashdata('message', 'Category deleted successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to delete category.');
        }
        return redirect()->to('/kategori');
    }
}
