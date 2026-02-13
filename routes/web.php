<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

// Core
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;

// Business
use App\Http\Controllers\ShopsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\UnitController;

// Management
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RoleController;

// Transactions
use App\Http\Controllers\TransactionController;

// Cart
use App\Http\Controllers\CartController;

// Reports
use App\Http\Controllers\ReportController;

// Customer
use App\Http\Controllers\CustomerController;

// Sales
use App\Http\Controllers\SaleController;

// Expenses
use App\Http\Controllers\ExpensesController;

// TixedExpenses
use App\Http\Controllers\FixedExpensesController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', fn () => redirect()->route('login'));

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest:web,staff')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisterController::class, 'register']);

// Product exports
Route::get('products/export-excel', [ProductController::class, 'exportExcel'])
    ->name('products.export.excel');

Route::get('products/export-pdf', [ProductController::class, 'exportPDF'])
    ->name('products.export.pdf');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {

    // Staff routes
    Route::middleware('staff')->group(function () {

        // Dashboard
        Route::get('/dashboard/dashboard_staff', [DashboardController::class, 'staff'])
            ->name('staff.dashboard');

        // Customers
        Route::post('/customers', [CustomerController::class, 'store'])
            ->name('customers.store');

        // Sales Checkout
        Route::post('/sales/checkout', [SaleController::class, 'checkout'])
            ->name('sales.checkout');

        // Cart
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::get('/cart', [CartController::class, 'getCart']);
        Route::post('/cart/update', [CartController::class, 'updateCart']);
        Route::post('/cart/remove', [CartController::class, 'removeFromCart']);

        // Transactions
        Route::post('/transaction/checkout', [TransactionController::class, 'checkout'])
            ->name('transaction.checkout');
    });

    // Admin & Authenticated user routes
    Route::middleware('auth')->group(function () {

        // Admin Dashboard
        Route::get('/dashboard/admin', [DashboardController::class, 'index'])
            ->middleware('admin')
            ->name('dashboard.admin');

        // Home
        Route::get('/home', [HomeController::class, 'index'])
            ->name('dashboard.index');

        // User / Profile
        Route::get('/profile', [UserController::class, 'profile'])
            ->name('profile');

        Route::post('/update/{user_id}', [UserController::class, 'updateprofile'])
            ->name('updateprofile');

        Route::get('/changepassword', [UserController::class, 'changepassword'])
            ->name('changepassword');

        Route::post('/changePassword/{user_id}', [UserController::class, 'updatePassword'])
            ->name('changePassword');

        Route::get('/main/logout', [UserController::class, 'userlogout'])
            ->name('user.logout');

        /*
        |----------------------------------------------------------------------
        | Shops
        |----------------------------------------------------------------------
        */
 
Route::get('/dashboard/shop', [ShopsController::class, 'index'])
    ->name('dashboard.shop');

Route::post('/shops', [ShopsController::class, 'store'])
    ->name('shops.store');

Route::get('/dashboard/shop/{shop}', [ShopsController::class, 'show'])
    ->name('dashboard.shop.show');

    Route::get('/shops/{shop}', [ShopsController::class, 'show'])
    ->name('shops.show');


        /*
        |----------------------------------------------------------------------
        | Products & Inventory
        |----------------------------------------------------------------------
        */
        Route::get('products/download-template', [ProductController::class, 'downloadTemplate'])
            ->name('products.download.template');

        Route::post('products/import-excel', [ProductController::class, 'importExcel'])
            ->name('products.import.excel');

        Route::resource('products', ProductController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        Route::resource('categories', ProductCategoryController::class);
        Route::resource('units', UnitController::class);


        // filterByStatus

        Route::get('/products/running-out', [ProductController::class, 'runningOut'])
     ->name('products.running-out');
     Route::get('/dashboard/products/expiring', [ProductController::class, 'expiring'])->name('products.expiring');
     Route::get('/dashboard/products/finished', [ProductController::class, 'finished'])->name('products.finished');





        /*
        |----------------------------------------------------------------------
        | Sales
        |----------------------------------------------------------------------
        */
       Route::get('sales/{shop}/date/{date}', [SaleController::class, 'detail'])->name('sales.detail');
       Route::resource('sales', SaleController::class)->only(['index', 'show']); 
      


    //    export
    // routes/web.php
        Route::get('sales/{shopId}/{date}/export-excel', [SaleController::class, 'exportExcel'])->name('sales.export.excel');
        Route::get('sales/{shopId}/{date}/export-pdf', [SaleController::class, 'exportPdf'])->name('sales.export.pdf');

        /*
        |----------------------------------------------------------------------
        | Expenses
        |----------------------------------------------------------------------
        */

    Route::prefix('expenses')->group(function () {
    Route::get('{shop}', [ExpensesController::class, 'index'])->name('expenses.index');
    Route::post('{shop}', [ExpensesController::class, 'store'])->name('expenses.store'); // <- store route

    Route::get('{shop}/create', [ExpensesController::class, 'create'])->name('expenses.create');
    Route::get('{id}/edit', [ExpensesController::class, 'edit'])->name('expenses.edit');
    Route::put('{id}', [ExpensesController::class, 'update'])->name('expenses.update');
    Route::delete('{id}', [ExpensesController::class, 'destroy'])->name('expenses.destroy');
    Route::get('{shop}/detail', [ExpensesController::class, 'details'])->name('expenses.detail');
});

Route::prefix('fixed-expenses')->group(function () {
    Route::get('{shop}', [FixedExpensesController::class, 'index'])->name('fixed-expenses.index');
    Route::get('{shop}/create', [FixedExpensesController::class, 'create'])->name('fixed-expenses.create');
    Route::post('{shop}', [FixedExpensesController::class, 'store'])->name('fixed-expenses.store');
    Route::get('{id}/edit', [FixedExpensesController::class, 'edit'])->name('fixed-expenses.edit');
    Route::put('{id}', [FixedExpensesController::class, 'update'])->name('fixed-expenses.update');
    Route::delete('{id}', [FixedExpensesController::class, 'destroy'])->name('fixed-expenses.destroy');
});


        /*
        |----------------------------------------------------------------------
        | Transactions
        |----------------------------------------------------------------------
        */
        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions.index');

        Route::get('/transactions/create', [TransactionController::class, 'create'])
            ->name('transactions.create');

        Route::post('/transactions', [TransactionController::class, 'store'])
            ->name('transactions.store');

        /*
        |----------------------------------------------------------------------
        | Staff
        |----------------------------------------------------------------------
        */
   Route::prefix('dashboard/staff')->name('staff.')->group(function() {
    Route::get('/', [StaffController::class, 'index'])->name('index');
    Route::post('/', [StaffController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [StaffController::class, 'edit'])->name('edit');
    Route::put('/{id}', [StaffController::class, 'update'])->name('update');
    Route::delete('/{id}', [StaffController::class, 'destroy'])->name('destroy');
});


        /*
        |----------------------------------------------------------------------
        | Roles & Permissions
        |----------------------------------------------------------------------
        */
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('role');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });

    });

});
