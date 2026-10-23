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
use App\Http\Controllers\BrandController;

// Management Controllers
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SuperAdminController;

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

// Sale return
use App\Http\Controllers\SaleReturnController;

// Purchase return
use App\Http\Controllers\PurchaseReturnController;

use App\Http\Controllers\PurchaseInvoiceController;

use App\Http\Controllers\OrdersController;

use App\Http\Controllers\FeedbackController;

use App\Http\Controllers\SalesRevenueController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ReturnsController;
use App\Http\Controllers\AdminOverviewController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SmsController;

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

// In-app notifications (admins and staff)
Route::middleware('auth:web,staff')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/{id}', [OrdersController::class, 'openNotification'])->name('open');
    Route::post('/read-all', [OrdersController::class, 'readAllNotifications'])->name('readAll');
});


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
        Route::get('/products', [ProductController::class, 'index'])
            ->middleware('permission:products.view')->name('products.index');

        // Customers
       Route::prefix('customers')->name('customers.')->middleware('permission:customers.manage')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/manage', [CustomerController::class, 'manage'])->name('manage');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');

            // Record Payment for a specific customer
            Route::post('/{customer}/record-payment', [CustomerController::class, 'recordPayment'])->name('recordPayment');
        });



        // Sales
        Route::post('sales/checkout/{shop}', [SaleController::class, 'checkout'])
            ->middleware('permission:pos.sell')->name('sales.checkout');

        Route::prefix('sales')->middleware('permission:sales.view')->group(function () {
        Route::get('/{shop}', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/{shop}/{date}', [SaleController::class, 'detail'])->name('sales.detail');
        Route::get('/{shop}/{date}/export-excel', [SaleController::class, 'exportExcel'])->name('sales.export.excel');
        Route::get('/{shop}/{date}/export-pdf', [SaleController::class, 'exportPdf'])->name('sales.export.pdf');
        Route::get('sales/{shop}/export/excel/{date}', [SaleController::class, 'exportExcel'])->name('sales.exportExcel');
        Route::get('sales/{shop}/export/pdf/{date}', [SaleController::class, 'exportPdf'])->name('sales.exportPdf');
    });

        // Cart
        Route::prefix('cart')->middleware('permission:pos.sell')->group(function () {
            Route::get('/', [CartController::class, 'getCart'])->name('cart.get');
            Route::post('/add', [CartController::class, 'add'])->name('cart.add');
            Route::post('/update', [CartController::class, 'updateCart'])->name('cart.update');
            Route::post('/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
        });

        // Expenses
        Route::prefix('expenses')->name('expenses.')->middleware('permission:expenses.manage')->group(function () {
            Route::get('{shop}', [ExpensesController::class, 'index'])->name('index');
            Route::get('{shop}/create', [ExpensesController::class, 'create'])->name('create');
            Route::get('{shop}/detail', [ExpensesController::class, 'details'])->name('detail');
            Route::post('{shop}', [ExpensesController::class, 'store'])->name('store');
            Route::get('{id}/edit', [ExpensesController::class, 'edit'])->name('edit');
            Route::put('{id}', [ExpensesController::class, 'update'])->name('update');
            Route::delete('{id}', [ExpensesController::class, 'destroy'])->name('destroy');
        });

        // Transactions
        Route::post('/transaction/checkout', [TransactionController::class, 'checkout'])
            ->middleware('permission:pos.sell')->name('transaction.checkout');


    // Orders (stock requests sent to admin for approval)
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::middleware('permission:orders.create')->group(function () {
                Route::get('/create', [OrdersController::class, 'create'])->name('create');
                Route::get('/product-search', [OrdersController::class, 'searchProducts'])->name('productSearch');
                Route::post('/store', [OrdersController::class, 'store'])->name('store');
            });
            Route::middleware('permission:orders.view')->group(function () {
                Route::get('/', [OrdersController::class, 'index'])->name('index');
                Route::get('/my-orders', [OrdersController::class, 'myOrders'])->name('my_orders');
                Route::get('/{order}', [OrdersController::class, 'show'])->name('show');
            });
        });


//    feedback
        Route::prefix('report-issue')->name('report.issue.')->middleware('permission:issues.report')->group(function () {
            Route::get('/', [FeedbackController::class, 'index'])->name('index');
            Route::post('/', [FeedbackController::class, 'store'])->name('store');
        });



    });

