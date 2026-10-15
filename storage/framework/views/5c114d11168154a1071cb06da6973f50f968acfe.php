<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<style>
.dashboard-card{
    background:linear-gradient(135deg, #ffffff 0%, #f7f9fc 100%);
    border-radius:10px;
    border:1px solid #e8ebf1;
    padding:18px 20px;
    transition:.25s;
    height:100%;
    display:flex;
    align-items:center;
    gap:16px;
    color:inherit;
    text-decoration:none;
}
.dashboard-card:hover{
    box-shadow:0 10px 24px rgba(31,41,55,.08);
    border-color:#dce2eb;
}
.dashboard-icon{
    width:44px;
    height:44px;
    flex:0 0 44px;
    border-radius:12px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
}
.dashboard-title{
    color:#6b7280;
    font-size:12.5px;
    font-weight:500;
    margin:0 0 3px;
}
.dashboard-number{
    font-size:24px;
    font-weight:700;
    line-height:1.1;
    color:#111827;
    white-space:nowrap;
}
.dashboard-card-content{
    flex:1;
    min-width:0;
}
.dashboard-card-heading{
    display:flex;
    flex-direction:column;
    align-items:flex-end;
    gap:3px;
}
.dashboard-card-heading .dashboard-title{
    align-self:flex-end;
    text-align:right;
}
.dashboard-link{
    display:inline-block;
    margin-top:9px;
    text-decoration:none;
    font-size:12.5px;
    font-weight:500;
}
.bg-blue{ background:#eef2ff; color:#4f46e5; }
.bg-green{ background:#e9f9ef; color:#16a34a; }
.bg-orange{ background:#fef3e2; color:#d97706; }
.bg-red{ background:#fdeceb; color:#ef4444; }

/* "View all" button under each stock-alert tab, tinted to match its tab */
.alert-view-all{ display:inline-flex; align-items:center; padding:7px 16px; border-radius:8px; font-weight:600; font-size:13.5px; border:1px solid transparent; transition:.15s; }
.alert-view-all .bi-arrow-right{ transition:transform .15s; }
.alert-view-all:hover .bi-arrow-right{ transform:translateX(3px); }
.alert-view-expired{ background:#fdeceb; color:#b91c1c; border-color:#f8d4d1; }
.alert-view-expired:hover{ background:#dc2626; color:#fff; }
.alert-view-running_out{ background:#fef3e2; color:#b45309; border-color:#fbe0b8; }
.alert-view-running_out:hover{ background:#d97706; color:#fff; }
.alert-view-expiring{ background:#eef2ff; color:#4338ca; border-color:#dbe1fe; }
.alert-view-expiring:hover{ background:#4f46e5; color:#fff; }
.alert-view-out_of_stock{ background:#f1f5f9; color:#334155; border-color:#e2e8f0; }
.alert-view-out_of_stock:hover{ background:#475569; color:#fff; }

.dashboard-panel{
    background:#fff;
    border-radius:16px;
    border:1px solid #edf0f7;
    padding:16px 18px;
    height:100%;
}
.dashboard-panel-title{
    font-size:14px;
    font-weight:700;
    color:#1d2433;
    margin-bottom:12px;
}

.quick-action-btn{
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #edf0f7;
    color:#1d2433;
    text-decoration:none;
    font-size:13px;
    font-weight:500;
    transition:.2s;
    height:100%;
}
.quick-action-btn:hover{
    background:#f7f9fc;
    border-color:#dde3ee;
    color:#1d2433;
}
.quick-action-btn i{
    font-size:16px;
    color:#1677ff;
}
</style>

<div class="container-fluid">

    <!-- Hero stat cards -->
    <div class="row g-4">

        <div class="col-xl-3 col-md-6">
            <a href="<?php echo e(route('products.index', ['filter' => 'all'])); ?>" class="dashboard-card">
                <div class="dashboard-icon bg-blue"><i class="bi bi-box-seam"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number"><?php echo e(number_format($totalProducts)); ?></div>
                        <div class="dashboard-title">Total Products</div>
                    </div>
                    <span class="dashboard-link text-primary">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="<?php echo e(route('products.in-stock')); ?>" class="dashboard-card">
                <div class="dashboard-icon bg-green"><i class="bi bi-check-circle"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number"><?php echo e(number_format($remainingProducts)); ?></div>
                        <div class="dashboard-title">Remaining In Stock</div>
                    </div>
                    <span class="dashboard-link text-success">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="<?php echo e(route('products.expired')); ?>" class="dashboard-card">
                <div class="dashboard-icon bg-orange"><i class="bi bi-calendar-x"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number"><?php echo e(number_format($expiredProducts)); ?></div>
                        <div class="dashboard-title">Expired</div>
                    </div>
                    <span class="dashboard-link text-warning">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6">
            <a href="<?php echo e(route('products.out-of-stock')); ?>" class="dashboard-card">
                <div class="dashboard-icon bg-red"><i class="bi bi-x-circle"></i></div>
                <div class="dashboard-card-content">
                    <div class="dashboard-card-heading">
                        <div class="dashboard-number"><?php echo e(number_format($outOfStockProducts)); ?></div>
                        <div class="dashboard-title">Out of Stock</div>
                    </div>
                    <span class="dashboard-link text-danger">
                        View all <i class="bi bi-arrow-right"></i>
                    </span>
                </div>
            </a>
        </div>

    </div>

    <!-- Overview chart + Top Selling Products -->
    <div class="row g-4 mt-1">

        <div class="col-xl-8">
            <div class="dashboard-panel">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="dashboard-panel-title mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Overview — <?php echo e(date('Y')); ?></div>
                </div>
                <div class="d-flex gap-4 mb-3">
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span style="width:10px;height:10px;border-radius:50%;background:#4f46e5;display:inline-block;"></span> Sales
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span style="width:10px;height:10px;border-radius:50%;background:#16a34a;display:inline-block;"></span> Purchases
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span style="width:10px;height:10px;border-radius:50%;background:#d97706;display:inline-block;"></span> Expenses
                    </div>
                </div>
                <div style="height:280px;">
                    <canvas id="overviewChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="dashboard-panel">
                <div class="dashboard-panel-title"><i class="bi bi-trophy me-2 text-warning"></i>Top Selling Products</div>
                <?php $__empty_1 = true; $__currentLoopData = $topSellingProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex align-items-center justify-content-between <?php echo e(!$loop->last ? 'mb-3' : ''); ?>">
                        <div class="d-flex align-items-center gap-2">
                            <div class="dashboard-icon bg-blue" style="width:32px;height:32px;font-size:13px;font-weight:700;"><?php echo e($i + 1); ?></div>
                            <div>
                                <div style="font-size:13.5px;font-weight:600;color:#1d2433;"><?php echo e($p->name); ?></div>
                                <div style="font-size:12px;color:#8891a3;"><?php echo e($p->brand ?? 'No brand'); ?></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:13.5px;font-weight:700;color:#1d2433;"><?php echo e(number_format($p->total_qty)); ?> sold</div>
                            <div style="font-size:12px;color:#8891a3;">TZS <?php echo e(number_format($p->total_revenue)); ?></div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-muted small">No sales recorded yet.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Quick Actions + secondary stats -->
    <div class="row g-4 mt-1">

        <div class="col-xl-4 col-lg-5">
            <div class="dashboard-panel">
                <div class="dashboard-panel-title">Quick Actions</div>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?php echo e(route('admin.pos.index')); ?>" class="quick-action-btn">
                            <i class="bi bi-cash-coin"></i> Sell / POS
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('dashboard.shop')); ?>" class="quick-action-btn">
                            <i class="bi bi-shop"></i> Business Mgmt
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('products.index')); ?>" class="quick-action-btn">
                            <i class="bi bi-box-seam"></i> Manage Products
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?php echo e(route('customers.index')); ?>" class="quick-action-btn">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="dashboard-panel">
                <div class="dashboard-panel-title">More Inventory Stats</div>
                <div class="row g-3">
                    <?php $__currentLoopData = [
                        ['products.running-out', 'bg-orange', 'bi-exclamation-triangle', $runningOutProducts, 'Running Out', 'text-warning'],
                        ['products.expiring', 'bg-blue', 'bi-calendar-event', $expiringProducts, 'Expiring within 1 year', 'text-primary'],
                        ['products.trash', 'bg-red', 'bi-trash', $disposedProducts, 'Disposed', 'text-danger'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$route, $bg, $icon, $count, $label, $linkClass]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-sm-4">
                            <a href="<?php echo e(route($route)); ?>" class="d-flex align-items-center gap-2 text-decoration-none">
                                <div class="dashboard-icon <?php echo e($bg); ?>" style="width:40px;height:40px;font-size:16px;"><i class="bi <?php echo e($icon); ?>"></i></div>
                                <div>
                                    <div class="dashboard-number" style="font-size:20px;"><?php echo e(number_format($count)); ?></div>
                                    <div class="dashboard-title" style="margin-top:0;"><?php echo e($label); ?></div>
                                    <span class="dashboard-link small <?php echo e($linkClass); ?>">View <i class="bi bi-arrow-right"></i></span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Shop performance -->
    <div class="row g-4 mt-1">
        <div class="col-12">
    <div class="dashboard-panel p-3 h-100">
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

    <!-- Stock alerts -->
    <?php
        $alertTabs = [
            'expired' => ['Expired', 'bi-calendar-x', 'text-danger', $expiredProducts, 'products.expired'],
            'running_out' => ['Running Out', 'bi-hourglass-split', 'text-warning', $runningOutProducts, 'products.running-out'],
            'expiring' => ['Expiring within 1 year', 'bi-calendar-event', 'text-primary', $expiringProducts, 'products.expiring'],
            'out_of_stock' => ['Out of Stock', 'bi-x-circle', 'text-secondary', $outOfStockProducts, 'products.out-of-stock'],
        ];
        $today = \Carbon\Carbon::today();
    ?>
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            <div class="dashboard-panel p-3">
                <div class="dashboard-panel-title"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Stock Alerts</div>

                <ul class="nav nav-tabs mb-3 flex-nowrap overflow-auto" id="stockAlertTabs" role="tablist">
                    <?php $__currentLoopData = $alertTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label, $icon, $color, $count, $route]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-nowrap <?php echo e($loop->first ? 'active' : ''); ?>" id="alert-<?php echo e($key); ?>-tab"
                                    data-bs-toggle="tab" data-bs-target="#alert-<?php echo e($key); ?>" type="button" role="tab"
                                    aria-controls="alert-<?php echo e($key); ?>" aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>">
                                <i class="bi <?php echo e($icon); ?> <?php echo e($color); ?> me-1"></i><?php echo e($label); ?>

                                <span class="badge rounded-pill bg-light text-dark border ms-1"><?php echo e(number_format($count)); ?></span>
                            </button>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <div class="tab-content">
                    <?php $__currentLoopData = $alertTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label, $icon, $color, $count, $route]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $rows = $stockAlerts[$key]; ?>
                        <div class="tab-pane fade <?php echo e($loop->first ? 'show active' : ''); ?>" id="alert-<?php echo e($key); ?>" role="tabpanel" aria-labelledby="alert-<?php echo e($key); ?>-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center mb-2">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">Product</th>
                                            <th>Shop</th>
                                            <th>Qty</th>
                                            <?php if($key === 'running_out'): ?><th>Minimum</th><?php endif; ?>
                                            <?php if(in_array($key, ['expired', 'expiring'])): ?><th>Expiry date</th><th><?php echo e($key === 'expired' ? 'Expired' : 'Time left'); ?></th><?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php $expiry = $product->expire_date ? \Carbon\Carbon::parse($product->expire_date) : null; ?>
                                            <tr>
                                                <td class="text-start"><?php echo e($product->name); ?></td>
                                                <td><?php echo e($product->shop->name ?? '—'); ?></td>
                                                <td class="<?php echo e($product->quantity <= 0 ? 'text-danger fw-semibold' : ''); ?>"><?php echo e($product->quantity); ?></td>
                                                <?php if($key === 'running_out'): ?><td><?php echo e($product->min_quantity); ?></td><?php endif; ?>
                                                <?php if(in_array($key, ['expired', 'expiring'])): ?>
                                                    <td class="text-nowrap"><?php echo e($expiry ? $expiry->format('d M Y') : '—'); ?></td>
                                                    <td class="text-nowrap <?php echo e($key === 'expired' ? 'text-danger' : ($expiry && $expiry->diffInDays($today) <= 30 ? 'text-danger fw-semibold' : '')); ?>">
                                                        <?php echo e($expiry ? $expiry->diffForHumans(['parts' => 2]) : '—'); ?>

                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr><td colspan="5" class="text-muted py-4">Nothing here — good news.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if($count > 0): ?>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
                                    <span class="text-muted small">Showing <?php echo e($rows->count()); ?> of <?php echo e(number_format($count)); ?></span>
                                    <a href="<?php echo e(route($route)); ?>" class="btn btn-sm alert-view-all alert-view-<?php echo e($key); ?>">
                                        View all <?php echo e(number_format($count)); ?> <?php echo e(strtolower($label)); ?>

                                        <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- START: page scripts -->
<script>
    $( function() {

        ///////////////////////////////////////////////////////////
        // tooltips
        $("[data-toggle=tooltip]").tooltip();

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

    ///////////////////////////////////////////////////////////
    // Overview chart (Sales / Purchases / Expenses per month)
    (function () {
        const canvas = document.getElementById('overviewChart');
        if (!canvas || typeof Chart === 'undefined') return;

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                datasets: [
                    {
                        label: 'Sales',
                        data: <?php echo json_encode($monthlySales, 15, 512) ?>,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79,70,229,.08)',
                        lineTension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        borderWidth: 2,
                    },
                    {
                        label: 'Purchases',
                        data: <?php echo json_encode($monthlyPurchases, 15, 512) ?>,
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,.08)',
                        lineTension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        borderWidth: 2,
                    },
                    {
                        label: 'Expenses',
                        data: <?php echo json_encode($monthlyExpenses, 15, 512) ?>,
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217,119,6,.08)',
                        lineTension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true }, gridLines: { color: '#f1f3f8' } }],
                    xAxes: [{ gridLines: { display: false } }],
                },
            },
        });
    })();
</script>
<!-- END: page scripts -->
<!-- <?php echo $__env->make('components/footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> -->

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\admin\index.blade.php ENDPATH**/ ?>