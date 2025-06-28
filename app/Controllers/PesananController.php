<?php namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProdukModel;
use App\Models\CustomerModel;
use App\Models\PenggunaModel; // For staff/user name on order list
use App\Controllers\BaseController;

class PesananController extends BaseController
{
    protected $orderModel;
    protected $orderItemModel;
    protected $produkModel;
    protected $customerModel;
    protected $penggunaModel;
    protected $helpers = ['form', 'url', 'number', 'session']; // Added session helper

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->produkModel = new ProdukModel();
        $this->customerModel = new CustomerModel();
        $this->penggunaModel = new PenggunaModel();
    }

    // Removed private checkAuth() method, will use hasPermission() helper

    public function index()
    {
        if (!hasPermission('orders_view_all')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view orders.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // Fetch orders with customer and user (staff) names
        // Note: Model alias for 'orders.id' might be needed if it clashes with other 'id' fields after join.
        // For now, assuming 'orders.id' is distinct enough or handled by model's $primaryKey.
        $db = \Config\Database::connect();
        $builder = $db->table('orders');
        $builder->select('orders.*, customers.name as customer_name, users.name as user_name');
        $builder->join('customers', 'customers.id = orders.customer_id', 'left');
        $builder->join('users', 'users.id = orders.user_id', 'left'); // Assuming 'user_id' in 'orders' is the staff ID
        $builder->orderBy('orders.order_date', 'DESC');
        // $query = $builder->get();
        // $data['orders'] = $query->getResultArray();

        // Using Model's built-in capabilities if preferred and model is set up for it
        // This is often cleaner. Ensure ProdukModel's $table is 'products'.
        $data['orders'] = $this->orderModel
            ->select('orders.*, customers.name as customer_name, users.name as user_name')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left') // Ensure users table is correctly aliased if PenggunaModel $table is 'users'
            ->orderBy('orders.order_date', 'DESC')
            ->findAll(); // Consider adding pagination here: ->paginate(config('Pager')->perPage)

        // $data['pager'] = $this->orderModel->pager; // For pagination if using model's paginate

        return view('pesanan/index', $data);
    }

    public function new()
    {
        if (!hasPermission('orders_create_pos')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to create new orders.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // We will remove pre-loading all products.
        // The view should use the AJAX endpoint to search for products dynamically.
        $data['products'] = []; // Pass an empty array initially.
 
        $data['customers'] = $this->customerModel->orderBy('name', 'ASC')->findAll();
        $data['validation'] = \Config\Services::validation(); // For potential redirects with errors

        return view('pesanan/new', $data);
    }

    public function submitOrder()
    {
        if (!hasPermission('orders_create_pos')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to submit orders.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/pesanan/new')->with('error', 'Invalid request method.');
        }

        // Retrieve data from POST
        $rawCustomerId = $this->request->getPost('pos_customer_id');
        $customerId = !empty($rawCustomerId) ? (int)$rawCustomerId : null;

        $itemsJson = $this->request->getPost('pos_items_json');
        // Client-side totals are for reference/cross-check only if desired, server calculates authoritatively.
        // $clientTotalAmount = $this->request->getPost('pos_total_amount');

        // New discount fields from form
        $discountType = $this->request->getPost('pos_discount_type'); // 'percentage' or 'fixed_amount'
        $discountValueInput = $this->request->getPost('pos_discount_value_input'); // The value user typed for discount

        // Calculated amounts from client (for reference or detailed logging if needed, but will be recalculated)
        // $clientCalculatedDiscount = $this->request->getPost('pos_calculated_discount_amount');
        // $clientTaxAmount = $this->request->getPost('pos_tax_amount');
        // $clientSubtotalBeforeDiscount = $this->request->getPost('pos_subtotal_before_discount');

        $finalNotes = $this->request->getPost('pos_final_notes');
        $staffUserId = session()->get('user_id') ?? null;

        // --- Validation (Server-Side) ---
        $items = json_decode($itemsJson, true);
        if (empty($items) || json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->to('/pesanan/new')->with('error', 'Order items are invalid or empty. Please add products to the cart.')->withInput();
        }

        if ($customerId !== null) {
            $customer = $this->customerModel->find($customerId);
            if (!$customer) {
                return redirect()->to('/pesanan/new')->with('error', 'Selected customer is invalid.')->withInput();
            }
        }

        // Server-side calculation of totals
        $calculatedSubtotal = 0;
        $validatedItems = [];

        foreach ($items as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price_per_unit'])) {
                 return redirect()->to('/pesanan/new')->with('error', 'Malformed item data in order.')->withInput();
            }
            $product = $this->produkModel->find($item['product_id']);
            if (!$product) {
                return redirect()->to('/pesanan/new')->with('error', "Product with ID {$item['product_id']} not found.")->withInput();
            }
            if ($product['stock'] < $item['quantity']) {
                return redirect()->to('/pesanan/new')->with('error', "Insufficient stock for product: {$product['name']}. Available: {$product['stock']}, Requested: {$item['quantity']}.")->withInput();
            }
            if ($item['quantity'] <= 0) {
                 return redirect()->to('/pesanan/new')->with('error', "Quantity for product {$product['name']} must be positive.")->withInput();
            }
            // Use server-side price for security, or ensure price_per_unit from client matches server price
            $itemPrice = (float)$product['price']; // Using server-side price
            $itemTotalPrice = $itemPrice * (int)$item['quantity'];
            $calculatedSubtotal += $itemTotalPrice;

            $validatedItems[] = [
                'product_id'     => (int)$item['product_id'],
                'quantity'       => (int)$item['quantity'],
                'price_per_unit' => $itemPrice, // Use server-side price
                'total_price'    => $itemTotalPrice, // Server-calculated total for this item
                'product_stock_before_sale' => $product['stock'] // For stock update later
            ];
        }

        // Server-side recalculation of discount
        $serverCalculatedDiscountAmount = 0;
        $discountInputValueNumeric = (float)($discountValueInput ?? 0);

        if ($discountInputValueNumeric > 0) {
            if ($discountType === 'percentage') {
                if ($discountInputValueNumeric > 100) $discountInputValueNumeric = 100; // Cap percentage
                if ($discountInputValueNumeric < 0) $discountInputValueNumeric = 0;
                $serverCalculatedDiscountAmount = $calculatedSubtotal * ($discountInputValueNumeric / 100);
            } else { // fixed_amount
                $serverCalculatedDiscountAmount = $discountInputValueNumeric;
            }
            // Ensure discount doesn't exceed subtotal
            if ($serverCalculatedDiscountAmount > $calculatedSubtotal) {
                $serverCalculatedDiscountAmount = $calculatedSubtotal;
            }
             if ($serverCalculatedDiscountAmount < 0) $serverCalculatedDiscountAmount = 0;
        }


        $amountAfterDiscount = $calculatedSubtotal - $serverCalculatedDiscountAmount;

        // Server-side tax calculation (e.g., 10% of amount after discount)
        $taxRate = 0.10; // Define globally or from config
        $serverCalculatedTaxAmount = $amountAfterDiscount * $taxRate;

        $finalTotalAmount = $amountAfterDiscount + $serverCalculatedTaxAmount;

        if ($finalTotalAmount < 0) $finalTotalAmount = 0; // Total cannot be negative

        // --- Database Transaction ---
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create Main Order Record
            $orderData = [
                'customer_id'              => $customerId,
                'user_id'                  => $staffUserId,
                'order_date'               => date('Y-m-d H:i:s'),
                'subtotal_before_discount' => $calculatedSubtotal,       // New
                'discount_type'            => ($discountInputValueNumeric > 0) ? $discountType : null, // New
                'discount_value'           => ($discountInputValueNumeric > 0) ? $discountInputValueNumeric : null, // New - store the input value (e.g. 10 for 10% or 10000 for fixed)
                'calculated_discount_amount' => $serverCalculatedDiscountAmount, // New
                'tax_amount'               => $serverCalculatedTaxAmount,    // New
                'total_amount'             => $finalTotalAmount,
                'status'                   => 'completed',
                'notes'                    => $finalNotes,
            ];

            $orderId = $this->orderModel->insert($orderData, true);

            if (!$orderId) {
                // This should ideally not happen if inserts are typically successful and PK is auto-increment
                log_message('error', 'Failed to insert main order record. DB Error: ' . json_encode($this->orderModel->errors()));
                $db->transRollback();
                session()->setFlashdata('error', 'Failed to create order record.');
                return redirect()->to('/pesanan/new')->withInput();
            }

            // Loop Through Items and Process
            foreach ($validatedItems as $vItem) {
                $orderItemData = [
                    'order_id'       => $orderId,
                    'product_id'     => $vItem['product_id'],
                    'quantity'       => $vItem['quantity'],
                    'price_per_unit' => $vItem['price_per_unit'],
                    'total_price'    => $vItem['total_price']
                ];
                if (!$this->orderItemModel->save($orderItemData)) {
                    log_message('error', 'Failed to save order item. Data: '.json_encode($orderItemData).'. DB Error: '.json_encode($this->orderItemModel->errors()));
                    // transRollback will be called by transStatus check
                    throw new \Exception("Failed to save order item for product ID {$vItem['product_id']}.");
                }

                // Decrement Product Stock
                $newStock = $vItem['product_stock_before_sale'] - $vItem['quantity'];
                if (!$this->produkModel->update($vItem['product_id'], ['stock' => $newStock])) {
                     log_message('error', 'Failed to update stock for product ID '.$vItem['product_id'].'. DB Error: '.json_encode($this->produkModel->errors()));
                     throw new \Exception("Failed to update stock for product ID {$vItem['product_id']}.");
                }
            }

            // Complete Transaction
            if ($db->transStatus() === false) {
                $db->transRollback();
                session()->setFlashdata('error', 'Order processing failed due to a database issue. Please try again.');
            } else {
                $db->transCommit();
                session()->setFlashdata('message', 'Order placed successfully! Order ID: #' . $orderId);
                // Redirect to receipt view on success
                return redirect()->to('/pesanan/receipt/' . $orderId);
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[ERROR] Exception during order submission: ' . $e->getMessage());
            session()->setFlashdata('error', 'An unexpected error occurred: ' . $e->getMessage());
        }

        return redirect()->to('/pesanan/new')->withInput(); // Redirect back to POS on failure
    }

    public function view($id = null)
    {
        if (!hasPermission('orders_view_details')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view order details.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // Basic data fetching example (adjust as needed)
        $order = $this->orderModel
            ->select('orders.*, customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone, customers.address as customer_address, users.name as user_name')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left')
            ->find($id);

        if (!$order) {
            session()->setFlashdata('error', 'Order not found.');
            return redirect()->to('/pesanan');
        }

        $order_items = $this->orderItemModel
            ->select('order_items.*, products.name as product_name, products.sku as product_sku')
            ->join('products', 'products.id = order_items.product_id', 'left')
            ->where('order_items.order_id', $id)
            ->findAll();

        $data = [
            'order' => $order,
            'order_items' => $order_items,
            // Customer data is already joined into $order, but if needed separately for the view structure:
            'customer' => ($order['customer_id']) ? $this->customerModel->find($order['customer_id']) : null
        ];

        return view('pesanan/view', $data);
    }

    public function edit($id = null)
    {
        if (!hasPermission('orders_edit')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to edit orders.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // To be implemented: Allow editing of PENDING orders (e.g., items, customer, notes)
        // This would likely involve a modified POS-like interface.
        session()->setFlashdata('info', 'Editing existing orders is not yet implemented. (Order ID: ' . $id . ')');
        return redirect()->to('/pesanan/view/' . $id); // Redirect to view for now
    }

    public function cancelOrder($id = null)
    {
        if (!hasPermission('orders_cancel')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to cancel orders.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        // To be implemented: Handle order cancellation
        // - Check if order can be cancelled (e.g., status is 'pending')
        // - Update order status to 'cancelled'
        // - Potentially restore product stock
        // - DB Transaction
        // - Redirect with flash message

        if (!hasPermission('orders_cancel')) {
            session()->setFlashdata('error', 'Access Denied. You do not have permission to cancel orders.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        $order = $this->orderModel->find($id);

        if (!$order) {
            session()->setFlashdata('error', 'Order not found.');
            return redirect()->to('/pesanan');
        }

        if (strtolower($order['status']) !== 'pending') {
            session()->setFlashdata('error', 'Only pending orders can be cancelled. This order is already ' . $order['status'] . '.');
            return redirect()->to('/pesanan/view/' . $id);
        }

        $db = $this->orderModel->db; // Use existing DB connection from a model
        $db->transStart();

        try {
            // Fetch Order Items
            $items = $this->orderItemModel->where('order_id', $id)->findAll();

            // Restore Stock
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (!$this->produkModel->incrementStock($item['product_id'], $item['quantity'])) {
                        // Log error or add more specific error handling if incrementStock fails
                        log_message('error', "Failed to restore stock for product ID {$item['product_id']} on order cancellation {$id}.");
                        // This will trigger transRollback due to exception or return false from incrementStock
                        throw new \Exception("Failed to restore stock for product ID {$item['product_id']}.");
                    }
                }
            }

            // Update Order Status
            if (!$this->orderModel->update($id, ['status' => 'cancelled'])) {
                 throw new \Exception("Failed to update order status for order ID {$id}.");
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                session()->setFlashdata('error', 'Failed to cancel order. Please try again.');
            } else {
                $db->transCommit();
                session()->setFlashdata('message', 'Order #' . $id . ' has been successfully cancelled and stock restored.');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', "[ERROR] Exception during order cancellation (Order ID: {$id}): " . $e->getMessage());
            session()->setFlashdata('error', 'An unexpected error occurred while cancelling the order: ' . $e->getMessage());
        }

        return redirect()->to('/pesanan/view/' . $id);
    }

    public function ajaxProductSearch()
    {
        // This AJAX endpoint supports the POS creation page, so it should have the same permission.
        if (!hasPermission('orders_create_pos')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Access Denied.']);
        }

        $keyword = $this->request->getGet('term'); // 'term' is common for jQuery UI Autocomplete, select2

        $query = $this->produkModel->select('id, name, sku, price, stock, image_path');

        if (!empty($keyword)) {
            $query->groupStart()
                  ->like('name', $keyword)
                  ->orLike('sku', $keyword)
                  ->groupEnd();
        }

        $query->where('stock >', 0); // Only show products in stock for POS selection
        $query->orderBy('name', 'ASC');
        $query->limit(15); // Limit results for performance in autocomplete scenarios

        $products = $query->findAll();

        // Format for select2 or other autocomplete libraries if needed, or return as is.
        // Example for select2:
        // $formatted_products = [];
        // foreach($products as $product) {
        //     $formatted_products[] = ['id' => $product['id'], 'text' => $product['name'] . ($product['sku'] ? ' ('.$product['sku'].')' : '') . ' - Rp ' . number_format($product['price'],0,',','.') . ' | Stock: ' . $product['stock']];
        // }
        // return $this->response->setJSON($formatted_products);

        return $this->response->setJSON($products);
    }

    public function receipt($orderId = null)
    {
        if (!hasPermission('orders_view_details')) { // Same permission as viewing order details
            session()->setFlashdata('error', 'Access Denied. You do not have permission to view this receipt.');
            return redirect()->to(isLoggedIn() ? '/dashboard' : '/login');
        }

        $order = $this->orderModel
            ->select('orders.*, customers.name as customer_name, customers.email as customer_email, customers.phone as customer_phone, users.name as user_name')
            ->join('customers', 'customers.id = orders.customer_id', 'left')
            ->join('users', 'users.id = orders.user_id', 'left') // Staff who processed
            ->find($orderId);

        if (!$order) {
            session()->setFlashdata('error', 'Order not found.');
            return redirect()->to('/pesanan');
        }

        $items = $this->orderItemModel
            ->select('order_items.*, products.name as product_name, products.sku as product_sku')
            ->join('products', 'products.id = order_items.product_id', 'left')
            ->where('order_items.order_id', $orderId)
            ->findAll();

        // Assuming discount and tax are stored directly in the orders table or calculated based on stored values
        // For now, the 'orders' table has 'total_amount'. If discount_amount and tax_amount were added to orders table:
        // $order['discount_amount'] = $order['discount_amount'] ?? 0; // Example if these fields exist
        // $order['tax_amount'] = $order['tax_amount'] ?? 0; // Example
        // $order['subtotal_for_receipt'] = $order['total_amount'] - $order['tax_amount'] + $order['discount_amount']; // Recalculate subtotal if needed

        $data = [
            'order' => $order,
            'items' => $items,
            'storeName' => 'KasirKu Store', // Example, can be from config
            'storeAddress' => '123 Main Street, Anytown', // Example
            'storeContact' => 'Phone: (123) 456-7890', // Example
        ];

        return view('pesanan/receipt', $data);
    }
}
