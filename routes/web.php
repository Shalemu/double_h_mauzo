<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Auth Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Core Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

// Business Controllers
use App\Http\Controllers\ShopsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\UnitController;

// Management Controllers
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RoleController;

// Transactions & Cart
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CartController;

// Customer & Sales
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;

// Expenses
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\FixedExpensesController;

// Purchases & Supplier
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\SupplierController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Authentication
Route::middleware('guest:web,staff')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Product exports
Route::get('products/export-excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
Route::get('products/export-pdf', [ProductController::class, 'exportPDF'])->name('products.export.pdf');

/*
|--------------------------------------------------------------------------
| Staff Routes (POS)
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard/staff')
    ->middleware(['web', 'staff'])
    ->name('staff.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'staff'])->name('dashboard');

        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        // Customers
       Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/manage', [CustomerController::class, 'manage'])->name('manage');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');

            // Record Payment for a specific customer
            Route::post('/{customer}/record-payment', [CustomerController::class, 'recordPayment'])->name('recordPayment');
        });



        // Sales
        Route::prefix('sales')->group(function () {
    Route::get('/{shop}', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/{shop}/{date}', [SaleController::class, 'detail'])->name('sales.detail');

   
    Route::post('/checkout/{shop}', [SaleController::class, 'checkout'])
        ->name('sales.checkout');

    Route::get('/{shop}/{date}/export-excel', [SaleController::class, 'exportExcel'])->name('sales.export.excel');
    Route::get('/{shop}/{date}/export-pdf', [SaleController::class, 'exportPdf'])->name('sales.export.pdf');
});


        // Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'getCart'])->name('cart.get');
            Route::post('/add', [CartController::class, 'add'])->name('cart.add');
            Route::post('/update', [CartController::class, 'updateCart'])->name('cart.update');
            Route::post('/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
        });

        // Expenses
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('{shop}', [ExpensesController::class, 'index'])->name('index');
            Route::get('{shop}/create', [ExpensesController::class, 'create'])->name('create');
            Route::get('{shop}/detail', [ExpensesController::class, 'details'])->name('detail');
            Route::post('{shop}', [ExpensesController::class, 'store'])->name('store');
            Route::get('{id}/edit', [ExpensesController::class, 'edit'])->name('edit');
            Route::put('{id}', [ExpensesController::class, 'update'])->name('update');
            Route::delete('{id}', [ExpensesController::class, 'destroy'])->name('destroy');
        });

        // Transactions
        Route::post('/transaction/checkout', [TransactionController::class, 'checkout'])->name('transaction.checkout');
    });

/*
|--------------------------------------------------------------------------
| Admin & Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {

    // Redirect /dashboard
    Route::get('/dashboard', function () {
        if (Auth::guard('staff')->check()) return redirect()->route('staff.dashboard');
        return redirect()->route('dashboard.admin');
    })->name('dashboard');

    // Admin Dashboard
    Route::get('/dashboard/admin', [DashboardController::class, 'index'])
        ->middleware('admin')->name('dashboard.admin');

    // Profile & Password
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/update/{user_id}', [UserController::class, 'updateprofile'])->name('updateprofile');
    Route::get('/changepassword', [UserController::class, 'changepassword'])->name('changepassword');
    Route::post('/changePassword/{user_id}', [UserController::class, 'updatePassword'])->name('changePassword');
    Route::get('/main/logout', [UserController::class, 'userlogout'])->name('user.logout');

    /*
    |--------------------------------------------------------------------------
    | Admin Staff Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/admin/staff')
        ->middleware('admin')
        ->name('staff.manage.')
        ->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::post('/', [StaffController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [StaffController::class, 'edit'])->name('edit');
            Route::put('/{id}', [StaffController::class, 'update'])->name('update');
            Route::delete('/{id}', [StaffController::class, 'destroy'])->name('destroy');
        });

    /*
    |--------------------------------------------------------------------------
    | Shops
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard/shop', [ShopsController::class, 'index'])->name('dashboard.shop');
    Route::post('/shops', [ShopsController::class, 'store'])->name('shops.store');
    Route::get('/dashboard/shop/{shop}', [ShopsController::class, 'show'])->name('dashboard.shop.show');

    /*
    |--------------------------------------------------------------------------
    | Products & Inventory
    |--------------------------------------------------------------------------
    */
    Route::resource('products', ProductController::class)
        ->only(['index','create','store','edit','update','destroy']);
    Route::resource('categories', ProductCategoryController::class);
    Route::resource('units', UnitController::class);

    Route::get('products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download.template');
    Route::post('products/import-excel', [ProductController::class, 'importExcel'])->name('products.import.excel');
    Route::get('products/running-out', [ProductController::class, 'runningOut'])->name('products.running-out');
    Route::get('dashboard/products/expiring', [ProductController::class, 'expiring'])->name('products.expiring');
    Route::get('dashboard/products/finished', [ProductController::class, 'finished'])->name('products.finished');

    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */
    Route::resource('sales', SaleController::class)->only(['index', 'show']);
 
    // Admin
   Route::get('sales/{shop}/date/{date}', [SaleController::class, 'detail'])->name('admin.sales.detail');

    Route::get('sales/{shopId}/{date}/export-excel', [SaleController::class, 'exportExcel'])->name('sales.export.excel');
    Route::get('sales/{shopId}/{date}/export-pdf', [SaleController::class, 'exportPdf'])->name('sales.export.pdf');

    /*
    |--------------------------------------------------------------------------
    | Expenses & Fixed Expenses
    |--------------------------------------------------------------------------
    */
    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('{shop}', [ExpensesController::class, 'index'])->name('index');
        Route::post('{shop}', [ExpensesController::class, 'store'])->name('store');
        Route::get('{shop}/create', [ExpensesController::class, 'create'])->name('create');
        Route::get('{id}/edit', [ExpensesController::class, 'edit'])->name('edit');
        Route::put('{id}', [ExpensesController::class, 'update'])->name('update');
        Route::delete('{id}', [ExpensesController::class, 'destroy'])->name('destroy');
        Route::get('{shop}/detail', [ExpensesController::class, 'details'])->name('detail');
    });

    Route::prefix('fixed-expenses')->name('fixed-expenses.')->group(function () {
        Route::get('{shop}', [FixedExpensesController::class, 'index'])->name('index');
        Route::get('{shop}/create', [FixedExpensesController::class, 'create'])->name('create');
        Route::post('{shop}', [FixedExpensesController::class, 'store'])->name('store');
        Route::get('{id}/edit', [FixedExpensesController::class, 'edit'])->name('edit');
        Route::put('{id}', [FixedExpensesController::class, 'update'])->name('update');
        Route::delete('{id}', [FixedExpensesController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Purchases & Suppliers
    |--------------------------------------------------------------------------
    */
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchasesController::class, 'index'])->name('index');
        Route::get('/create', [PurchasesController::class, 'create'])->name('create');
        Route::post('/', [PurchasesController::class, 'store'])->name('store');
        Route::get('/shops/{shop}/purchases/{date}', [PurchasesController::class, 'detail'])->name('detail');
        Route::get('/products/new-item', [PurchasesController::class, 'newItem'])->name('products.new_item');
    });

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
    });

    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('transactions', [TransactionController::class, 'store'])->name('transactions.store');

    /*
    |--------------------------------------------------------------------------
    | Roles & Permissions
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('role');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

});
