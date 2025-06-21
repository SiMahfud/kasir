# KasirKu - Point of Sale & Inventory Management

KasirKu is a web-based Point of Sale (POS) and inventory management application built with PHP and the CodeIgniter 4 framework. It features a modern, responsive user interface styled with Tailwind CSS.

## Features

*   **User Management:**
    *   Admin and Staff roles.
    *   CRUD operations for users (Admin only).
    *   Secure password hashing.
    *   Role-based access control for application features.
*   **Product Management:**
    *   CRUD operations for products.
    *   Categorization of products.
    *   Image uploads for products.
    *   SKU and stock tracking.
*   **Category Management:**
    *   CRUD operations for product categories.
    *   Prevents deletion of categories with associated products.
*   **Customer Management:**
    *   CRUD operations for customer records.
    *   Prevents deletion of customers with associated orders.
*   **Point of Sale (POS) Interface:**
    *   Interactive interface for creating new orders.
    *   Product selection with cart management (add, update quantity, remove).
    *   Customer selection (optional).
    *   Real-time calculation of subtotal, discount, tax (10%), and grand total.
    *   Order submission with server-side validation and stock updates (within DB transaction).
*   **Order Management:**
    *   List view of all past orders with key details.
    *   Detailed view of individual orders, including items and customer information.
    *   (Placeholders for order editing/cancellation).
*   **Reporting:**
    *   Sales Report: View sales data with date filters and summary statistics (total orders, revenue, average order value).
    *   Stock Report: View product stock levels with filters for category and stock status (low stock, out of stock), with visual cues for stock levels.
*   **Data Seeding:**
    *   Seeders for initial setup of admin/staff users, categories, products, and customers.

## Technology Stack

*   **Backend:** PHP 8.x, CodeIgniter 4.x
*   **Frontend:** Tailwind CSS, Vanilla JavaScript (for POS interactivity)
*   **Database:** MySQL (or other database compatible with CodeIgniter)
*   **Package Management:** Composer (PHP), npm (for Tailwind CSS)

## Prerequisites

*   PHP 8.0 or higher (with `intl`, `mbstring`, `json`, `mysqlnd`, `libcurl` extensions).
*   Composer 2.x.
*   Node.js and npm (for Tailwind CSS development).
*   A web server (e.g., Apache, Nginx) or use `php spark serve`.
*   A database server (e.g., MySQL, MariaDB).

## Installation and Setup

1.  **Clone the Repository:**
    ```bash
    git clone <repository_url>
    cd <repository_directory>
    ```

2.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **Install Node.js Dependencies (for Tailwind CSS):**
    ```bash
    npm install
    ```

4.  **Environment Configuration:**
    *   Copy the `env` file to `.env`:
        ```bash
        cp env .env
        ```
    *   Open the `.env` file and configure the following:
        *   `app.baseURL`: Set to your application's base URL (e.g., `http://localhost:8080/`).
        *   `database.default.hostname`: Database host (e.g., `localhost`).
        *   `database.default.database`: Database name.
        *   `database.default.username`: Database username.
        *   `database.default.password`: Database password.
        *   `database.default.DBDriver`: Usually `MySQLi`.
    *   Ensure the database specified in `.env` exists, or create it.

5.  **Run Database Migrations:**
    *   This will create all the necessary tables in your database.
    ```bash
    php spark migrate
    ```

6.  **Run Database Seeders (Optional but Recommended for initial data):**
    *   This will populate the database with sample data, including an admin user. The default password for seeded users is `password123`.
    ```bash
    php spark db:seed DatabaseSeeder
    ```
    (Note: `DatabaseSeeder` is specified here to run all configured seeders.)

7.  **Compile Tailwind CSS:**
    *   This generates the `public/css/style.css` file used by the application.
    ```bash
    npm run build-css
    ```
    *   For development, you can use `npm run build-css -- --watch` to automatically recompile on changes to view files or `tailwind.config.js`. (Note: The script name in `package.json` might be just `build-css` without the extra `--watch` by default as defined in previous steps, the `-- --watch` is an additional flag for the user to add if needed).

## Running the Application

*   **Using CodeIgniter's Spark (Development Server):**
    ```bash
    php spark serve
    ```
    The application will typically be available at `http://localhost:8080/`.

*   **Using a traditional Web Server (Apache, Nginx):**
    Configure your web server's document root to point to the `public/` directory of the project.

## Usage

### Default User Accounts (from Seeder)

*   **Admin:**
    *   Email: `admin@example.com`
    *   Password: `password123`
*   **Staff:**
    *   Email: `staff@example.com`
    *   Password: `password123`

### Basic Workflow

1.  **Login:** Access the `/login` page (or the application root `/` which should redirect to `/login` if not authenticated) and use one of the default accounts.
2.  **Dashboard:** After login, you'll be redirected to the dashboard.
3.  **Navigation:** Use the main navigation menu to access different modules:
    *   **Pengguna (Users):** Manage users (Admin only).
    *   **Produk (Products):** Manage products.
    *   **Kategori (Categories):** Manage product categories.
    *   **Pelanggan (Customers):** Manage customer records.
    *   **Pesanan (Orders/POS):**
        *   Click "Create New Order (POS)" to access the Point of Sale interface.
        *   View past orders in the order list.
    *   **Laporan (Reports):** View Sales and Stock reports.
4.  **POS Usage:**
    *   Select products from the list to add them to the cart.
    *   Adjust quantities or remove items from the cart.
    *   Optionally select a customer.
    *   Enter any discount (flat amount) or notes.
    *   The system calculates subtotal, tax (10%), and total automatically.
    *   Click "Finalize & Submit Order" to save the order.

---

This README provides a comprehensive guide to setting up and using the KasirKu application. Remember to replace `<repository_url>` and `<repository_directory>` with actual values.
