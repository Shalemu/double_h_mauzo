<?php $__env->startSection('title', 'Sale Items'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Units sold</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->quantity)); ?></div>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Revenue (Tsh)</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->revenue, 2)); ?></div>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Cost of goods (Tsh)</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->cost, 2)); ?></div>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Gross profit (Tsh)</div>
            <div class="fs-4 fw-bold <?php echo e($totals->revenue - $totals->cost < 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format($totals->revenue - $totals->cost, 2)); ?></div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-basket me-2"></i>Sale Items</h5></div>
        <div class="card-body">
            <?php echo $__env->make('pages.sales_revenue._filters', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Shop</th>
                            <th>Type</th>
                            <th>Qty sold</th>
                            <th>Revenue (Tsh)</th>
                            <th>Cost (Tsh)</th>
                            <th>Profit (Tsh)</th>
                            <th>Last sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $profit = $item->revenue - $item->cost; ?>
                            <tr>
                                <td class="text-start"><?php echo e($item->product ?? 'Deleted product'); ?></td>
                                <td><?php echo e($item->shop ?? 'N/A'); ?></td>
                                <td><?php echo e(ucfirst($item->sale_type ?? 'retail')); ?></td>
                                <td><?php echo e(number_format($item->quantity)); ?></td>
                                <td><?php echo e(number_format($item->revenue, 2)); ?></td>
                                <td><?php echo e(number_format($item->cost, 2)); ?></td>
                                <td class="<?php echo e($profit < 0 ? 'text-danger' : 'text-success'); ?>"><?php echo e(number_format($profit, 2)); ?></td>
                                <td class="text-nowrap"><?php echo e(\Carbon\Carbon::parse($item->last_sold)->format('d M Y')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="text-muted py-4">No items sold in this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($items->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\sales_revenue\items.blade.php ENDPATH**/ ?>