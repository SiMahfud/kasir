<?php namespace App\Controllers;

use App\Models\ProdukModel;
use App\Models\KategoriModel; // Added KategoriModel
use App\Controllers\BaseController;

class Produk extends BaseController
{
    protected $produkModel;
    protected $kategoriModel;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
        $this->kategoriModel = new KategoriModel();
        helper(['form', 'url', 'filesystem', 'session']); // Load helpers, added session
    }

    private function checkAuth()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to manage products.');
            return redirect()->to('/login');
        }
        $role = session()->get('user_role');
        if ($role !== 'admin' && $role !== 'staff') {
            session()->setFlashdata('error', 'Access Denied. You do not have permission for this action.');
            return redirect()->to('/dashboard'); // Or a general access denied page
        }
        return null; // No redirect, auth passed
    }

    public function index()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        // Fetch products with category names
        $data['products'] = $this->produkModel
            ->select('products.*, categories.name as category_name')
            ->join('categories', 'categories.id = products.category_id', 'left')
            ->findAll();

        return view('produk/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'        => 'required|min_length[3]|max_length[255]',
                'category_id' => 'required|is_not_unique[categories.id]',
                'description' => 'permit_empty|max_length[2000]',
                'price'       => 'required|decimal|greater_than_equal_to[0]',
                'stock'       => 'required|integer|greater_than_equal_to[0]',
                'sku'         => 'permit_empty|is_unique[products.sku]|max_length[100]',
                'image'       => 'permit_empty|uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpeg,image/jpg,image/png,image/gif]'
            ];

            if (!$this->validate($rules)) {
                $data['categories'] = $this->kategoriModel->findAll();
                $data['validation'] = $this->validator;
                return view('produk/form', $data);
            } else {
                $productData = [
                    'name'        => $this->request->getPost('name'),
                    'category_id' => $this->request->getPost('category_id'),
                    'description' => $this->request->getPost('description'),
                    'price'       => $this->request->getPost('price'),
                    'stock'       => $this->request->getPost('stock'),
                    'sku'         => $this->request->getPost('sku'),
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
        $data['validation'] = \Config\Services::validation();
        return view('produk/form', $data);
    }

    public function edit($id = null)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $product = $this->produkModel->find($id);
        if (!$product) {
            session()->setFlashdata('error', 'Product not found.');
            return redirect()->to('/produk');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'        => 'required|min_length[3]|max_length[255]',
                'category_id' => 'required|is_not_unique[categories.id]',
                'description' => 'permit_empty|max_length[2000]',
                'price'       => 'required|decimal|greater_than_equal_to[0]',
                'stock'       => 'required|integer|greater_than_equal_to[0]',
                'sku'         => "permit_empty|is_unique[products.sku,id,{$id}]|max_length[100]",
                'image'       => 'permit_empty|uploaded[image]|max_size[image,2048]|is_image[image]|mime_in[image,image/jpeg,image/jpg,image/png,image/gif]'
            ];
             // Note: 'permit_empty' for image means if 'image' field is not sent or empty, it passes.
             // If it IS sent, then 'uploaded' and other rules apply.

            if (!$this->validate($rules)) {
                $data['product'] = $product; // existing product data
                $data['categories'] = $this->kategoriModel->findAll();
                $data['validation'] = $this->validator;
                return view('produk/form', $data);
            } else {
                $updateData = [
                    'name'        => $this->request->getPost('name'),
                    'category_id' => $this->request->getPost('category_id'),
                    'description' => $this->request->getPost('description'),
                    'price'       => $this->request->getPost('price'),
                    'stock'       => $this->request->getPost('stock'),
                    'sku'         => $this->request->getPost('sku'),
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
        $data['validation'] = \Config\Services::validation();
        return view('produk/form', $data);
    }

    public function delete($id = null)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        // For delete, one might argue only admin should do it.
        // For now, sticking to admin/staff as per initial simplified plan.
        // if (session()->get('user_role') !== 'admin') {
        //     session()->setFlashdata('error', 'Access Denied. Only admins can delete products.');
        //     return redirect()->to('/produk');
        // }

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
