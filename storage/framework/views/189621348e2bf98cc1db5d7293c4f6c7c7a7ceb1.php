<?php $__env->startSection('title', 'Sales Returns'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Returns</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->count)); ?></div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Units returned</div><div class="fs-4 fw-bold"><?php echo e(number_format($totals->quantity)); ?></div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Refunded (Tsh)</div><div class="fs-4 fw-bold text-danger"><?php echo e(number_format($totals->amount, 2)); ?></div>
        </div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-arrow-return-left me-2"></i>Sales Returns</h5>
                <small class="text-muted">Goods customers brought back. Recording one restocks the product and reduces the original sale.</small>
            </div>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#recordReturnModal">
                <i class="bi bi-plus-circle"></i> Record return
            </button>
        </div>
        <div class="card-body">
            <?php echo $__env->make('pages.sales_revenue._filters', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Shop</th>
                            <th>Product</th>
                            <th>Sale #</th>
                            <th>Qty</th>
                            <th>Refund (Tsh)</th>
                            <th>Reason</th>
                            <th>Processed by</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $returns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-nowrap"><?php echo e(($return->returned_at ?? $return->created_at)->format('d M Y H:i')); ?></td>
                                <td><?php echo e($return->shop->name ?? 'N/A'); ?></td>
                                <td class="text-start"><?php echo e($return->product->name ?? 'Deleted product'); ?></td>
                                <td><?php echo e($return->saleItem->sale_id ?? '—'); ?></td>
                                <td><?php echo e($return->quantity); ?></td>
                                <td><?php echo e(number_format($return->amount, 2)); ?></td>
                                <td><?php echo e($return->reason ?: '—'); ?></td>
                                <td><?php echo e($return->processedBy->name ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8" class="text-muted py-4">No sales returns in this period.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($returns->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>

<?php echo $__env->make('pages.returns._record_modal', ['type' => 'sale'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\returns\sales.blade.php ENDPATH**/ ?>