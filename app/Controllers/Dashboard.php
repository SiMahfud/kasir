<?php namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\CustomerModel;
use App\Models\ProdukModel;

class Dashboard extends BaseController
{
    protected $orderModel;
    protected $customerModel;
    protected $produkModel;

    public function __construct()
    {
        helper(['auth', 'url', 'session', 'number']); // auth for isLoggedIn, number for currency
        $this->orderModel = new OrderModel();
        $this->customerModel = new CustomerModel();
        $this->produkModel = new ProdukModel();
    }

    public function index()
    {
        if (!isLoggedIn()) { // Using auth_helper function
            return redirect()->to('/login')->with('error', 'Please login to access the dashboard.');
        }

        $data = [];
        $today = date('Y-m-d');

        // Summary Data
        $salesTodayResult = $this->orderModel
            ->where('DATE(order_date)', $today)
            ->where('status', 'completed')
            ->selectSum('total_amount', 'totalSales')
            ->first();
        $data['salesToday'] = $salesTodayResult['totalSales'] ?? 0;

        $data['ordersToday'] = $this->orderModel
            ->where('DATE(order_date)', $today)
            ->where('status', 'completed')
            ->countAllResults();

        $data['totalCustomers'] = $this->customerModel->countAllResults();

        $lowStockThreshold = 10; // Define or get from config
        $data['lowStockProducts'] = $this->produkModel
            ->where('stock >', 0)
            ->where('stock <=', $lowStockThreshold)
            ->countAllResults();

        // Sales Last 7 Days (for chart)
        $salesLast7Days = $this->orderModel
            ->select("DATE(order_date) as sale_date, SUM(total_amount) as daily_total")
            ->where('order_date >=', date('Y-m-d 00:00:00', strtotime('-6 days')))
            ->where('order_date <=', date('Y-m-d 23:59:59', strtotime('today')))
            ->where('status', 'completed')
            ->groupBy('DATE(order_date)')
            ->orderBy('DATE(order_date)', 'ASC')
            ->findAll();

        $chartLabels = [];
        $chartData = [];
        // Ensure a full 7-day range even if no sales on some days
        try {
            $dateRange = new \DatePeriod(
                new \DateTime('-6 days midnight'), // Start from 6 days ago
                new \DateInterval('P1D'),      // Increment by 1 day
                new \DateTime('today midnight +1 day') // End on today (inclusive)
            );

            foreach ($dateRange as $date) {
                $chartLabels[] = $date->format('M d'); // e.g., Jun 26
                $dailySale = 0;
                foreach ($salesLast7Days as $sale) {
                    if ($sale['sale_date'] === $date->format('Y-m-d')) {
                        $dailySale = (float)$sale['daily_total'];
                        break;
                    }
                }
                $chartData[] = $dailySale;
            }
        } catch (\Exception $e) {
            // Handle DatePeriod exception, though unlikely with valid inputs
            log_message('error', 'Error creating date range for dashboard chart: ' . $e->getMessage());
            // Fallback to empty chart data or simpler range if needed
        }

        $data['chartSalesLabels'] = json_encode($chartLabels);
        $data['chartSalesData'] = json_encode($chartData);
        $data['title'] = 'Dashboard'; // For the view's title section
        $data['userName'] = session()->get('user_name') ?? 'User';


        return view('dashboard/index', $data);
    }
}