/*
|--------------------------------------------------------------------------
| Admin & Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth', 'panel.permission'])->group(function () {

    // Redirect /dashboard
    Route::get('/dashboard', function () {
        if (Auth::guard('staff')->check()) return redirect()->route('staff.dashboard');
        return redirect()->route('dashboard.admin');
    })->name('dashboard');

    // Admin Dashboard
    Route::get('/dashboard/admin', [DashboardController::class, 'index'])
        ->middleware('admin')->name('dashboard.admin');

    /*
    |--------------------------------------------------------------------------
    | Admin POS — admin-tier accounts can sell directly, across any shop
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/admin/pos')
        ->middleware('admin')
        ->name('admin.pos.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'adminPos'])->name('index');
            Route::post('/checkout/{shop}', [SaleController::class, 'checkout'])->name('checkout');
            Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        });

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
    | Super Admin: User Approval & Role Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/admin/users')
        ->middleware('superadmin')
        ->name('users.manage.')
        ->group(function () {
            Route::get('/', [SuperAdminController::class, 'index'])->name('index');
            Route::put('/{user}/approve', [SuperAdminController::class, 'approve'])->name('approve');
            Route::delete('/{user}/reject', [SuperAdminController::class, 'reject'])->name('reject');
            Route::put('/{user}/role', [SuperAdminController::class, 'updateRole'])->name('role');
            Route::put('/{user}/super-user', [SuperAdminController::class, 'toggleSuperUser'])->name('superUser');
        });

    /*
    |--------------------------------------------------------------------------
    | Shops
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard/shop', [ShopsController::class, 'index'])
        ->middleware('admin')->name('dashboard.shop');
    Route::post('/shops', [ShopsController::class, 'store'])
        ->middleware('admin')->name('shops.store');
    Route::get('/dashboard/shop/{shop}', [ShopsController::class, 'show'])
        ->middleware('admin')->name('dashboard.shop.show');
    Route::put('/shops/{shop}', [ShopsController::class, 'update'])
        ->middleware('admin')->name('shops.update');
    Route::delete('/shops/{shop}', [ShopsController::class, 'destroy'])
        ->middleware('admin')->name('shops.destroy');
    Route::patch('/shops/{shop}/toggle-status', [ShopsController::class, 'toggleStatus'])
        ->middleware('admin')->name('shops.toggleStatus');

    // Stores (warehouses) — separate module from shops
    Route::prefix('dashboard/stores')->middleware('admin')->name('stores.')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::post('/', [StoreController::class, 'store'])->name('store');
        Route::get('/{store}', [StoreController::class, 'show'])->name('show');
        Route::put('/{store}', [StoreController::class, 'update'])->name('update');
        Route::patch('/{store}/toggle-status', [StoreController::class, 'toggleStatus'])->name('toggleStatus');
        Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Active Shop Switcher (admin-tier)
    |--------------------------------------------------------------------------
    */
    Route::post('/dashboard/admin/active-shop', [ShopsController::class, 'setActiveShop'])
        ->middleware('admin')->name('admin.activeShop.set');
    Route::delete('/dashboard/admin/active-shop', [ShopsController::class, 'clearActiveShop'])
        ->middleware('admin')->name('admin.activeShop.clear');

    /*
    |--------------------------------------------------------------------------
    | Products & Inventory
    |--------------------------------------------------------------------------
    */
    Route::get('products/in-stock', [ProductController::class, 'index'])
        ->defaults('filter', 'in_stock')->name('products.in-stock');
    Route::get('products/expired', [ProductController::class, 'index'])
        ->defaults('filter', 'expired')->name('products.expired');
    Route::get('products/out-of-stock', [ProductController::class, 'index'])
        ->defaults('filter', 'out_of_stock')->name('products.out-of-stock');

    Route::resource('products', ProductController::class)
        ->only(['index','create','store','edit','update','destroy']);
    Route::resource('categories', ProductCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('units', UnitController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('brands', BrandController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::post('products/bulk-assign-shop', [ProductController::class, 'bulkAssignShop'])
        ->middleware('admin')->name('products.bulkAssignShop');
    Route::get('products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download.template');
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::get('products/export-pdf', [ProductController::class, 'exportPDF'])->name('products.export.pdf');
    Route::post('products/import-excel', [ProductController::class, 'importExcel'])->name('products.import.excel');
    Route::get('products/running-out', [ProductController::class, 'index'])
        ->defaults('filter', 'running_out')->name('products.running-out');
    Route::get('dashboard/products/expiring', [ProductController::class, 'index'])
        ->defaults('filter', 'expiring')->name('products.expiring');
    Route::get('dashboard/products/finished', [ProductController::class, 'index'])
        ->defaults('filter', 'out_of_stock')->name('products.finished');

    // trash

    Route::prefix('deleted-products')->group(function () {
        Route::get('/', [ProductController::class, 'trash'])->name('products.trash');               // Trash page
        Route::post('/restore/{id}', [ProductController::class, 'restore'])->name('products.restore');  // Restore
        Route::delete('/force-delete/{id}', [ProductController::class, 'forceDelete'])->name('products.forceDelete'); // Force delete
    });
    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */
    Route::resource('sales', SaleController::class)->only(['index', 'show']);
// ================= SALES RETURNS =================


       Route::get('sales_returns/{shop}/date/{date}', [SaleReturnController::class, 'detail'])->name('admin.sales_returns.detail');
 
    // Admin
   Route::get('sales/{shop}/date/{date}', [SaleController::class, 'detail'])->name('admin.sales.detail');
   Route::post('sales-returns', [SaleReturnController::class, 'store'])
    ->name('sales-returns.store');

// ================= PURCHASE RETURNS =================

    Route::get('purchase_returns/{shop}/date/{date}', [PurchaseReturnController::class, 'detail'])->name('admin.purchase_returns.detail');

    Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])
    ->name('purchase-returns.store');

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
        Route::get('shop/{shop}', [FixedExpensesController::class, 'index'])->name('index');
        Route::post('shop/{shop}', [FixedExpensesController::class, 'store'])->name('store');
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
    Route::get('/supplier/{supplier}/products', [PurchasesController::class, 'supplierProducts'])->name('supplier_products');
    Route::post('/', [PurchasesController::class, 'store'])->name('store');
    Route::get('/shops/{shop}/purchases/{date}', [PurchasesController::class, 'detail'])->name('detail');
    Route::get('/history/{invoice}', [PurchasesController::class, 'history'])->name('history');
    Route::get('/orders/{invoice}/receive', [PurchasesController::class, 'receive'])->name('receive');
    Route::post('/orders/{invoice}/receive', [PurchasesController::class, 'confirmReceive'])->name('confirm_receive');
    Route::post('/orders/{invoice}/cancel', [PurchasesController::class, 'cancel'])->name('cancel');
    Route::get('/print/{invoice}', [PurchasesController::class, 'print'])->name('print');
    Route::get('/invoice/{invoice}/pdf', [PurchasesController::class, 'invoicePdf'])->name('invoice_pdf');
    Route::get('/invoice/{invoice}/preview', [PurchasesController::class, 'invoicePreview'])->name('invoice_preview');
    Route::post('/invoice/{invoice}/email', [PurchasesController::class, 'emailInvoice'])->name('invoice_email');

    Route::get('/new-product', [PurchasesController::class, 'newProduct'])->name('new_product');
    Route::post('/new-product', [PurchasesController::class, 'storeNewProduct'])->name('store_new_product');
});

    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Customers (Admin view — all shops, credit/debt tracking)
    |--------------------------------------------------------------------------
    */
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'adminIndex'])->name('index');
        Route::get('/{customer}', [CustomerController::class, 'adminShow'])->name('show');
        Route::post('/{customer}/record-payment', [CustomerController::class, 'recordPayment'])->name('recordPayment');
    });

    // credit

    Route::get('/dashboard/purchases/credit', [ShopsController::class, 'purchaseCredits'])
    ->name('purchases.credit');

    //depost
    Route::post('/purchases/deposit', [PurchasesController::class, 'deposit'])
    ->name('purchases.deposit');

    // purchasing invoice
  
   
    Route::get('/invoices', [PurchaseInvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [PurchaseInvoiceController::class, 'show'])->name('invoices.show');



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
    Route::prefix('dashboard')->name('dashboard.')->middleware('admin')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('role');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Sales & Revenue (admin)
    |--------------------------------------------------------------------------
    */
    Route::prefix('dashboard/admin')->middleware('admin')->group(function () {
        Route::get('/sales', [SalesRevenueController::class, 'sales'])->name('admin.sales.index');
        Route::get('/sales/{sale}', [SalesRevenueController::class, 'show'])->name('admin.sales.show');
        Route::get('/sale-items', [SalesRevenueController::class, 'items'])->name('admin.sales.items');
        Route::get('/revenue', [SalesRevenueController::class, 'revenue'])->name('admin.revenue');

        // Procurement
        Route::get('/supplier-products', [ProcurementController::class, 'supplierProducts'])->name('procurement.supplierProducts');
        Route::get('/supplier-products/product-lookup', [ProcurementController::class, 'productLookup'])->name('procurement.supplierProducts.lookup');
        Route::post('/supplier-products', [ProcurementController::class, 'assignProduct'])->name('procurement.supplierProducts.assign');
        Route::post('/supplier-products/{assignment}/apply-price', [ProcurementController::class, 'applySupplierPrice'])->name('procurement.supplierProducts.applyPrice');
        Route::delete('/supplier-products/{assignment}', [ProcurementController::class, 'removeAssignment'])->name('procurement.supplierProducts.remove');
        Route::get('/price-manager', [ProcurementController::class, 'priceManager'])->name('procurement.prices');
        Route::put('/price-manager', [ProcurementController::class, 'updatePrices'])->name('procurement.prices.update');

        // Returns (recording still posts to sales-returns.store / purchase-returns.store)
        Route::get('/sales-returns', [ReturnsController::class, 'sales'])->name('returns.sales');
        Route::get('/sales-returns/sale/{sale}', [ReturnsController::class, 'createSaleReturn'])->name('returns.sales.create');
        Route::post('/sales-returns/sale/{sale}', [ReturnsController::class, 'storeSaleReturn'])->name('returns.sales.store');
        Route::get('/purchase-returns', [ReturnsController::class, 'purchases'])->name('returns.purchases');
        Route::get('/purchase-returns/invoice/{invoice}', [ReturnsController::class, 'createPurchaseReturn'])->name('returns.purchases.create');
        Route::post('/purchase-returns/invoice/{invoice}', [ReturnsController::class, 'storePurchaseReturn'])->name('returns.purchases.store');
        Route::get('/returns/sale-items', [ReturnsController::class, 'saleItemLookup'])->name('returns.saleItemLookup');
        Route::get('/returns/purchase-items', [ReturnsController::class, 'purchaseItemLookup'])->name('returns.purchaseItemLookup');

        // All-shop overviews
        Route::get('/credit', [AdminOverviewController::class, 'credit'])->name('admin.credit');
        Route::get('/expenses', [AdminOverviewController::class, 'expenses'])->name('admin.expenses');
        Route::get('/fixed-expenses', [AdminOverviewController::class, 'fixedExpenses'])->name('admin.fixedExpenses');
        Route::get('/issues', [AdminOverviewController::class, 'issues'])->name('admin.issues');

        // Stock transfers between shops
        Route::get('/stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
        Route::get('/stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create');
        Route::post('/stock-transfers', [StockTransferController::class, 'store'])->name('stock-transfers.store');
        Route::get('/stock-transfers/product-search', [StockTransferController::class, 'productSearch'])->name('stock-transfers.productSearch');
        Route::get('/stock-transfers/{transfer}', [StockTransferController::class, 'show'])->name('stock-transfers.show');

        // SMS to customers / staff (Mobishastra)
        Route::get('/sms', [SmsController::class, 'index'])->name('sms.index');
        Route::post('/sms', [SmsController::class, 'send'])->name('sms.send');

        // Distributors taking stock out of a store and settling when they come back
        Route::get('/distributions', [DistributionController::class, 'index'])->name('distributions.index');
        Route::post('/distributions', [DistributionController::class, 'store'])->name('distributions.store');
        Route::get('/distributions/product-search', [DistributionController::class, 'productSearch'])->name('distributions.productSearch');
        Route::get('/distributions/{distribution}', [DistributionController::class, 'show'])->name('distributions.show');
        Route::post('/distributions/{distribution}/settle', [DistributionController::class, 'settle'])->name('distributions.settle');
        Route::post('/distributions/{distribution}/payments', [DistributionController::class, 'pay'])->name('distributions.pay');
    });

Route::prefix('dashboard/admin')->name('admin.')->middleware('admin')->group(function() {
    Route::get('/shops/{shop}/orders', [ShopsController::class, 'orders'])->name('shops.orders');

    // Order requests raised by staff
    Route::get('/orders', [OrdersController::class, 'adminIndex'])->name('orders.index');
    Route::get('/orders/{order}', [OrdersController::class, 'adminShow'])
    ->name('orders.show');

    Route::put('/orders/{order}/status', [OrdersController::class, 'updateStatus'])
        ->name('orders.updateStatus');
});

    //view feedback
Route::patch('/admin/feedback/{feedback}/resolve', [FeedbackController::class, 'resolve'])
    ->name('admin.feedback.resolve')
    ->middleware('auth:web');


});
