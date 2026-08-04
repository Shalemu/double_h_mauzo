<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>

    <?php
        $shops = $shops ?? collect();
    ?>

    <?php $__env->startSection('title', 'Dashboard'); ?>

    <?php echo $__env->make('components.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

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

    <?php echo $__env->make('components.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="container-fluid main-content" id="main-content-area">
        <!-- =================== SHOP SUMMARY =================== -->
        <div id="shop-summary" class="dashboard-section">
            <?php echo $__env->make('dashboard.shops.show', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <?php
            use App\Models\Products;
            use App\Models\ProductCategory;
            use App\Models\Unit;

            $products   = $products   ?? Products::with(['unit','category'])->get();
            $categories = $categories ?? ProductCategory::whereNull('parent_id')->get();
            $units      = $units      ?? Unit::all();
        ?>

        <!-- =================== PRODUCTS =================== -->
        <div id="product-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.products.index', [
                'products' => $products,
                'categories' => $categories,
                'units' => $units
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- =================== SALES =================== -->
        <div id="sale-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.sales.index', [
                'sales' => $sales ?? collect(),
                'totalSales' => $totalSales ?? 0,
                'totalItems' => $totalItems ?? 0,
                'totalDiscount' => $totalDiscount ?? 0,
                'totalShipping' => $totalShipping ?? 0,
                'date' => $date ?? \Carbon\Carbon::today(),
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <div id="credit-section" class="dashboard-section">
        <?php echo $__env->make('dashboard.credit.index', [
            'credits' => $credits ?? collect(),
            'creditByDate' => $creditByDate ?? collect(), 
            'dailyCredit' => $dailyCredit ?? 0,
            'monthlyCredit' => $monthlyCredit ?? 0, 
            'totalCredit' => $totalCredit ?? 0,
            'date' => $date ?? \Carbon\Carbon::today(),
        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>


        <div id="order-section" class="dashboard-section">
        <?php echo $__env->make('dashboard.orders.index', [

        'orders' => $orders ?? collect(),
        'shop' => $shop ?? null

        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>


        <!-- =================== EXPENSES =================== -->
        <div id="expense-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.expenses.index', [
                'shop' => $shop,
                'expensesByDate' => $expensesByDate ?? collect()
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
      
            <!-- =================== Fixed EXPENSES =================== -->
        <div id="fixed-expense-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.fixed_expenses.index', [
                'shop' => $shop,
                'fixedExpenses' => $fixedExpenses ?? collect()
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
        
        <div id="purchases-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.purchases.index', [
                'shop' => $shop,
                'purchasesByDate' => $purchasesByDate ?? collect()
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <div id="purchases-return-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.purchase_returns.index', [
                'shop' => $shop,
                'purchasesByDate' => $purchasesByDate ?? collect()
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <!-- =================== SALES RETURN =================== -->
        <div id="sales-return-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.sales_returns.index', [
                'sales' => $sales ?? collect(),
                'totalSales' => $totalSales ?? 0,
                'totalItems' => $totalItems ?? 0,
                'totalDiscount' => $totalDiscount ?? 0,
                'totalShipping' => $totalShipping ?? 0,
                'date' => $date ?? \Carbon\Carbon::today(),
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <div id="deleted_products-section" class="dashboard-section">
            <?php echo $__env->make('dashboard.deleted_product.index', [
                'products' => $deletedProducts, 
                'categories' => $categories, 
                'units' => $units
            ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>

        <div id="report_issue-section" class="dashboard-section">
        <?php echo $__env->make('dashboard.report_issue.index', [

        ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>


    </div>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php echo $__env->make('components.dashboard_scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.nav-item a');
            const sections = document.querySelectorAll('.dashboard-section');
            const storageKey = 'dashboard-active-section';

            function activateSection(target) {
                if (!target || !document.getElementById(target)) return false;

                menuItems.forEach(i => i.parentElement.classList.remove('active'));
                sections.forEach(sec => sec.style.display = 'none');

                document.getElementById(target).style.display = 'block';

                const matchingItem = Array.from(menuItems).find(i => i.getAttribute('data-content') === target);
                if (matchingItem) matchingItem.parentElement.classList.add('active');

                return true;
            }

            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    const target = item.getAttribute('data-content');
                    if (activateSection(target)) {
                        localStorage.setItem(storageKey, target);
                    }
                });
            });

            // Restore whichever section was open before the page was refreshed
            const savedTarget = localStorage.getItem(storageKey);
            if (savedTarget) activateSection(savedTarget);
        });
    </script>
</body>
</html>
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/dashboard.blade.php ENDPATH**/ ?>