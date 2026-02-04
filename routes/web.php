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

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Redirect root to login
Route::get('/', fn () => redirect()->route('login'));

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisterController::class, 'register']);

// Export products to Excel
Route::get('products/export-excel', [ProductController::class, 'exportExcel'])
    ->name('products.export.excel');

// Export products to PDF
Route::get('products/export-pdf', [ProductController::class, 'exportPDF'])
    ->name('products.export.pdf');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    | Dashboard
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/home', [HomeController::class, 'index'])
        ->name('dashboard.index');


    /*
    | User / Profile
    */
    Route::get('/profile', [UserController::class, 'profile'])
        ->name('profile');

    Route::post('/update/{user_id}', [UserController::class, 'updateprofile'])
        ->name('updateprofile');

    Route::get('/changepassword', [UserController::class, 'changepassword'])
        ->name('changepassword');

    Route::post('/changePassword/{user_id}', [UserController::class, 'updatePassword'])
        ->name('changePassword');

    /*
    | Logout
    */
    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::get('/main/logout', [UserController::class, 'userlogout'])
        ->name('user.logout');


    /*
    |--------------------------------------------------------------------------
    | Shops
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard/shop', [DashboardController::class, 'shopDashboard'])
        ->name('dashboard.shop');

    // Shop CRUD
    Route::post('/shops', [ShopsController::class, 'store'])->name('shops.store');
    Route::get('/shops/{id}', [ShopsController::class, 'show'])->name('shops.show');

    // Shop index (all shops)
    Route::get('/dashboard/shop', [ShopsController::class, 'index'])->name('dashboard.shop');

    // Specific shop dashboard
    Route::get('/dashboard/shop/{id}', [DashboardController::class, 'showShop'])
        ->name('dashboard.shop.show');


    /*
    |--------------------------------------------------------------------------
    | Products & Inventory
    |--------------------------------------------------------------------------
    */

  // Download Excel template
Route::get('products/download-template', [ProductController::class, 'downloadTemplate'])
    ->name('products.download.template');

// Import products from Excel
Route::post('products/import-excel', [ProductController::class, 'importExcel'])
    ->name('products.import.excel');
    // Route::resource('products', ProductController::class);

    Route::get('products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download.template');

// Import Excel
    Route::post('products/import-excel', [ProductController::class, 'importExcel'])->name('products.import.excel');
     Route::resource('products', ProductController::class)->only(['index','create','store','edit','update','destroy']);

    Route::resource('categories', ProductCategoryController::class);
    Route::resource('units', UnitController::class);



    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');

    Route::get('/transactions/create', [TransactionController::class, 'create'])
        ->name('transactions.create');

    Route::post('/transactions', [TransactionController::class, 'store'])
        ->name('transactions.store');


    /*
    |--------------------------------------------------------------------------
    | Staff
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard/staff', [StaffController::class, 'index'])
        ->name('dashboard.staff');

    Route::post('/dashboard/staff', [StaffController::class, 'store'])
        ->name('staff.store');


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

    // export products
   

});
