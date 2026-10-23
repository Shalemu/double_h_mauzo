<?php $__env->startSection('title', 'Stock Transfers'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
    <?php if($errors->any()): ?><div class="alert alert-danger"><?php echo e($errors->first()); ?></div><?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Stock Transfers</h5>
                <small class="text-muted">Move stock between shops and stores (store → store, store → shop, shop → shop). The receiving side's matching product (same barcode or name) is topped up, or created if it doesn't stock it yet.</small>
            </div>
            <a href="<?php echo e(route('stock-transfers.create')); ?>" class="btn btn-sm btn-primary <?php echo e(($shops->count() < 2) ? 'disabled' : ''); ?>">
                <i class="bi bi-plus-circle"></i> New transfer
            </a>
        </div>
        <div class="card-body">
            <?php if($shops->count() < 2): ?>
                <div class="alert alert-info">You need at least two shops or stores to transfer stock. <a href="<?php echo e(route('dashboard.shop')); ?>">Add a shop or store</a>.</div>
            <?php endif; ?>

            <?php echo $__env->make('pages.overview._shop_filter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr><th>#</th><th>Date</th><th>From</th><th>To</th><th>Items</th><th>Units</th><th>By</th><th>Note</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transfer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($transfer->id); ?></td>
                                <td class="text-nowrap"><?php echo e($transfer->created_at->format('d M Y H:i')); ?></td>
                                <td><?php echo e($transfer->fromShop->label ?? '—'); ?></td>
                                <td><?php echo e($transfer->toShop->label ?? '—'); ?></td>
                                <td>
                                    <?php $__currentLoopData = $transfer->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="small"><?php echo e($item->product_name); ?> × <?php echo e($item->quantity); ?></div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                <td class="text-center"><?php echo e($transfer->items->sum('quantity')); ?></td>
                                <td><?php echo e($transfer->transferredBy->name ?? '—'); ?></td>
                                <td><?php echo e($transfer->note ?: '—'); ?></td>
                                <td class="text-center text-nowrap">
                                    <a href="<?php echo e(route('stock-transfers.show', $transfer)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <a href="<?php echo e(route('stock-transfers.show', [$transfer, 'print' => 1])); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" aria-label="Print transfer #<?php echo e($transfer->id); ?>">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No stock transfers yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3"><?php echo e($transfers->links('pagination::bootstrap-4')); ?></div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\PROJECTS\d\double_h_mauzo\resources\views\pages\stock_transfers\index.blade.php ENDPATH**/ ?>