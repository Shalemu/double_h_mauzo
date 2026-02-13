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

    @include('main')
    @include('components/breadcrumb')
    @include('components/mainmenu')

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        .main-content {
            position: relative;
            top: 50px; 
            margin-left: 70px;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                margin-top: 120px;
            }
        }

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
            @include('dashboard.shops.show')
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
            @include('dashboard.products.index', [
                'products' => $products,
                'categories' => $categories,
                'units' => $units
            ])
        </div>

        <!-- =================== SALES =================== -->
        <div id="sale-section" class="dashboard-section">
            @include('dashboard.sales.index', [
                'sales' => $sales ?? collect(),
                'totalSales' => $totalSales ?? 0,
                'totalItems' => $totalItems ?? 0,
                'totalDiscount' => $totalDiscount ?? 0,
                'totalShipping' => $totalShipping ?? 0,
                'date' => $date ?? \Carbon\Carbon::today(),
            ])
        </div>

        <!-- =================== EXPENSES =================== -->
        <div id="expense-section" class="dashboard-section">
            @include('dashboard.expenses.index', [
                'shop' => $shop,
                'expensesByDate' => $expensesByDate ?? collect()
            ])
        </div>
      
            <!-- =================== Fixed EXPENSES =================== -->
        <div id="fixed-expense-section" class="dashboard-section">
            @include('dashboard.fixed_expenses.index', [
                'shop' => $shop,
                'fixedExpenses' => $fixedExpenses ?? collect()
            ])
        </div>

    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.nav-item a');
            const sections = document.querySelectorAll('.dashboard-section');

            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active from all menu items
                    menuItems.forEach(i => i.parentElement.classList.remove('active'));
                    item.parentElement.classList.add('active');

                    // Hide all sections
                    sections.forEach(sec => sec.style.display = 'none');

                    // Show the selected section
                    const target = item.getAttribute('data-content');
                    if(target) {
                        const section = document.getElementById(target);
                        if(section) section.style.display = 'block';
                    }
                });
            });
        });
    </script>
</body>
</html>
