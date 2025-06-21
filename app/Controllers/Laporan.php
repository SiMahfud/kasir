<?php namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\ProdukModel;
use App\Models\KategoriModel;
use App\Controllers\BaseController; // Ensure this is the correct path to your BaseController

class Laporan extends BaseController // Class name matches Laporan.php
{
    protected $orderModel;
    protected $produkModel;
    protected $kategoriModel;
    protected $helpers = ['form', 'url', 'number', 'session']; // 'auth' helper is autoloaded

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->produkModel = new ProdukModel();
        $this->kategoriModel = new KategoriModel();
    }

    // Removed private checkAuth() method

    public function penjualan()
    {
        if (!hasPermission('reports_view_sales')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view sales reports.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        $filters = [
            'tanggal_awal'  => $this->request->getGet('tanggal_awal'),
            'tanggal_akhir' => $this->request->getGet('tanggal_akhir')
        ];

        $query = $this->orderModel
            ->select('orders.*, customers.name as customer_name, users.name as user_name')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left');

        if (!empty($filters['tanggal_awal'])) {
            // Ensure date is in YYYY-MM-DD format for comparison
            $query->where('orders.order_date >=', date('Y-m-d', strtotime($filters['tanggal_awal'])) . ' 00:00:00');
        }
        if (!empty($filters['tanggal_akhir'])) {
            $query->where('orders.order_date <=', date('Y-m-d', strtotime($filters['tanggal_akhir'])) . ' 23:59:59');
        }

        // Clone the query builder instance for summary calculation *before* adding ordering and pagination for sales data
        $summaryQueryBuilder = clone $query->builder(); // Get the DB builder instance from the model's query

        // For sales data with pagination
        $data['sales_data'] = $query->orderBy('orders.order_date', 'DESC')->paginate(15); // Default perPage or specify
        $data['pager'] = $this->orderModel->pager;

        // Calculate Summary using the cloned builder without pagination/ordering affecting summary results
        // The select method on the model's builder returns a new builder instance,
        // so we need to be careful. Or use a new builder instance based on the model's table.
        // For simplicity, let's re-evaluate how summary is built on filtered data.
        // One way: Get IDs from paginated result and re-query, or adjust the summary query.

        // Simpler summary: use the non-paginated, non-ordered $summaryQueryBuilder
        // If using model's query builder which might have internal state, it's tricky.
        // Let's use a fresh query for summary but with same where clauses.

        $summaryQuery = $this->orderModel
            ->select('COUNT(orders.id) as total_orders, SUM(orders.total_amount) as total_revenue');
            // No need to join for this summary if only using orders table fields for count/sum

        if (!empty($filters['tanggal_awal'])) {
            $summaryQuery->where('orders.order_date >=', date('Y-m-d', strtotime($filters['tanggal_awal'])) . ' 00:00:00');
        }
        if (!empty($filters['tanggal_akhir'])) {
            $summaryQuery->where('orders.order_date <=', date('Y-m-d', strtotime($filters['tanggal_akhir'])) . ' 23:59:59');
        }
        // Add other filters if they affect which orders are included in summary (e.g. status = 'completed')
        // $summaryQuery->where('orders.status', 'completed'); // Example: only completed orders for revenue

        $summary_results = $summaryQuery->first();

        $data['summary']['total_orders'] = $summary_results['total_orders'] ?? 0;
        $data['summary']['total_revenue'] = $summary_results['total_revenue'] ?? 0;
        $data['summary']['average_order_value'] = ($data['summary']['total_orders'] > 0)
            ? $data['summary']['total_revenue'] / $data['summary']['total_orders']
            : 0;

        $data['filters'] = $filters;
        return view('laporan/penjualan', $data);
    }

    public function stok()
    {
        if (!hasPermission('reports_view_stock')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view stock reports.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        $filters = [
            'category_id' => $this->request->getGet('category_id'),
            'stock_level' => $this->request->getGet('stock_level')
        ];

        $data['categories'] = $this->kategoriModel->orderBy('name', 'ASC')->findAll();

        $query = $this->produkModel
            ->select('products.*, categories.name as category_name') // products.name, categories.name
            ->join('categories', 'categories.id = products.category_id', 'left'); // Use correct table names

        if (!empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if (!empty($filters['stock_level'])) {
            $low_stock_threshold = 10; // Can be a config value
            if ($filters['stock_level'] === 'low') {
                $query->where('products.stock >', 0)->where('products.stock <=', $low_stock_threshold);
            } elseif ($filters['stock_level'] === 'out') {
                $query->where('products.stock <=', 0);
            }
        }

        $data['stock_data'] = $query->orderBy('products.name', 'ASC')->paginate(15); // Default perPage or specify
        $data['pager'] = $this->produkModel->pager;
        $data['filters'] = $filters;

        return view('laporan/stok', $data);
    }

    public function exportSalesCSV()
    {
        if (!hasPermission('reports_export_data')) {
            // For file downloads, a redirect might not show the flash message effectively.
            // Returning a simple error response is better.
            return $this->response->setStatusCode(403)->setBody('Access Denied: You do not have permission to export this data.');
        }

        $tanggal_awal = $this->request->getGet('tanggal_awal');
        $tanggal_akhir = $this->request->getGet('tanggal_akhir');
        // Add other filters here if your sales report supports them (e.g., status, customer)

        $query = $this->orderModel
            ->select('orders.id as order_id, orders.order_date, customers.name as customer_name, users.name as user_name, orders.subtotal_before_discount, orders.calculated_discount_amount, orders.tax_amount, orders.total_amount, orders.status')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left');

        $filename_parts = ["sales_report"];
        if (!empty($tanggal_awal)) {
            $query->where('orders.order_date >=', date('Y-m-d', strtotime($tanggal_awal)) . ' 00:00:00');
            $filename_parts[] = "from_" . date('Ymd', strtotime($tanggal_awal));
        }
        if (!empty($tanggal_akhir)) {
            $query->where('orders.order_date <=', date('Y-m-d', strtotime($tanggal_akhir)) . ' 23:59:59');
            $filename_parts[] = "to_" . date('Ymd', strtotime($tanggal_akhir));
        }
        // Apply other filters to $query here

        $sales_data = $query->orderBy('orders.order_date', 'DESC')->findAll();

        $filename = implode("_", $filename_parts) . ".csv";

        // Set HTTP headers for CSV download
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=\"{$filename}\""); // Note the escaped quotes for filename
        header("Content-Type: application/csv; charset=UTF-8");

        $file = fopen('php://output', 'w');

        // Add BOM for UTF-8 to ensure Excel opens cyrillic/special characters correctly
        fputs($file, "\xEF\xBB\xBF");

        // CSV Headers
        fputcsv($file, [
            'Order ID', 'Date', 'Customer', 'Staff',
            'Subtotal', 'Discount Applied', 'Tax Amount', 'Total Amount', 'Status'
        ]);

        // CSV Rows
        foreach ($sales_data as $sale) {
            fputcsv($file, [
                $sale['order_id'],
                date('Y-m-d H:i:s', strtotime($sale['order_date'])),
                $sale['customer_name'] ?? 'N/A (Guest)',
                $sale['user_name'] ?? 'N/A',
                number_format($sale['subtotal_before_discount'] ?? 0, 2, '.', ''), // Use dot as decimal separator for CSV
                number_format($sale['calculated_discount_amount'] ?? 0, 2, '.', ''),
                number_format($sale['tax_amount'] ?? 0, 2, '.', ''),
                number_format($sale['total_amount'] ?? 0, 2, '.', ''),
                ucfirst($sale['status'] ?? 'N/A')
            ]);
        }

        fclose($file);
        exit(); // Crucial to prevent any further output from CodeIgniter
    }
}
