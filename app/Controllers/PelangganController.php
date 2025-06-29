<?php namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\OrderModel; // For checking associated orders
use App\Controllers\BaseController;

class PelangganController extends BaseController
{
    protected $customerModel;
    protected $helpers = ['form', 'url', 'session']; // 'auth' helper is autoloaded

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
    }

    // Removed private checkAuth() method

    public function index()
    {
        if (!hasPermission('customers_view')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view customers.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
        $data['customers'] = $this->customerModel->orderBy('name', 'ASC')->findAll();
        return view('pelanggan/index', $data);
    }

    public function create()
    {
        if (!hasPermission('customers_create')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to create customers.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'    => 'required|min_length[3]|max_length[255]',
                'email'   => 'permit_empty|valid_email|is_unique[customers.email]|max_length[255]',
                'phone'   => 'permit_empty|max_length[50]',
                'address' => 'permit_empty|max_length[1000]'
            ];

            if (!$this->validate($rules)) {
                return view('pelanggan/form', [
                    'validation' => $this->validator
                ]);
            } else {
                $saveData = [
                    'name'    => $this->request->getPost('name'),
                    'email'   => $this->request->getPost('email'),
                    'phone'   => $this->request->getPost('phone'),
                    'address' => $this->request->getPost('address')
                ];

                if ($this->customerModel->save($saveData)) {
                    session()->setFlashdata('message', 'Customer created successfully.');
                    return redirect()->to('/pelanggan');
                } else {
                    session()->setFlashdata('error', 'Failed to create customer.');
                    return redirect()->back()->withInput()->with('validation', $this->validator);
                }
            }
        }

        // GET request
        return view('pelanggan/form', [
            'validation' => \Config\Services::validation()
        ]);
    }

    public function edit($id = null)
    {
        if (!hasPermission('customers_edit')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to edit customers.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            session()->setFlashdata('error', 'Customer not found.');
            return redirect()->to('/pelanggan');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'    => 'required|min_length[3]|max_length[255]',
                'email'   => "permit_empty|valid_email|is_unique[customers.email,id,{$id}]|max_length[255]",
                'phone'   => 'permit_empty|max_length[50]',
                'address' => 'permit_empty|max_length[1000]'
            ];

            if (!$this->validate($rules)) {
                return view('pelanggan/form', [
                    'customer'   => $customer,
                    'validation' => $this->validator
                ]);
            } else {
                $updateData = [
                    'name'    => $this->request->getPost('name'),
                    'email'   => $this->request->getPost('email'),
                    'phone'   => $this->request->getPost('phone'),
                    'address' => $this->request->getPost('address')
                ];

                if ($this->customerModel->update($id, $updateData)) {
                    session()->setFlashdata('message', 'Customer updated successfully.');
                    return redirect()->to('/pelanggan');
                } else {
                    session()->setFlashdata('error', 'Failed to update customer or no changes made.');
                    return redirect()->back()->withInput()->with('validation', $this->validator);
                }
            }
        }

        // GET request
        return view('pelanggan/form', [
            'customer'   => $customer,
            'validation' => \Config\Services::validation()
        ]);
    }

    public function delete($id = null)
    {
        if (!hasPermission('customers_delete')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to delete customers.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
        $customer = $this->customerModel->find($id);
        if (!$customer) {
            session()->setFlashdata('error', 'Customer not found.');
            return redirect()->to('/pelanggan');
        }

        // Check for associated orders
        $orderModel = new OrderModel();
        $associatedOrders = $orderModel->where('customer_id', $id)->countAllResults();

        if ($associatedOrders > 0) {
            session()->setFlashdata('error', 'Cannot delete customer. They have ' . $associatedOrders . ' associated orders. Consider anonymizing or archiving customer data instead, or reassign orders if applicable.');
            return redirect()->to('/pelanggan');
        }

        // If no associated orders, proceed with deletion
        if ($this->customerModel->delete($id)) {
            session()->setFlashdata('message', 'Customer deleted successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to delete customer.');
        }
        return redirect()->to('/pelanggan');
    }
}
