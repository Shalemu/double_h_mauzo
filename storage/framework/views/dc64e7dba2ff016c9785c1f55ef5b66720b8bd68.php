<?php $__env->startSection('title', 'Dashboard'); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<div class="cat__content">
    <br><br>


<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap" style="gap: 12px; padding-left: 50px;">
        <button type="button" class="btn btn-outline-danger">
            <i class="bi bi-cart-plus"></i> Sales
        </button>
        <button type="button" class="btn btn-outline-success">
            <i class="bi bi-bag-plus"></i> Purchases
        </button>
        <button type="button" class="btn btn-outline-warning text-dark">
            <i class="bi bi-cash-stack"></i> Expenses
        </button>
        <button type="button" class="btn btn-outline-primary">
            <i class="bi bi-shop"></i> My Shops
        </button>
        <button type="button" class="btn btn-outline-secondary">
            <i class="bi bi-geo-alt"></i> Salepoints
        </button>
        <button type="button" class="btn btn-outline-info text-dark">
            <i class="bi bi-people"></i> My Customers
        </button>
        <button type="button" class="btn btn-outline-warning text-dark">
            <i class="bi bi-person-badge"></i> My Employees
        </button>
    </div>
</div>

    <!-- Use container-fluid to reduce side gap -->
   <div class="container-fluid">
    <div class="row g-4" style="padding-left: 30px; padding-right: 30px;">

        <!-- Gross Profit -->
        <div class="col-lg-3 col-md-6">
            <div class="cat__core__widget">
                <div class="cat__core__step cat__core__step--success">
                    <span class="cat__core__step__digit">
                        <i class="icmn-database"></i>
                    </span>
                    <div class="cat__core__step__desc">
                        <span class="cat__core__step__title">Gross Profit (All Shops)</span>
                        <p>Tz: <?php echo e(number_format($grossProfit, 2)); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-lg-3 col-md-6">
            <div class="cat__core__widget">
                <div class="cat__core__step cat__core__step--primary">
                    <span class="cat__core__step__digit">
                        <i class="icmn-users"></i>
                    </span>
                    <div class="cat__core__step__desc">
                        <span class="cat__core__step__title">Total Expenses (All Shops)</span>
                        <p>Tz: <?php echo e(number_format($totalExpenses, 2)); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="col-lg-3 col-md-6">
            <div class="cat__core__widget">
                <div class="cat__core__step cat__core__step--danger">
                    <span class="cat__core__step__digit">
                        <i class="icmn-bullhorn"></i>
                    </span>
                    <div class="cat__core__step__desc">
                        <span class="cat__core__step__title">Net Profit</span>
                        <p>Tz: <?php echo e(number_format($netProfit, 2)); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales -->
        <div class="col-lg-3 col-md-6">
            <div class="cat__core__widget">
                <div class="cat__core__step cat__core__step--default">
                    <span class="cat__core__step__digit">
                        <i class="icmn-price-tags"></i>
                    </span>
                    <div class="cat__core__step__desc">
                        <span class="cat__core__step__title">Sales (All Shops)</span>
                        <p>Tz: <?php echo e(number_format($totalSales, 2)); ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</div>


<div class="cat__content">
    <div class="container-fluid">
        <div class="row g-4" style="padding-left:30px; padding-right:30px;">

            <!-- Left: Product Metrics -->
            <div class="col-xl-5"> <!-- Increased width -->
                <div class="cat__core__widget p-3 h-100" style="background:#fff;">
                    <strong>Total Products:</strong>
                    <p class="text-muted">All products available in the shop</p>
                    <div class="progress mb-3" style="height: 10px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 100%"></div>
                    </div>

                    <strong>Remaining Products:</strong>
                    <p class="text-muted">Products still available for sale</p>
                    <div class="progress mb-3" style="height: 10px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 75%"></div>
                    </div>

                    <strong>Expired Products:</strong>
                    <p class="text-muted">Products that passed expiration date</p>
                    <div class="progress mb-3" style="height: 10px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 10%"></div>
                    </div>

                    <strong>Disposed Products:</strong>
                    <p class="text-muted">Products removed from inventory</p>
                    <div class="progress mb-3" style="height: 10px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 5%"></div>
                    </div>

                    <strong>Running Out Products:</strong>
                    <p class="text-muted">Products with low stock</p>
                    <div class="progress mb-3" style="height: 10px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 20%"></div>
                    </div>

                    <strong>Out of Stock Products:</strong>
                    <p class="text-muted">Products not available for sale</p>
                    <div class="progress mb-3" style="height: 10px">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-dark" role="progressbar" style="width: 5%"></div>
                    </div>
                </div>
            </div>

            <!-- Right: Shop Tables -->
           <div class="col-xl-7">
    <div class="cat__core__widget p-3 h-100" style="background:#fff;">
        <!-- Shop Tabs -->
        <ul class="nav nav-tabs mb-3" id="shopTab" role="tablist">
            <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo e($index == 0 ? 'active' : ''); ?>" 
                       id="shop<?php echo e($shop->id); ?>-tab" 
                       data-bs-toggle="tab" 
                       href="#shop<?php echo e($shop->id); ?>" 
                       role="tab"><?php echo e($shop->name); ?></a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="tab-pane fade <?php echo e($index == 0 ? 'show active' : ''); ?>" 
                     id="shop<?php echo e($shop->id); ?>" role="tabpanel">
                    <table class="table table-bordered text-center">
                        <thead class="table-warning">
                            <tr>
                                <th>Metric</th>
                                <th>TZS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Purchases</td><td><?php echo e(number_format($shop->totalPurchases, 2)); ?></td></tr>
                            <tr><td>Sales</td><td><?php echo e(number_format($shop->totalSales, 2)); ?></td></tr>
                            <tr><td>Gross Profit</td><td><?php echo e(number_format($shop->grossProfit, 2)); ?></td></tr>
                            <tr><td>Total Expenses</td><td><?php echo e(number_format($shop->totalExpenses, 2)); ?></td></tr>
                            <tr><td>Net Profit</td><td><?php echo e(number_format($shop->netProfit, 2)); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

        </div>
    </div>
