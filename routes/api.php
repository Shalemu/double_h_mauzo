<?php

<<<<<<< Updated upstream
use Illuminate\Http\Request;
=======
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\ShopController;
use Illuminate\Support\Facades\Route;
>>>>>>> Stashed changes

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

<<<<<<< Updated upstream
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
=======
// Open: no token yet. 5 tries a minute per account+IP stops password
// guessing (limiter 'api-login' in AppServiceProvider).
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Shared by staff and admins.
    Route::middleware('ability:staff,admin')->group(function () {

        // Products — sellers (pos.sell / admin-pos.sell) and stock viewers
        // (products.view / products.list). Admins with full access always pass.
        Route::middleware('permission:pos.sell,products.view,admin-pos.sell,products.list')->group(function () {
            Route::get('/products', [ProductController::class, 'index']);
            Route::get('/products/summary', [ProductController::class, 'summary']);
            Route::get('/products/barcode/{code}', [ProductController::class, 'barcode']);
            Route::get('/products/{id}', [ProductController::class, 'show'])->whereNumber('id');
        });

        // Customers — anyone who sells may look up and add customers (as on the web POS).
        Route::middleware('permission:customers.manage,customers.view,pos.sell,admin-pos.sell')->group(function () {
            Route::get('/customers', [CustomerController::class, 'index']);
            Route::get('/customers/{id}', [CustomerController::class, 'show'])->whereNumber('id');
        });
        Route::post('/customers', [CustomerController::class, 'store'])
            ->middleware('permission:customers.manage,pos.sell,admin-pos.sell');

        // Shops this person can work in (for shop pickers in the app).
        Route::get('/shops', [ShopController::class, 'index']);

        // Expenses — same permissions as the web Expenses page.
        Route::get('/expenses', [ExpenseController::class, 'index'])
            ->middleware('permission:expenses.manage,expenses.view,expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])
            ->middleware('permission:expenses.manage,expenses.create');

        // Distributor out with stock: their trip, products they carry and sales so far.
        Route::get('/trip', [TripController::class, 'show'])->middleware('permission:pos.sell');

        // Payment methods to offer at checkout (Settings › Payment Methods).
        Route::get('/payment-methods', [PaymentMethodController::class, 'index'])
            ->middleware('permission:pos.sell,admin-pos.sell,sales.view,sales.list');

        // Sales — checkout needs a selling permission; history/receipts also
        // open to sellers so a cashier can reprint a receipt.
        Route::post('/sales', [SaleController::class, 'store'])
            ->middleware('permission:pos.sell,admin-pos.sell');
        Route::middleware('permission:sales.view,sales.list,pos.sell,admin-pos.sell')->group(function () {
            Route::get('/sales', [SaleController::class, 'index']);
            Route::get('/sales/{id}', [SaleController::class, 'show'])->whereNumber('id');
        });
    });

    // Only for staff tokens (none yet).
    Route::middleware('abilities:staff')->prefix('staff')->group(function () {
        //
    });

    // Only for admin tokens (none yet).
    Route::middleware('abilities:admin')->prefix('admin')->group(function () {
        //
    });
>>>>>>> Stashed changes
});
