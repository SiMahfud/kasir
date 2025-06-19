<?php namespace App\Controllers;

use App\Models\PenggunaModel;
use App\Controllers\BaseController; // Correct base controller

class Pengguna extends BaseController
{
    protected $penggunaModel;
    // protected $session; // Session is typically available via $this->session or service('session')
    protected $helpers = ['form', 'url', 'session']; // Added session helper

    public function __construct()
    {
        $this->penggunaModel = new PenggunaModel();
        // $this->session = \Config\Services::session(); // Or use service('session') directly
        // Ensure helpers are loaded if not in BaseController or auto-loaded
        // helper($this->helpers); // Already declared in $helpers property
    }

    // --- AUTHENTICATION METHODS ---
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }
        return view('pengguna/login', ['validation' => \Config\Services::validation()]);
    }

    public function authenticate()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/login')->with('error', 'Invalid request method.');
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/login')->withInput()->with('validation', $this->validator);
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->penggunaModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            $sessionData = [
                'isLoggedIn' => true,
                'user_id'    => $user['id'],
                'user_name'  => $user['name'],
                'user_role'  => $user['role']
            ];
            session()->set($sessionData);
            return redirect()->to('/dashboard')->with('message', 'Login successful!');
        } else {
            return redirect()->to('/login')->withInput()->with('error', 'Invalid email or password.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('message', 'You have been logged out.');
    }

    // --- CRUD METHODS WITH AUTH CHECKS ---
    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'admin') {
            session()->setFlashdata('error', 'Access denied. You do not have permission to manage users.');
            return redirect()->to(session()->get('isLoggedIn') ? '/dashboard' : '/login');
        }
        $data['users'] = $this->penggunaModel->findAll();
        return view('pengguna/index', $data);
    }

    public function create()
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'admin') {
            session()->setFlashdata('error', 'Access denied. You do not have permission to manage users.');
            return redirect()->to(session()->get('isLoggedIn') ? '/dashboard' : '/login');
        }

        // Handle POST request for storing new user
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'             => 'required|min_length[3]|max_length[255]',
                'email'            => 'required|valid_email|is_unique[users.email]',
                'password'         => 'required|min_length[6]',
                'password_confirm' => 'required_with[password]|matches[password]',
                'role'             => 'required|in_list[admin,staff]'
            ];

            if (!$this->validate($rules)) {
                return view('pengguna/form', [
                    'validation' => $this->validator
                ]);
            } else {
                $userData = [
                    'name'     => $this->request->getPost('name'),
                    'email'    => $this->request->getPost('email'),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role'     => $this->request->getPost('role')
                ];

                if ($this->penggunaModel->save($userData)) {
                    session()->setFlashdata('message', 'User created successfully.');
                } else {
                    session()->setFlashdata('error', 'Failed to create user.');
                }
                return redirect()->to('/pengguna');
            }
        }

        // Handle GET request for displaying the create form
        return view('pengguna/form', [
            'validation' => \Config\Services::validation()
        ]);
    }

    public function edit($id = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'admin') {
            session()->setFlashdata('error', 'Access denied. You do not have permission to manage users.');
            return redirect()->to(session()->get('isLoggedIn') ? '/dashboard' : '/login');
        }

        $user = $this->penggunaModel->find($id);
        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('/pengguna');
        }

        // Handle POST request for updating existing user
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'             => 'required|min_length[3]|max_length[255]',
                'email'            => "required|valid_email|is_unique[users.email,id,{$id}]",
                'password'         => 'permit_empty|min_length[6]', // Optional password change
                'password_confirm' => 'required_with[password]|matches[password]',
                'role'             => 'required|in_list[admin,staff]'
            ];

            if (!$this->validate($rules)) {
                return view('pengguna/form', [
                    'user'       => $user,
                    'validation' => $this->validator
                ]);
            } else {
                $updateData = [
                    'name'  => $this->request->getPost('name'),
                    'email' => $this->request->getPost('email'),
                    'role'  => $this->request->getPost('role')
                ];

                // If password is provided, hash and add to updateData
                $password = $this->request->getPost('password');
                if (!empty($password)) {
                    $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
                }

                if ($this->penggunaModel->update($id, $updateData)) {
                    session()->setFlashdata('message', 'User updated successfully.');
                } else {
                     session()->setFlashdata('error', 'Failed to update user or no changes made.');
                }
                return redirect()->to('/pengguna');
            }
        }

        // Handle GET request for displaying the edit form
        return view('pengguna/form', [
            'user'       => $user,
            'validation' => \Config\Services::validation()
        ]);
    }

    public function delete($id = null)
    {
        if (!session()->get('isLoggedIn') || session()->get('user_role') !== 'admin') {
            session()->setFlashdata('error', 'Access denied. You do not have permission to manage users.');
            return redirect()->to(session()->get('isLoggedIn') ? '/dashboard' : '/login');
        }

        $user = $this->penggunaModel->find($id);
        if ($user) {
            // Prevent deleting oneself - optional rule
            if ($user['id'] === session()->get('user_id')) {
                session()->setFlashdata('error', 'You cannot delete your own account.');
                return redirect()->to('/pengguna');
            }
            if ($this->penggunaModel->delete($id)) {
                session()->setFlashdata('message', 'User deleted successfully.');
            } else {
                session()->setFlashdata('error', 'Failed to delete user.');
            }
        } else {
            session()->setFlashdata('error', 'User not found.');
        }
        return redirect()->to('/pengguna');
    }
}
