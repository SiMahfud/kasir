<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function __construct()
    {
        helper(['url', 'session']); // Ensure helpers are loaded
    }

    public function index() // Return type will be a RedirectResponse or string
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }
        return redirect()->to('/dashboard');
    }
}
