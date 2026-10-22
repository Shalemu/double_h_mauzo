<?php $__env->startSection('title', 'Sales'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Sales</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->count)); ?></div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Total sold (Tsh)</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->total, 2)); ?></div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Unpaid / credit (Tsh)</div><div class="fs-4 fw-bold text-danger"><?php echo e(number_format($totals->credit, 2)); ?></div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Sales</h5></div>
        <div class="card-body">
            <?php echo $__env->make('pages.sales_revenue._filters', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Shop</th>
                            <th>Sold by</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Payment</th>
                            <th>Total (Tsh)</th>
                            <th>Balance (Tsh)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($sale->id); ?></td>
                                <td class="text-nowrap"><?php echo e($sale->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($sale->shop->name ?? 'N/A'); ?></td>
                                <td><?php echo e($sale->staff->full_name ?? ($sale->admin->name ?? 'N/A')); ?></td>
                                <td><?php echo e($sale->customer->name ?? 'Walk-in'); ?></td>
                                <td><?php echo e($sale->items_count); ?></td>
                                <td><?php echo e(ucfirst($sale->payment_type ?? $sale->payment_method ?? '—')); ?></td>
                                <td><?php echo e(number_format($sale->total, 2)); ?></td>
                                <td class="<?php echo e($sale->remaining_amount > 0 ? 'text-danger' : ''); ?>"><?php echo e(number_format($sale->remaining_amount ?? 0, 2)); ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary text-nowrap" href="<?php echo e(route('admin.sales.show', $sale->id)); ?>"
                                       aria-label="View sale #<?php echo e($sale->id); ?>">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="10" class="text-muted py-4">No sales in this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($sales->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views/pages/sales_revenue/sales.blade.php ENDPATH**/ ?>