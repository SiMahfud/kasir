<?php namespace App\Controllers;

use App\Models\KategoriModel;
use App\Models\ProdukModel; // For checking associated products
use App\Controllers\BaseController;

class KategoriController extends BaseController
{
    protected $kategoriModel;
    protected $helpers = ['form', 'url', 'session']; // 'auth' helper is autoloaded

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
    }

    // Removed private checkAuth() method

    public function index()
    {
        if (!hasPermission('categories_view')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view categories.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
        $data['categories'] = $this->kategoriModel->orderBy('name', 'ASC')->findAll();
        return view('kategori/index', $data);
    }

    public function create()
    {
        if (!hasPermission('categories_create')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to create categories.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
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
        if (!hasPermission('categories_edit')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to edit categories.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
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
        if (!hasPermission('categories_delete')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to delete categories.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
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
