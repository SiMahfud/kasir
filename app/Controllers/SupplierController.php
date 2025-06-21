<?php

namespace App\Controllers;

use App\Models\SupplierModel;
// use App\Models\ProdukModel; // For checking associated products if that feature is added
use App\Controllers\BaseController;

class SupplierController extends BaseController
{
    protected $supplierModel;
    protected $helpers = ['form', 'url', 'session'];

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
    }

    private function checkAuth()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to('/login');
        }
        $role = session()->get('user_role');
        if (!in_array($role, ['admin', 'staff'])) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission for this action.');
            return redirect()->to('/dashboard');
        }
        return null; // No redirect, auth passed
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $data['suppliers'] = $this->supplierModel->orderBy('name', 'ASC')->findAll();
        return view('supplier/index', $data);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        // Not typically used if edit serves as the view for a single resource form
        // Could be implemented to show a read-only detail page
        if ($redirect = $this->checkAuth()) return $redirect;
        $supplier = $this->supplierModel->find($id);
        if (!$supplier) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('/suppliers');
        }
        // For now, redirect to edit or implement a supplier/show view
        return redirect()->to(site_url('suppliers/edit/' . $id));
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $data['validation'] = \Config\Services::validation();
        return view('supplier/form', $data);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $rules = [
            'name'  => 'required|min_length[3]|max_length[255]',
            'email' => 'permit_empty|valid_email|is_unique[suppliers.email]|max_length[255]',
            'phone' => 'permit_empty|max_length[50]',
            'address' => 'permit_empty|max_length[1000]',
            'contact_person' => 'permit_empty|max_length[255]',
            'notes' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return view('supplier/form', ['validation' => $this->validator]);
        }

        $saveData = [
            'name'           => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'email'          => $this->request->getPost('email'),
            'phone'          => $this->request->getPost('phone'),
            'address'        => $this->request->getPost('address'),
            'notes'          => $this->request->getPost('notes'),
        ];

        if ($this->supplierModel->save($saveData)) {
            session()->setFlashdata('message', 'Supplier created successfully.');
            return redirect()->to('/suppliers');
        } else {
            session()->setFlashdata('error', 'Failed to create supplier.');
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $supplier = $this->supplierModel->find($id);
        if (!$supplier) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('/suppliers');
        }

        $data['supplier'] = $supplier;
        $data['validation'] = \Config\Services::validation();
        return view('supplier/form', $data);
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $supplier = $this->supplierModel->find($id);
        if (!$supplier) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('/suppliers');
        }

        $rules = [
            'name'  => 'required|min_length[3]|max_length[255]',
            'email' => "permit_empty|valid_email|is_unique[suppliers.email,id,{$id}]|max_length[255]",
            'phone' => 'permit_empty|max_length[50]',
            'address' => 'permit_empty|max_length[1000]',
            'contact_person' => 'permit_empty|max_length[255]',
            'notes' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            $data['supplier'] = $supplier; // Pass existing data back
            $data['validation'] = $this->validator;
            return view('supplier/form', $data);
        }

        $updateData = [
            'name'           => $this->request->getPost('name'),
            'contact_person' => $this->request->getPost('contact_person'),
            'email'          => $this->request->getPost('email'),
            'phone'          => $this->request->getPost('phone'),
            'address'        => $this->request->getPost('address'),
            'notes'          => $this->request->getPost('notes'),
        ];

        if ($this->supplierModel->update($id, $updateData)) {
            session()->setFlashdata('message', 'Supplier updated successfully.');
            return redirect()->to('/suppliers');
        } else {
            session()->setFlashdata('error', 'Failed to update supplier or no changes made.');
            // Pass back the original supplier data on failed update attempt if needed
            $data['supplier'] = $supplier; // Or $this->request->getPost() to repopulate form with attempted changes
            $data['validation'] = $this->validator; // This might be empty if validation passed but DB update failed
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $supplier = $this->supplierModel->find($id);
        if (!$supplier) {
            session()->setFlashdata('error', 'Supplier not found.');
            return redirect()->to('/suppliers');
        }

        // TODO: Check for associated products if supplier_id is added to products table in the future.
        // Example:
        // $produkModel = new \App\Models\ProdukModel();
        // $associatedProducts = $produkModel->where('supplier_id', $id)->countAllResults();
        // if ($associatedProducts > 0) {
        //     session()->setFlashdata('error', 'Cannot delete supplier. It has associated products.');
        //     return redirect()->to('/suppliers');
        // }

        if ($this->supplierModel->delete($id)) { // This will be a soft delete if $useSoftDeletes is true in model
            session()->setFlashdata('message', 'Supplier deleted successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to delete supplier.');
        }
        return redirect()->to('/suppliers');
    }
}
