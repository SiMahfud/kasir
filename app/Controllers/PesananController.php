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

        // Fetch products that are in stock or handle stock display/logic in view JS
        $data['products'] = $this->produkModel
                            // ->where('stock >', 0) // Example: only show in-stock items
                            ->orderBy('name', 'ASC')
                            ->findAll();

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
        $clientTotalAmount = $this->request->getPost('pos_total_amount'); // For reference, will be recalculated
        $clientDiscountAmount = $this->request->getPost('pos_discount_amount') ?? 0;
        $clientTaxAmount = $this->request->getPost('pos_tax_amount') ?? 0; // Assuming tax is a value, not a rate here
        $finalNotes = $this->request->getPost('pos_final_notes');

        $staffUserId = session()->get('user_id') ?? null; // Assumes logged-in staff ID is in session

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

        // Apply discount and tax (example: tax is 10% of subtotal after discount)
        $discountAmount = (float)$clientDiscountAmount;
        if ($discountAmount < 0) $discountAmount = 0;
        if ($discountAmount > $calculatedSubtotal) $discountAmount = $calculatedSubtotal;

        $amountAfterDiscount = $calculatedSubtotal - $discountAmount;

        // Assuming clientTaxAmount is a pre-calculated value. If it's a rate, apply it here.
        // For simplicity, let's assume clientTaxAmount is the value to be added.
        // Or, recalculate tax based on a fixed rate if that's the business rule.
        // Example: $taxRate = 0.10; $taxAmount = $amountAfterDiscount * $taxRate;
        $taxAmount = (float)$clientTaxAmount; // Trusting client-calculated tax for now, or recalculate
        if ($taxAmount < 0) $taxAmount = 0;

        $finalTotalAmount = $amountAfterDiscount + $taxAmount;

        // Validate final total amount (optional, but good sanity check)
        if (!is_numeric($finalTotalAmount) || $finalTotalAmount < 0) { // Can be 0 if fully discounted
             return redirect()->to('/pesanan/new')->with('error', 'Calculated total amount is invalid.')->withInput();
        }
        // Compare with clientTotalAmount if desired, though server calculation is king
        // if (abs($finalTotalAmount - (float)$clientTotalAmount) > 0.01) { // Allow for small float differences
        //     log_message('warning', "Client total {$clientTotalAmount} differs from server calculated total {$finalTotalAmount} for a potential order.");
        // }


        // --- Database Transaction ---
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create Main Order Record
            $orderData = [
                'customer_id'  => $customerId,
                'user_id'      => $staffUserId,
                'order_date'   => date('Y-m-d H:i:s'), // Current timestamp
                'total_amount' => $finalTotalAmount,
                'status'       => 'completed', // Default status, adjust if payment pending etc.
                'notes'        => $finalNotes,
                // Storing calculated subtotal, discount, tax for reference
                // 'subtotal_amount' => $calculatedSubtotal,
                // 'discount_amount' => $discountAmount,
                // 'tax_amount'      => $taxAmount,
            ];
            // Note: The database schema from earlier did not explicitly include subtotal, discount, tax in 'orders' table.
            // If they are needed, migrations should be updated. For now, they are part of `total_amount`.
            // The POS view did have discount and tax displays, so it's good to store them if possible.
            // Assuming 'total_amount' is the final amount after discount and tax.

            $orderId = $this->orderModel->insert($orderData, true); // true for returning insert ID

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
                return redirect()->to('/pesanan/view/' . $orderId); // Redirect to order view on success
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
        session()->setFlashdata('info', 'Cancelling orders is not yet implemented. (Order ID: ' . $id . ')');
        return redirect()->to('/pesanan');
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
}
