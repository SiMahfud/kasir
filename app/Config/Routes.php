<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home Route (already updated to redirect based on auth)
$routes->get('/', 'Home::index');

// Authentication Routes
$routes->get('/login', 'Pengguna::login', ['as' => 'login']); // Named route for login
$routes->post('/login/authenticate', 'Pengguna::authenticate'); // Changed from /authenticate to avoid conflict if a generic Auth controller is made
$routes->get('/logout', 'PenggunaController::logout');

// Dashboard Route
$routes->get('/dashboard', 'Dashboard::index');

// Resourceful Routes for CRUD Controllers
// Note: Controllers use create() for GET (form) & POST (store), edit($id) for GET (form) & POST (update)
// CodeIgniter's resource routes map POST /resource to create() and PUT /resource/(:segment) to update($id).
// Our forms for edit use <input type="hidden" name="_method" value="PUT">, so this should align.

// Users (Pengguna) - Full resource, managed by PenggunaController
// Access to these routes is further restricted within PenggunaController to 'admin' role.
$routes->resource('pengguna', ['controller' => 'Pengguna']);
// This will generate:
// GET      /pengguna           => PenggunaController::index
// GET      /pengguna/new       => PenggunaController::new (Our create() method handles GET for form) - Manual route might be better
// POST     /pengguna           => PenggunaController::create (Our create() method handles POST for store)
// GET      /pengguna/(:segment)    => PenggunaController::show($1) (We use edit for form, view for details if separate) - We don't have a show method, edit handles viewing form.
// GET      /pengguna/(:segment)/edit => PenggunaController::edit($1)
// PUT      /pengguna/(:segment)    => PenggunaController::update($1) (Our edit() method handles POST with _method=PUT)
// DELETE   /pengguna/(:segment)    => PenggunaController::delete($1)

// Products (Produk)
$routes->resource('produk', ['controller' => 'Produk']);

// Categories (Kategori)
$routes->resource('kategori', ['controller' => 'KategoriController']);

// Customers (Pelanggan)
$routes->resource('pelanggan', ['controller' => 'PelangganController']);

// Suppliers
$routes->resource('suppliers', ['controller' => 'SupplierController']);


// Custom routes for Order Management (Pesanan)
$routes->get('pesanan', 'PesananController::index');
$routes->get('pesanan/new', 'PesananController::new');                   // Display POS form
$routes->get('pesanan/ajax_product_search', 'PesananController::ajaxProductSearch'); // AJAX Product Search for POS
$routes->post('pesanan/submit_order', 'PesananController::submitOrder'); // Handle POS submission
$routes->get('pesanan/view/(:num)', 'PesananController::view/$1');
$routes->get('pesanan/receipt/(:num)', 'PesananController::receipt/$1'); // New Receipt Route
$routes->get('pesanan/edit/(:num)', 'PesananController::edit/$1');       // Placeholder for editing an order (e.g., if status allows)
// $routes->post('pesanan/update/(:num)', 'PesananController::update/$1'); // Placeholder for actual update logic if edit is implemented
$routes->get('pesanan/cancel/(:num)', 'PesananController::cancelOrder/$1'); // Placeholder for cancelling an order


// Routes for Reports (Laporan)
// Note: The controller class is 'Laporan', not 'LaporanController'
$routes->get('laporan/penjualan', 'Laporan::penjualan');
$routes->get('laporan/export_sales_csv', 'Laporan::exportSalesCSV'); // CSV Export Route
$routes->get('laporan/stok', 'Laporan::stok');


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
