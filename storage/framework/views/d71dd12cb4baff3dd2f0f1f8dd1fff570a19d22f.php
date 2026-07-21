<?php $__env->startSection('title', 'Dashboard'); ?>
<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('components/mainmenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<style>
    .premium-stat-card {
        border-width: 1px;
        border-radius: 1rem;
        transition: transform .18s ease, box-shadow .18s ease;
        background: #fff;
    }
    .premium-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.08) !important;
    }
    .premium-stat-icon {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        border-radius: 14px;
    }
    .premium-stat-label {
        letter-spacing: .04em;
        font-size: .72rem;
    }
    .premium-stat-value {
        font-size: 1.35rem;
    }
</style>

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
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-success w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-success text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Gross Profit (All Shops)</div>
                        <div class="fw-bold premium-stat-value text-success">Tz: <?php echo e(number_format($grossProfit, 2)); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-primary w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-primary text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Total Expenses (All Shops)</div>
                        <div class="fw-bold premium-stat-value text-primary">Tz: <?php echo e(number_format($totalExpenses, 2)); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-danger w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-danger text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-piggy-bank fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Net Profit</div>
                        <div class="fw-bold premium-stat-value text-danger">Tz: <?php echo e(number_format($netProfit, 2)); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales -->
        <div class="col-lg-3 col-md-6 d-flex">
            <div class="card premium-stat-card border-warning w-100 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="premium-stat-icon bg-warning text-white d-flex align-items-center justify-content-center me-3">
                        <i class="bi bi-tags fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted text-uppercase premium-stat-label fw-semibold mb-1">Sales (All Shops)</div>
                        <div class="fw-bold premium-stat-value text-warning-emphasis">Tz: <?php echo e(number_format($totalSales, 2)); ?></div>
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
<?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/dashboard/admin/index.blade.php ENDPATH**/ ?>