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

        // Standard user fetch first to verify password against stored hash
        $userAuthCheck = $this->penggunaModel->where('email', $email)->first();

        if ($userAuthCheck && password_verify($password, $userAuthCheck['password'])) {
            // Password is correct, now fetch user with role and permissions
            $userWithPermissions = $this->penggunaModel->getUserWithRoleAndPermissions($userAuthCheck['id']);

            if ($userWithPermissions) {
                $sessionData = [
                    'isLoggedIn'        => true,
                    'user_id'           => $userWithPermissions['id'],
                    'user_name'         => $userWithPermissions['name'],
                    'user_role'         => $userWithPermissions['role_name'], // Store role name
                    'user_permissions'  => explode(',', $userWithPermissions['permissions_list'] ?? ''), // Store as an array
                ];
                session()->set($sessionData);
                return redirect()->to('/dashboard')->with('message', 'Login successful!');
            } else {
                // Should not happen if userAuthCheck was successful, but good to have a fallback
                log_message('error', "Failed to fetch role/permissions for successfully authenticated user ID: " . $userAuthCheck['id']);
                return redirect()->to('/login')->with('error', 'Login successful, but failed to retrieve user details. Please contact support.');
            }
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
        if (!hasPermission('users_view')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view users.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }
        $data['users'] = $this->penggunaModel->findAll();
        return view('pengguna/index', $data);
    }

    public function create()
    {
        if (!hasPermission('users_create')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to create users.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // Handle POST request for storing new user
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'             => 'required|min_length[3]|max_length[255]',
                'email'            => 'required|valid_email|is_unique[users.email]',
                'password'         => 'required|min_length[6]',
                'password_confirm' => 'required_with[password]|matches[password]',
                'role_id'          => 'required|is_not_unique[roles.id]' // Changed from 'role' to 'role_id'
            ];

            if (!$this->validate($rules)) {
                // Need to pass roles to the view if validation fails for dropdown repopulation
                $roleModel = new \App\Models\RoleModel();
                return view('pengguna/form', [
                    'validation' => $this->validator,
                    'roles' => $roleModel->findAll() // Assuming 'roles' variable is used in form for dropdown
                ]);
            } else {
                $userData = [
                    'name'     => $this->request->getPost('name'),
                    'email'    => $this->request->getPost('email'),
                    'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                    'role_id'  => $this->request->getPost('role_id') // Changed from 'role'
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
        // Need to pass roles to the view for dropdown
        $roleModel = new \App\Models\RoleModel();
        return view('pengguna/form', [
            'validation' => \Config\Services::validation(),
            'roles' => $roleModel->findAll() // Assuming 'roles' variable is used in form for dropdown
        ]);
    }

    public function edit($id = null)
    {
        if (!hasPermission('users_edit')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to edit users.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        $user = $this->penggunaModel->find($id); // This will now fetch user with role_id
        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('/pengguna');
        }

        // Handle POST request for updating existing user
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'             => 'required|min_length[3]|max_length[255]',
                'email'            => "required|valid_email|is_unique[users.email,id,{$id}]",
                'password'         => 'permit_empty|min_length[6]',
                'password_confirm' => 'required_with[password]|matches[password]',
                'role_id'          => 'required|is_not_unique[roles.id]' // Changed from 'role' to 'role_id'
            ];

            if (!$this->validate($rules)) {
                $roleModel = new \App\Models\RoleModel();
                return view('pengguna/form', [
                    'user'       => $user, // Pass existing user data (with role_id)
                    'validation' => $this->validator,
                    'roles'      => $roleModel->findAll()
                ]);
            } else {
                $updateData = [
                    'name'    => $this->request->getPost('name'),
                    'email'   => $this->request->getPost('email'),
                    'role_id' => $this->request->getPost('role_id') // Changed from 'role'
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
        $roleModel = new \App\Models\RoleModel();
        return view('pengguna/form', [
            'user'       => $user, // User data now includes role_id
            'validation' => \Config\Services::validation(),
            'roles'      => $roleModel->findAll()
        ]);
    }

    public function delete($id = null)
    {
        if (!hasPermission('users_delete')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to delete users.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
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
