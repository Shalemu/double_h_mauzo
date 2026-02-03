<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ShopsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Clean, structured routes for login, registration, dashboard, and users.
|
*/

// ------------------------
// Guest Routes (not logged in)
// ------------------------

// Default route: redirect guests to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Login page
Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');

// Handle login submission
Route::post('/login', [LoginController::class, 'login'])
    ->name('login.submit');

// Register page
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])
    ->middleware('guest')
    ->name('register');

// Handle registration submission
Route::post('/register', [RegisterController::class, 'register']);

// Password reset (lost password)
Route::get('/password/lost', 'ForgotPasswordController@forgotPassword')->name('password.lost');

// ------------------------
// Authenticated Routes (must be logged in)
// ------------------------
Route::middleware(['auth'])->group(function () {

    // Dashboard (admin only)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Change password
    Route::get('/changepassword', [UserController::class, 'changepassword'])->name('changepassword');
    Route::post('/updatepassword', [UserController::class, 'updatePassword'])->name('updatepassword');

    // Profile
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/user/profile', [UserController::class, 'profile']);

    // Update user
    Route::post('/update/{user_id}', [UserController::class, 'updateprofile'])->name('updateprofile');
    Route::post('/changePassword/{user_id}', [UserController::class, 'updatePassword'])->name('changePassword');

    // Pages resource
    Route::resource('pages', 'PagesController');

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/main/logout', [UserController::class, 'userlogout'])->name('user.logout');
});
// ------------------------
// Optional home route (if needed)
// ------------------------
Route::get('/home', [HomeController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard.index');

    //navigation
    // routes/web.php
Route::get('/dashboard/shop', function () {
    return view('dashboard.shops.shop');
// points to resources/views/dashboard/shps/shop.blade.php
})->name('dashboard.shop');

//staff
Route::get('/dashboard/staff', function () {
    return view('dashboard.staff.staff');
})->name('dashboard.staff');


//shop
Route::middleware(['auth'])->group(function () {

  // Add new shop
    Route::post('/shops', [ShopsController::class, 'store'])->name('shops.store');
});

Route::middleware(['auth'])->get('/dashboard/shop', [ShopsController::class, 'index'])->name('dashboard.shop');

// Route::middleware(['auth'])->get('/shops/{shop}', [ShopsController::class, 'show'])->name('shops.show');
Route::get('/dashboard/shop/{id}', [DashboardController::class, 'showShop'])->name('dashboard.shop.show');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Generic Shop dashboard (My Business link)
Route::get('/dashboard/shop', [DashboardController::class, 'shopDashboard'])->name('dashboard.shop');


// Show a specific shop by id (when clicking $shop->name)

Route::get('/shops/{id}', [ShopsController::class, 'show'])->name('shops.show');

// products

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');












//transactions
Route::middleware(['auth'])->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
});

//staff


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/staff', [StaffController::class, 'index'])
        ->name('dashboard.staff');

    Route::post('/dashboard/staff', [StaffController::class, 'store'])
        ->name('staff.store');
});



Route::middleware(['auth'])->prefix('dashboard')->name('dashboard.')->group(function () {

    // Role routes
    Route::get('roles', [RoleController::class, 'index'])->name('role');  // list roles
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store'); // store
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update'); // update
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy'); // delete

});