</div><br>



<!-- <div class="row">
    <div class="col-lg-6">
        <div class="cat__core__widget">
            <p class="pt-3 px-3"><strong>REVENUE STATISTICS (Last 7 Days)</strong></p>
            <div class="chart-line height-300 chartist"></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="cat__core__widget">
            <p class="pt-3 px-3"><strong>GROWTH (Current Year)</strong></p>
            <div class="chart-overlapping-bar height-300 chartist"></div>
        </div>
    </div>
</div> -->

 <!-- Product Sales Table -->
    <!-- <div class="container-fluid mt-4">
        <div class="row g-4" style="padding-left:30px; padding-right:30px;">
            <div class="col-lg-12">
                <div class="cat__core__widget">
                    <p class="pt-3 px-3"><strong>PRODUCT SALES SUMMARY (ALL SHOPS)</strong></p>
                    <div class="table-responsive">
                        <table class="table table-hover nowrap" width="100%">
                            <thead class="thead-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Quantity Sold</th>
                                    <th>Unit Price (TZS)</th>
                                    <th>Total Sales (TZS)</th>
                                    <th>Date Sold</th>
                                    <th>Profit (TZS)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $shops; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $__currentLoopData = $shop->sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $__currentLoopData = $sale->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><?php echo e($item->product->name); ?></td>
                                                <td><?php echo e($item->quantity); ?></td>
                                                <td><?php echo e(number_format($item->unit_price, 2)); ?></td>
                                                <td><?php echo e(number_format($item->quantity * $item->unit_price, 2)); ?></td>
                                                <td><?php echo e($sale->created_at->format('Y/m/d')); ?></td>
                                                <td><?php echo e(number_format(($item->unit_price - $item->product->purchase_price) * $item->quantity, 2)); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th><?php echo e(number_format($totalSales, 2)); ?></th>
                                    <th></th>
                                    <th><?php echo e(number_format($grossProfit, 2)); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

<!-- START: page scripts -->
<script>
    $( function() {

        ///////////////////////////////////////////////////////////
        // tooltips
        $("[data-toggle=tooltip]").tooltip();

        ///////////////////////////////////////////////////////////
        // chart1
        new Chartist.Line(".chart-line", {
            labels: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
            series: [
                [5, 0, 7, 8, 12],
                [2, 1, 3.5, 7, 3],
                [1, 3, 4, 5, 6]
            ]
        }, {
            fullWidth: !0,
            chartPadding: {
                right: 40
            },
            plugins: [
                Chartist.plugins.tooltip()
            ]
        });

        ///////////////////////////////////////////////////////////
        // chart 2
        var overlappingData = {
                    labels: ["Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    series: [
                        [5, 4, 3, 7, 5, 10, 3, 4, 8, 10, 6, 8],
                        [3, 2, 9, 5, 4, 6, 4, 6, 7, 8, 7, 4]
                    ]
                },
                overlappingOptions = {
                    seriesBarDistance: 10,
                    plugins: [
                        Chartist.plugins.tooltip()
                    ]
                },
                overlappingResponsiveOptions = [
                    ["", {
                        seriesBarDistance: 5,
                        axisX: {
                            labelInterpolationFnc: function(value) {
                                return value[0]
                            }
                        }
                    }]
                ];

        new Chartist.Bar(".chart-overlapping-bar", overlappingData, overlappingOptions, overlappingResponsiveOptions);

        ///////////////////////////////////////////////////////////
        // custom scroll
        if (!('ontouchstart' in document.documentElement) && jQuery().jScrollPane) {
            $('.custom-scroll').each(function() {
                $(this).jScrollPane({
                    contentWidth: '100%',
                    autoReinitialise: true,
                    autoReinitialiseDelay: 100
                });
                var api = $(this).data('jsp'),
                        throttleTimeout;
                $(window).bind('resize', function() {
                    if (!throttleTimeout) {
                        throttleTimeout = setTimeout(function() {
                            api.reinitialise();
                            throttleTimeout = null;
                        }, 50);
                    }
                });
            });
        }

    } );
</script>
<!-- END: page scripts -->
<!-- <?php echo $__env->make('components/footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> -->
<?php /**PATH E:\PROJECT\double h\double h\resources\views/dashboard/admin/index.blade.php ENDPATH**/ ?>