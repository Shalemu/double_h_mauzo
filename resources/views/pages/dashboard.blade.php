<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    @php
        $shops = $shops ?? collect();
    @endphp

    @section('title', 'Dashboard')

    @include('components.header')
    @include('components/breadcrumb')
    @include('components/mainmenu')

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Hide sections by default except summary */
        .dashboard-section {
            display: none;
        }
        #shop-summary {
            display: block; /* summary visible by default */
        }
    </style>
</head>
<body>

    @include('components.sidebar')

    <div class="container-fluid main-content" id="main-content-area">
        <!-- =================== SHOP SUMMARY =================== -->
        <div id="shop-summary" class="dashboard-section">
            @include('pages.shops.show')
        </div>

        @php
            use App\Models\Products;
            use App\Models\ProductCategory;
            use App\Models\Unit;

            $products   = $products   ?? Products::with(['unit','category'])->get();
            $categories = $categories ?? ProductCategory::whereNull('parent_id')->get();
            $units      = $units      ?? Unit::all();
        @endphp

        <!-- =================== PRODUCTS =================== -->
        <div id="product-section" class="dashboard-section">
            @include('pages.products.index', [
                'products' => $products,
                'categories' => $categories,
                'units' => $units
            ])
        </div>

        <!-- =================== SALES =================== -->
        <div id="sale-section" class="dashboard-section">
            @include('pages.sales.index', [
                'sales' => $sales ?? collect(),
                'totalSales' => $totalSales ?? 0,
                'totalItems' => $totalItems ?? 0,
                'totalDiscount' => $totalDiscount ?? 0,
                'totalShipping' => $totalShipping ?? 0,
                'date' => $date ?? \Carbon\Carbon::today(),
            ])
        </div>

        <div id="credit-section" class="dashboard-section">
        @include('pages.credit.index', [
            'credits' => $credits ?? collect(),
            'creditByDate' => $creditByDate ?? collect(), 
            'dailyCredit' => $dailyCredit ?? 0,
            'monthlyCredit' => $monthlyCredit ?? 0, 
            'totalCredit' => $totalCredit ?? 0,
            'date' => $date ?? \Carbon\Carbon::today(),
        ])
        </div>


        <div id="order-section" class="dashboard-section">
        @include('pages.orders.index', [

        'orders' => $orders ?? collect(),
        'shop' => $shop ?? null

        ])
        </div>


        <!-- =================== EXPENSES =================== -->
        <div id="expense-section" class="dashboard-section">
            @include('pages.expenses.index', [
                'shop' => $shop,
                'expensesByDate' => $expensesByDate ?? collect()
            ])
        </div>
      
            <!-- =================== Fixed EXPENSES =================== -->
        <div id="fixed-expense-section" class="dashboard-section">
            @include('pages.fixed_expenses.index', [
                'shop' => $shop,
                'fixedExpenses' => $fixedExpenses ?? collect()
            ])
        </div>
        
        <div id="purchases-section" class="dashboard-section">
            @include('pages.purchases.index', [
                'shop' => $shop,
                'purchasesByDate' => $purchasesByDate ?? collect()
            ])
        </div>

        <div id="purchases-return-section" class="dashboard-section">
            @include('pages.purchase_returns.index', [
                'shop' => $shop,
                'purchasesByDate' => $purchasesByDate ?? collect()
            ])
        </div>

        <!-- =================== SALES RETURN =================== -->
        <div id="sales-return-section" class="dashboard-section">
            @include('pages.sales_returns.index', [
                'sales' => $sales ?? collect(),
                'totalSales' => $totalSales ?? 0,
                'totalItems' => $totalItems ?? 0,
                'totalDiscount' => $totalDiscount ?? 0,
                'totalShipping' => $totalShipping ?? 0,
                'date' => $date ?? \Carbon\Carbon::today(),
            ])
        </div>

        <div id="deleted_products-section" class="dashboard-section">
            @include('pages.deleted_product.index', [
                'products' => $deletedProducts, 
                'categories' => $categories, 
                'units' => $units
            ])
        </div>

        <div id="report_issue-section" class="dashboard-section">
        @include('pages.report_issue.index', [

        ])
        </div>


    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @include('components.dashboard_scripts')

    @include('pages._dashboard_scripts')
</body>
</html>
