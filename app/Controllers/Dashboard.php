<?php namespace App\Controllers;

class Dashboard extends BaseController
{
    public function __construct()
    {
        helper(['url', 'session']); // Ensure helpers are loaded
    }

    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login to access the dashboard.');
        }

        // In a real app, load a view:
        // $data['title'] = 'Dashboard';
        // $data['userName'] = session()->get('user_name') ?? 'User';
        // return view('dashboard/index', $data);

        $userName = esc(session()->get('user_name') ?? 'User');
        $output = "<!DOCTYPE html><html><head><title>Dashboard</title>";
        // Minimal styling to make it look like it respects the layout somewhat
        $output .= "<link rel='stylesheet' href='" . base_url('css/style.css') . "'>"; // Assuming style.css is available
        $output .= "</head><body class='bg-gray-100 p-8'>";
        $output .= "<div class='container mx-auto bg-white p-6 rounded-lg shadow-md'>";
        $output .= "<h1 class='text-2xl font-bold text-gray-700'>Welcome to the Dashboard, " . $userName . "!</h1>";
        $output .= "<p class='mt-4'>This is a placeholder dashboard page.</p>";
        $output .= "<p class='mt-6'><a href='" . site_url('/logout') . "' class='text-red-500 hover:text-red-700 font-semibold'>Logout</a></p>";
        $output .= "</div>";
        $output .= "</body></html>";
        return $output;
    }
}
