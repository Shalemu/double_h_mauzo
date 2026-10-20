<?php $__env->startSection('title', 'Revenue & Profit'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        <?php $__currentLoopData = [
            ['Sales', $totals['sales'], ''],
            ['Returns (already deducted)', $totals['returns'], 'text-warning'],
            ['Gross profit', $totals['gross_profit'], $totals['gross_profit'] < 0 ? 'text-danger' : 'text-success'],
            ['Net profit', $totals['net_profit'], $totals['net_profit'] < 0 ? 'text-danger' : 'text-success'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $class]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body">
                <div class="text-muted small"><?php echo e($label); ?> (Tsh)</div>
                <div class="fs-4 fw-bold <?php echo e($class); ?>"><?php echo e(number_format($value, 2)); ?></div>
            </div></div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Revenue by Shop</h5>
            <small class="text-muted">Sales are already net of returns. Gross profit = sales − cost of goods; net profit also subtracts expenses recorded in the period.</small>
        </div>
        <div class="card-body">
            <?php echo $__env->make('pages.sales_revenue._filters', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-end mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="text-start">Shop</th>
                            <th>Sales</th>
                            <th>Returns<br><small class="fw-normal">(already deducted)</small></th>
                            <th>Cost of goods</th>
                            <th>Gross profit</th>
                            <th>Expenses</th>
                            <th>Net profit</th>
                            <th>Fixed expenses<br><small class="fw-normal">(monthly, not deducted)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-start">
                                    <a href="<?php echo e(route('dashboard.shop.show', $row['shop']->id)); ?>"><?php echo e($row['shop']->name); ?></a>
                                </td>
                                <td><?php echo e(number_format($row['sales'], 2)); ?></td>
                                <td><?php echo e(number_format($row['returns'], 2)); ?></td>
                                <td><?php echo e(number_format($row['cost'], 2)); ?></td>
                                <td class="<?php echo e($row['gross_profit'] < 0 ? 'text-danger' : ''); ?>"><?php echo e(number_format($row['gross_profit'], 2)); ?></td>
                                <td><a href="<?php echo e(route('expenses.index', $row['shop']->id)); ?>"><?php echo e(number_format($row['expenses'], 2)); ?></a></td>
                                <td class="fw-semibold <?php echo e($row['net_profit'] < 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format($row['net_profit'], 2)); ?></td>
                                <td><a href="<?php echo e(route('fixed-expenses.index', $row['shop']->id)); ?>"><?php echo e(number_format($row['fixed'], 2)); ?></a></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No shops found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if($rows->count() > 1): ?>
                        <tfoot class="fw-bold">
                            <tr>
                                <td class="text-start">Total</td>
                                <td><?php echo e(number_format($totals['sales'], 2)); ?></td>
                                <td><?php echo e(number_format($totals['returns'], 2)); ?></td>
                                <td><?php echo e(number_format($totals['cost'], 2)); ?></td>
                                <td><?php echo e(number_format($totals['gross_profit'], 2)); ?></td>
                                <td><?php echo e(number_format($totals['expenses'], 2)); ?></td>
                                <td><?php echo e(number_format($totals['net_profit'], 2)); ?></td>
                                <td><?php echo e(number_format($totals['fixed'], 2)); ?></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Daily sales</h6></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered text-center mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Sales</th><th>Total (Tsh)</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $daily; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e(\Carbon\Carbon::parse($day->day)->format('D, d M Y')); ?></td>
                                <td><?php echo e($day->count); ?></td>
                                <td><?php echo e(number_format($day->total, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3" class="text-muted py-3">No sales in this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/sales_revenue/revenue.blade.php ENDPATH**/ ?>