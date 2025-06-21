<?php namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\KategoriModel;
use App\Models\SupplierModel; // Added SupplierModel
use App\Controllers\BaseController;

class Produk extends BaseController
{
    protected $produkModel;
    protected $kategoriModel;
    protected $supplierModel; // Added

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->kategoriModel = new KategoriModel();
        $this->supplierModel = new SupplierModel(); // Instantiate
        helper(['form', 'url', 'filesystem', 'session']); // auth_helper is autoloaded
    }

    // Removed private checkAuth(), will use hasPermission() directly or via a new BaseController method if preferred later

    public function index()
    {
        if (!hasPermission('products_view')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view products.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // Fetch products with category and supplier names
        $data['products'] = $this->produkModel
            ->select('products.*, categories.name as category_name, suppliers.name as supplier_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->join('suppliers', 'suppliers.id = products.supplier_id', 'left') // Join suppliers
            ->findAll();

        return view('produk/index', $data);
    }

    public function create()
    {
        if (!hasPermission('products_create')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to create products.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'           => 'required|min_length[3]|max_length[255]',
                'category_id'    => 'required|is_not_unique[categories.id]',
                'supplier_id'    => 'permit_empty|is_not_unique[suppliers.id]', // New
                'description'    => 'permit_empty|max_length[2000]',
                'price'          => 'required|decimal|greater_than_equal_to[0]',
                'purchase_price' => 'permit_empty|decimal|greater_than_equal_to[0]', // New
                'stock'          => 'required|integer|greater_than_equal_to[0]',
                'sku'            => 'permit_empty|is_unique[products.sku]|max_length[100]',
                'image'          => 'permit_empty|uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpeg,image/jpg,image/png,image/gif]'
            ];

            if (!$this->validate($rules)) {
                $data['categories'] = $this->kategoriModel->findAll();
                $data['suppliers'] = $this->supplierModel->orderBy('name', 'ASC')->findAll(); // New
                $data['validation'] = $this->validator;
                return view('produk/form', $data);
            } else {
                $productData = [
                    'name'           => $this->request->getPost('name'),
                    'category_id'    => $this->request->getPost('category_id'),
                    'supplier_id'    => $this->request->getPost('supplier_id') ?: null, // New, handle empty
                    'description'    => $this->request->getPost('description'),
                    'price'          => $this->request->getPost('price'),
                    'purchase_price' => $this->request->getPost('purchase_price') ?: null, // New, handle empty
                    'stock'          => $this->request->getPost('stock'),
                    'sku'            => $this->request->getPost('sku'),
                ];

                $img = $this->request->getFile('image');
                if ($img && $img->isValid() && !$img->hasMoved()) {
                    $newName = $img->getRandomName();
                    if ($img->move(ROOTPATH . 'public/uploads/products', $newName)) {
                        $productData['image_path'] = $newName;
                    } else {
                        session()->setFlashdata('error', 'Failed to upload image: ' . $img->getErrorString().' ('.$img->getError().')');
                        // It's often better to redirect back with error if image upload fails critically
                        // For now, let's proceed without image if move fails but validation passed.
                        // Or, add specific validation for move success.
                    }
                }

                if ($this->produkModel->save($productData)) {
                    session()->setFlashdata('message', 'Product created successfully.');
                } else {
                    session()->setFlashdata('error', 'Failed to create product.');
                }
                return redirect()->to('/produk');
            }
        }

        // GET request
        $data['categories'] = $this->kategoriModel->findAll();
        $data['suppliers'] = $this->supplierModel->orderBy('name', 'ASC')->findAll(); // New
        $data['validation'] = \Config\Services::validation();
        return view('produk/form', $data);
    }

    public function edit($id = null)
    {
        if (!hasPermission('products_edit')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to edit products.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        $product = $this->produkModel->find($id);
        if (!$product) {
            session()->setFlashdata('error', 'Product not found.');
            return redirect()->to('/produk');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'           => 'required|min_length[3]|max_length[255]',
                'category_id'    => 'required|is_not_unique[categories.id]',
                'supplier_id'    => 'permit_empty|is_not_unique[suppliers.id]', // New
                'description'    => 'permit_empty|max_length[2000]',
                'price'          => 'required|decimal|greater_than_equal_to[0]',
                'purchase_price' => 'permit_empty|decimal|greater_than_equal_to[0]', // New
                'stock'          => 'required|integer|greater_than_equal_to[0]',
                'sku'            => "permit_empty|is_unique[products.sku,id,{$id}]|max_length[100]",
                'image'          => 'permit_empty|uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpeg,image/jpg,image/png,image/gif]'
            ];

            if (!$this->validate($rules)) {
                $data['product'] = $product;
                $data['categories'] = $this->kategoriModel->findAll();
                $data['suppliers'] = $this->supplierModel->orderBy('name', 'ASC')->findAll(); // New
                $data['validation'] = $this->validator;
                return view('produk/form', $data);
            } else {
                $updateData = [
                    'name'           => $this->request->getPost('name'),
                    'category_id'    => $this->request->getPost('category_id'),
                    'supplier_id'    => $this->request->getPost('supplier_id') ?: null, // New
                    'description'    => $this->request->getPost('description'),
                    'price'          => $this->request->getPost('price'),
                    'purchase_price' => $this->request->getPost('purchase_price') ?: null, // New
                    'stock'          => $this->request->getPost('stock'),
                    'sku'            => $this->request->getPost('sku'),
                ];

                $newImg = $this->request->getFile('image');
                if ($newImg && $newImg->isValid() && !$newImg->hasMoved()) {
                    // Delete old image if it exists
                    if ($product && !empty($product['image_path']) && file_exists(ROOTPATH . 'public/uploads/products/' . $product['image_path'])) {
                        unlink(ROOTPATH . 'public/uploads/products/' . $product['image_path']);
                    }
                    $newName = $newImg->getRandomName();
                     if ($newImg->move(ROOTPATH . 'public/uploads/products', $newName)) {
                        $updateData['image_path'] = $newName;
                    } else {
                        session()->setFlashdata('error', 'Failed to upload new image: ' . $newImg->getErrorString().' ('.$newImg->getError().')');
                        // Potentially redirect back or handle error more gracefully
                    }
                }

                if ($this->produkModel->update($id, $updateData)) {
                    session()->setFlashdata('message', 'Product updated successfully.');
                } else {
                    session()->setFlashdata('error', 'Failed to update product or no changes made.');
                }
                return redirect()->to('/produk');
            }
        }

        // GET request
        $data['product'] = $product;
        $data['categories'] = $this->kategoriModel->findAll();
        $data['suppliers'] = $this->supplierModel->orderBy('name', 'ASC')->findAll(); // New
        $data['validation'] = \Config\Services::validation();
        return view('produk/form', $data);
    }

    public function delete($id = null)
    {
        if (!hasPermission('products_delete')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to delete products.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // For delete, one might argue only admin should do it.
        // The 'products_delete' permission can be assigned only to admin role by RoleSeeder
        // if that's the desired behavior. Current RoleSeeder gives it to admin.

        $product = $this->produkModel->find($id);
        if ($product) {
            // Delete image file first
            if (!empty($product['image_path']) && file_exists(ROOTPATH . 'public/uploads/products/' . $product['image_path'])) {
                unlink(ROOTPATH . 'public/uploads/products/' . $product['image_path']);
            }

            if ($this->produkModel->delete($id)) {
                session()->setFlashdata('message', 'Product deleted successfully.');
            } else {
                session()->setFlashdata('error', 'Failed to delete product.');
            }
        } else {
            session()->setFlashdata('error', 'Product not found.');
        }
        return redirect()->to('/produk');
    }
}
